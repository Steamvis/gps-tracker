package http_test

import (
	"context"
	"encoding/json"
	"errors"
	"net/http"
	"net/http/httptest"
	"testing"

	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
)

func doReadyz(t *testing.T, checks []transporthttp.ReadyCheck) (int, map[string]any) {
	t.Helper()
	h := transporthttp.NewReadyHandler(checks)
	req := httptest.NewRequest(http.MethodGet, "/readyz", nil)
	rec := httptest.NewRecorder()
	h.ServeHTTP(rec, req)

	var body map[string]any
	if err := json.Unmarshal(rec.Body.Bytes(), &body); err != nil {
		t.Fatalf("decode body: %v (raw=%q)", err, rec.Body.String())
	}
	return rec.Code, body
}

func TestReadyHandler_AllOK(t *testing.T) {
	checks := []transporthttp.ReadyCheck{
		{Name: "postgres", Check: func(context.Context) error { return nil }},
		{Name: "redis", Check: func(context.Context) error { return nil }},
	}
	code, body := doReadyz(t, checks)
	if code != http.StatusOK {
		t.Fatalf("status = %d, want %d", code, http.StatusOK)
	}
	if body["status"] != "ok" {
		t.Fatalf("status field = %v, want ok", body["status"])
	}
	got, _ := json.Marshal(body["checks"])
	if string(got) != `{"postgres":"ok","redis":"ok"}` {
		t.Fatalf("checks = %s, want both ok", got)
	}
}

func TestReadyHandler_OneFailing(t *testing.T) {
	checks := []transporthttp.ReadyCheck{
		{Name: "postgres", Check: func(context.Context) error { return errors.New("connection refused") }},
		{Name: "redis", Check: func(context.Context) error { return nil }},
	}
	code, body := doReadyz(t, checks)
	if code != http.StatusServiceUnavailable {
		t.Fatalf("status = %d, want %d", code, http.StatusServiceUnavailable)
	}
	if body["status"] != "degraded" {
		t.Fatalf("status field = %v, want degraded", body["status"])
	}
	checksMap, _ := body["checks"].(map[string]any)
	if checksMap["postgres"] != "connection refused" {
		t.Fatalf("postgres check = %v, want error string", checksMap["postgres"])
	}
	if checksMap["redis"] != "ok" {
		t.Fatalf("redis check = %v, want ok", checksMap["redis"])
	}
}
