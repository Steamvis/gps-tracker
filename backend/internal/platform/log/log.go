// Package log builds the service's slog.Logger from configuration.
package log

import (
	"log/slog"
	"os"

	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
)

// New returns a JSON slog.Logger writing to stdout at the level named by
// cfg.LogLevel (debug, info, warn, error). Unknown levels fall back to info.
func New(cfg config.Config) *slog.Logger {
	var level slog.Level
	switch cfg.LogLevel {
	case "debug":
		level = slog.LevelDebug
	case "warn":
		level = slog.LevelWarn
	case "error":
		level = slog.LevelError
	default:
		level = slog.LevelInfo
	}

	handler := slog.NewJSONHandler(os.Stdout, &slog.HandlerOptions{Level: level})
	return slog.New(handler).With(
		slog.String("service", cfg.ServiceName),
		slog.String("version", cfg.Version),
		slog.String("env", cfg.Env),
	)
}
