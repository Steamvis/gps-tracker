//go:build integration

package postgres_test

import (
	"context"
	"testing"
	"time"

	"github.com/testcontainers/testcontainers-go"
	tcpostgres "github.com/testcontainers/testcontainers-go/modules/postgres"
	"github.com/testcontainers/testcontainers-go/wait"

	"github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres"
	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
)

func TestPostgres_MigrateAndServerInfo(t *testing.T) {
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

	cfg := config.Config{DatabaseURL: dsn}

	if err := postgres.Migrate(ctx, cfg); err != nil {
		t.Fatalf("migrate: %v", err)
	}

	db, err := postgres.New(ctx, cfg)
	if err != nil {
		t.Fatalf("new db: %v", err)
	}
	t.Cleanup(db.Close)

	if err := db.Ping(ctx); err != nil {
		t.Fatalf("ping: %v", err)
	}

	info, err := db.Queries.ServerInfo(ctx)
	if err != nil {
		t.Fatalf("server info: %v", err)
	}
	if info.PostgisVersion == "" {
		t.Fatalf("PostgisVersion is empty, want a version string")
	}
	if info.Now.IsZero() {
		t.Fatalf("Now is zero, want a non-zero timestamp")
	}
}
