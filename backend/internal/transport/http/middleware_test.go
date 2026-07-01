package http

import (
	"context"
	"encoding/json"
	"io"
	"log/slog"
	"net/http"
	"net/http/httptest"
	"testing"

	"go.opentelemetry.io/contrib/instrumentation/net/http/otelhttp"
	sdktrace "go.opentelemetry.io/otel/sdk/trace"
	"go.opentelemetry.io/otel/sdk/trace/tracetest"
)

func discardLogger() *slog.Logger {
	return slog.New(slog.NewJSONHandler(io.Discard, nil))
}

func TestRequestIDMiddlewareSetsHeader(t *testing.T) {
	var seen string
	h := requestID(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		seen = requestIDFromContext(r.Context())
		w.WriteHeader(http.StatusOK)
	}))

	rec := httptest.NewRecorder()
	req := httptest.NewRequest(http.MethodGet, "/healthz", nil)
	h.ServeHTTP(rec, req)

	got := rec.Header().Get("X-Request-Id")
	if got == "" {
		t.Fatalf("expected X-Request-Id response header to be set, got empty")
	}
	if seen != got {
		t.Fatalf("context request id %q does not match header %q", seen, got)
	}
}

func TestRequestIDMiddlewareHonorsIncomingHeader(t *testing.T) {
	h := requestID(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusOK)
	}))
	rec := httptest.NewRecorder()
	req := httptest.NewRequest(http.MethodGet, "/healthz", nil)
	req.Header.Set("X-Request-Id", "abc-123")
	h.ServeHTTP(rec, req)
	if got := rec.Header().Get("X-Request-Id"); got != "abc-123" {
		t.Fatalf("expected incoming request id to be reused, got %q", got)
	}
}

func TestRecovererReturns500AndDoesNotCrash(t *testing.T) {
	h := recoverer(discardLogger())(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		panic("boom")
	}))

	rec := httptest.NewRecorder()
	req := httptest.NewRequest(http.MethodGet, "/api/v1/server-info", nil)
	h.ServeHTTP(rec, req) // must not panic

	if rec.Code != http.StatusInternalServerError {
		t.Fatalf("expected status 500, got %d", rec.Code)
	}
	var body map[string]string
	if err := json.Unmarshal(rec.Body.Bytes(), &body); err != nil {
		t.Fatalf("response body is not JSON: %v", err)
	}
	if body["status"] != "error" {
		t.Fatalf(`expected {"status":"error"}, got %v`, body)
	}
}

func TestAccessLogRunsAndPassesThrough(t *testing.T) {
	called := false
	h := requestID(accessLog(discardLogger())(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		called = true
		w.WriteHeader(http.StatusTeapot)
	})))

	rec := httptest.NewRecorder()
	req := httptest.NewRequest(http.MethodGet, "/healthz", nil)
	h.ServeHTTP(rec, req)

	if !called {
		t.Fatalf("inner handler was not called through accessLog")
	}
	if rec.Code != http.StatusTeapot {
		t.Fatalf("expected status 418 to pass through, got %d", rec.Code)
	}
}

// capturingHandler records the attributes of the last slog record so a test
// can assert on what accessLog logged.
type capturingHandler struct {
	attrs map[string]slog.Value
}

func (c *capturingHandler) Enabled(context.Context, slog.Level) bool { return true }
func (c *capturingHandler) Handle(_ context.Context, r slog.Record) error {
	c.attrs = make(map[string]slog.Value)
	r.Attrs(func(a slog.Attr) bool {
		c.attrs[a.Key] = a.Value
		return true
	})
	return nil
}
func (c *capturingHandler) WithAttrs([]slog.Attr) slog.Handler { return c }
func (c *capturingHandler) WithGroup(string) slog.Handler      { return c }

func TestAccessLogCapturesImplicit200(t *testing.T) {
	cap := &capturingHandler{}
	// Handler writes a body without ever calling WriteHeader; net/http sends a
	// 200 implicitly and accessLog must record 200, not 0.
	h := accessLog(slog.New(cap))(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		_, _ = w.Write([]byte("ok"))
	}))

	rec := httptest.NewRecorder()
	req := httptest.NewRequest(http.MethodGet, "/healthz", nil)
	h.ServeHTTP(rec, req)

	got, ok := cap.attrs["status"]
	if !ok {
		t.Fatalf("access log did not record a status attribute")
	}
	if got.Int64() != http.StatusOK {
		t.Fatalf("expected logged status 200 for implicit write, got %d", got.Int64())
	}
}

func TestOtelHTTPRecordsSpan(t *testing.T) {
	sr := tracetest.NewSpanRecorder()
	tp := sdktrace.NewTracerProvider(sdktrace.WithSpanProcessor(sr))
	t.Cleanup(func() { _ = tp.Shutdown(context.Background()) })

	inner := http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusOK)
	})
	h := otelhttp.NewHandler(inner, "gps-api",
		otelhttp.WithTracerProvider(tp),
		// Mirror router.go: keep the operation as the span name (otelhttp >=0.69
		// otherwise defaults the span name to the HTTP method).
		otelhttp.WithSpanNameFormatter(func(operation string, _ *http.Request) string {
			return operation
		}),
	)

	rec := httptest.NewRecorder()
	req := httptest.NewRequest(http.MethodGet, "/healthz", nil)
	h.ServeHTTP(rec, req)

	spans := sr.Ended()
	if len(spans) != 1 {
		t.Fatalf("expected exactly 1 recorded span, got %d", len(spans))
	}
	if spans[0].Name() != "gps-api" {
		t.Fatalf("expected span name %q, got %q", "gps-api", spans[0].Name())
	}
}
