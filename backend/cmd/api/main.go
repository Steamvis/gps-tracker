// Command api serves the gps-api HTTP transport. In milestone M0 Task 4 it
// serves the full end-to-end /api/v1/server-info slice; OTel wiring follows in
// Task 5 and the -health subcommand in Task 6.
package main

import (
	"context"
	"os"
	"os/signal"
	"syscall"

	"github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres"
	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
	platformlog "github.com/Steamvis/gps-tracker/backend/internal/platform/log"
	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
	"github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo"
)

func main() {
	cfg, err := config.Load()
	if err != nil {
		println("config load failed:", err.Error())
		os.Exit(1)
	}

	logger := platformlog.New(cfg)

	ctx, stop := signal.NotifyContext(context.Background(), syscall.SIGINT, syscall.SIGTERM)
	defer stop()

	if err := postgres.Migrate(ctx, cfg); err != nil {
		logger.Error("migrate failed", "error", err)
		os.Exit(1)
	}

	db, err := postgres.New(ctx, cfg)
	if err != nil {
		logger.Error("postgres connect failed", "error", err)
		os.Exit(1)
	}
	defer db.Close()

	srvInfo := serverinfo.New(db)

	router := transporthttp.NewRouter(transporthttp.Deps{
		Log:        logger,
		ServerInfo: srvInfo,
		Version:    cfg.Version,
		Ready: []transporthttp.ReadyCheck{
			{Name: "postgres", Check: db.Ping},
		},
	})

	server := transporthttp.NewServer(cfg.HTTPAddr, router, logger)

	if err := server.Run(ctx); err != nil {
		logger.Error("server exited with error", "error", err)
		os.Exit(1)
	}
}
