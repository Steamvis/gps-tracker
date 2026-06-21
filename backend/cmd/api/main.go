// Command api serves the gps-api HTTP transport. Milestone M0 Task 5 adds OTel
// (traces, metrics, logs) wired before logging; the -health subcommand is added
// in Task 6.
package main

import (
	"context"
	"fmt"
	"os"
	"os/signal"
	"syscall"
	"time"

	"github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres"
	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
	platformlog "github.com/Steamvis/gps-tracker/backend/internal/platform/log"
	platformotel "github.com/Steamvis/gps-tracker/backend/internal/platform/otel"
	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
	"github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo"
)

func main() {
	cfg, err := config.Load()
	if err != nil {
		println("config load failed:", err.Error())
		os.Exit(1)
	}

	ctx, stop := signal.NotifyContext(context.Background(), syscall.SIGINT, syscall.SIGTERM)
	defer stop()

	// Install global OTel providers before building the logger, so the otelslog
	// bridge handler resolves the LoggerProvider configured here.
	otelShutdown, err := platformotel.Setup(ctx, cfg)
	if err != nil {
		println("otel setup failed:", err.Error())
		os.Exit(1)
	}
	defer func() {
		shutdownCtx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
		defer cancel()
		if err := otelShutdown(shutdownCtx); err != nil {
			// The logger may already be torn down here, so report to stderr.
			fmt.Fprintln(os.Stderr, "otel shutdown failed:", err)
		}
	}()

	logger := platformlog.New(cfg)

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
