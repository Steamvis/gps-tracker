// Package log builds the application slog.Logger.
package log

import (
	"context"
	"log/slog"
	"os"

	"go.opentelemetry.io/contrib/bridges/otelslog"

	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
)

// New returns a slog.Logger that writes JSON to stdout and, in parallel, feeds
// every record to the OTel log bridge (exported via the global LoggerProvider
// configured by otel.Setup). The level is taken from cfg.LogLevel.
func New(cfg config.Config) *slog.Logger {
	level := parseLevel(cfg.LogLevel)
	jsonHandler := slog.NewJSONHandler(os.Stdout, &slog.HandlerOptions{Level: level})
	otelHandler := otelslog.NewHandler(cfg.ServiceName)
	return slog.New(newFanout(jsonHandler, otelHandler))
}

func parseLevel(s string) slog.Level {
	switch s {
	case "debug":
		return slog.LevelDebug
	case "warn":
		return slog.LevelWarn
	case "error":
		return slog.LevelError
	default:
		return slog.LevelInfo
	}
}

// fanout is a slog.Handler that dispatches every record to a set of underlying
// handlers, so logs reach both stdout (JSON) and the OTel log pipeline.
type fanout struct {
	handlers []slog.Handler
}

func newFanout(h ...slog.Handler) *fanout { return &fanout{handlers: h} }

func (f *fanout) Enabled(ctx context.Context, level slog.Level) bool {
	for _, h := range f.handlers {
		if h.Enabled(ctx, level) {
			return true
		}
	}
	return false
}

func (f *fanout) Handle(ctx context.Context, r slog.Record) error {
	for _, h := range f.handlers {
		if !h.Enabled(ctx, r.Level) {
			continue
		}
		if err := h.Handle(ctx, r.Clone()); err != nil {
			return err
		}
	}
	return nil
}

func (f *fanout) WithAttrs(attrs []slog.Attr) slog.Handler {
	next := make([]slog.Handler, len(f.handlers))
	for i, h := range f.handlers {
		next[i] = h.WithAttrs(attrs)
	}
	return &fanout{handlers: next}
}

func (f *fanout) WithGroup(name string) slog.Handler {
	next := make([]slog.Handler, len(f.handlers))
	for i, h := range f.handlers {
		next[i] = h.WithGroup(name)
	}
	return &fanout{handlers: next}
}
