// Command api serves the gps-api HTTP transport. The -health subcommand
// performs the in-container liveness probe used by the Docker HEALTHCHECK.
package main

import (
	"context"
	"flag"
	"fmt"
	"net/http"
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
	health := flag.Bool("health", false, "perform an HTTP health check against http://localhost:8080/healthz and exit")
	flag.Parse()
	if *health {
		if err := healthCheck(); err != nil {
			fmt.Fprintln(os.Stderr, "health check failed:", err)
			os.Exit(1)
		}
		os.Exit(0)
	}

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

// healthCheck performs the in-container liveness probe used by the Docker
// HEALTHCHECK and by docker-compose. It GETs the local /healthz endpoint and
// returns nil only when the server answers HTTP 200.
func healthCheck() error {
	client := &http.Client{Timeout: 3 * time.Second}
	resp, err := client.Get("http://localhost:8080/healthz")
	if err != nil {
		return err
	}
	defer func() { _ = resp.Body.Close() }()
	if resp.StatusCode != http.StatusOK {
		return fmt.Errorf("healthz returned status %d", resp.StatusCode)
	}
	return nil
}
