package http_test

import (
	"context"
	"encoding/json"
	"io"
	"log/slog"
	"net/http"
	"net/http/httptest"
	"testing"
	"time"

	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
	"github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo"
)

type fakeServerInfoRepo struct {
	info serverinfo.Info
}

func (f fakeServerInfoRepo) ServerInfo(ctx context.Context) (serverinfo.Info, error) {
	return f.info, nil
}

func TestServerInfoEndpointJSON(t *testing.T) {
	ts := time.Date(2026, 6, 20, 12, 30, 0, 0, time.UTC)
	svc := serverinfo.New(fakeServerInfoRepo{info: serverinfo.Info{Time: ts, PostGIS: "3.4 USE_GEOS=1"}})

	router := transporthttp.NewRouter(transporthttp.Deps{
		Log:        slog.New(slog.NewJSONHandler(io.Discard, nil)),
		ServerInfo: svc,
		Version:    "1.2.3",
	})

	rr := httptest.NewRecorder()
	req := httptest.NewRequest(http.MethodGet, "/api/v1/server-info", nil)
	router.ServeHTTP(rr, req)

	if rr.Code != http.StatusOK {
		t.Fatalf("status = %d, want %d (body %s)", rr.Code, http.StatusOK, rr.Body.String())
	}
	if ct := rr.Header().Get("Content-Type"); ct != "application/json" {
		t.Errorf("Content-Type = %q, want application/json", ct)
	}

	var got map[string]string
	if err := json.Unmarshal(rr.Body.Bytes(), &got); err != nil {
		t.Fatalf("invalid JSON: %v (body %s)", err, rr.Body.String())
	}
	want := map[string]string{
		"app":     "gps-tracker",
		"version": "1.2.3",
		"time":    ts.Format(time.RFC3339),
		"postgis": "3.4 USE_GEOS=1",
	}
	for k, v := range want {
		if got[k] != v {
			t.Errorf("field %q = %q, want %q", k, got[k], v)
		}
	}
	if len(got) != len(want) {
		t.Errorf("response has %d fields, want %d: %v", len(got), len(want), got)
	}
}
