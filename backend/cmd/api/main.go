// Command api serves the gps-api HTTP transport. In milestone M0 Task 2 it
// exposes only GET /healthz; readiness, server-info, database and OTel wiring
// follow in Tasks 3, 4 and 5, and the -health subcommand in Task 6.
package main

import (
	"context"
	"os"
	"os/signal"
	"syscall"

	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
	platformlog "github.com/Steamvis/gps-tracker/backend/internal/platform/log"
	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
)

func main() {
	cfg, err := config.Load()
	if err != nil {
		// No logger yet; fail loudly on stderr.
		println("config load failed:", err.Error())
		os.Exit(1)
	}

	logger := platformlog.New(cfg)

	router := transporthttp.NewRouter(transporthttp.Deps{
		Log:     logger,
		Version: cfg.Version,
		// ServerInfo and Ready are wired in Tasks 4 and 3 respectively.
	})

	server := transporthttp.NewServer(cfg.HTTPAddr, router, logger)

	ctx, stop := signal.NotifyContext(context.Background(), syscall.SIGINT, syscall.SIGTERM)
	defer stop()

	if err := server.Run(ctx); err != nil {
		logger.Error("server exited with error", "error", err)
		os.Exit(1)
	}
}
