//go:build integration

package postgres_test

import (
	"context"
	"encoding/json"
	"io"
	"log/slog"
	"net/http"
	"net/http/httptest"
	"testing"
	"time"

	"github.com/testcontainers/testcontainers-go"
	tcpostgres "github.com/testcontainers/testcontainers-go/modules/postgres"
	"github.com/testcontainers/testcontainers-go/wait"

	"github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres"
	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
	"github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo"
)

func TestServerInfoEndToEnd(t *testing.T) {
	ctx := context.Background()

	container, err := tcpostgres.Run(ctx, "postgis/postgis:16-3.4",
		tcpostgres.WithDatabase("gps"),
		tcpostgres.WithUsername("gps"),
		tcpostgres.WithPassword("gps"),
		testcontainers.WithWaitStrategy(
			wait.ForLog("database system is ready to accept connections").
				WithOccurrence(2).
				WithStartupTimeout(60*time.Second),
		),
	)
	if err != nil {
		t.Fatalf("start postgis container: %v", err)
	}
	t.Cleanup(func() {
		if err := testcontainers.TerminateContainer(container); err != nil {
			t.Logf("terminate container: %v", err)
		}
	})

	dsn, err := container.ConnectionString(ctx, "sslmode=disable")
	if err != nil {
		t.Fatalf("connection string: %v", err)
	}

	cfg := config.Config{
		Env:         "test",
		ServiceName: "gps-api",
		Version:     "test-1.0.0",
		DatabaseURL: dsn,
		LogLevel:    "info",
	}

	if err := postgres.Migrate(ctx, cfg); err != nil {
		t.Fatalf("migrate: %v", err)
	}

	db, err := postgres.New(ctx, cfg)
	if err != nil {
		t.Fatalf("postgres.New: %v", err)
	}
	t.Cleanup(db.Close)

	router := transporthttp.NewRouter(transporthttp.Deps{
		Log:        slog.New(slog.NewJSONHandler(io.Discard, nil)),
		ServerInfo: serverinfo.New(db),
		Version:    cfg.Version,
	})

	rr := httptest.NewRecorder()
	req := httptest.NewRequest(http.MethodGet, "/api/v1/server-info", nil)
	router.ServeHTTP(rr, req)

	if rr.Code != http.StatusOK {
		t.Fatalf("status = %d, want 200 (body %s)", rr.Code, rr.Body.String())
	}

	var got struct {
		App     string `json:"app"`
		Version string `json:"version"`
		Time    string `json:"time"`
		PostGIS string `json:"postgis"`
	}
	if err := json.Unmarshal(rr.Body.Bytes(), &got); err != nil {
		t.Fatalf("decode body: %v (%s)", err, rr.Body.String())
	}
	if got.App != "gps-tracker" {
		t.Errorf("app = %q, want gps-tracker", got.App)
	}
	if got.Version != cfg.Version {
		t.Errorf("version = %q, want %q", got.Version, cfg.Version)
	}
	if _, err := time.Parse(time.RFC3339, got.Time); err != nil {
		t.Errorf("time %q is not RFC3339: %v", got.Time, err)
	}
	if got.PostGIS == "" {
		t.Error("postgis field is empty, want a non-empty PostGIS version")
	}
}
