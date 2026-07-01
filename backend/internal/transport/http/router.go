// Package http wires the gps-api HTTP transport: the chi router, middleware
// chain, handlers and the graceful HTTP server.
package http

import (
	"context"
	"log/slog"
	"net/http"

	"github.com/go-chi/chi/v5"
	"go.opentelemetry.io/contrib/instrumentation/net/http/otelhttp"

	"github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo"
)

// ReadyCheck is a named readiness dependency.
type ReadyCheck struct {
	Name  string
	Check func(context.Context) error
}

// Deps holds everything the router needs to serve its endpoints.
type Deps struct {
	Log        *slog.Logger
	ServerInfo *serverinfo.Service
	Ready      []ReadyCheck
	Version    string
}

// NewRouter builds the chi router, mounts the three endpoints and wraps the
// whole handler in the contract middleware chain (outer->inner):
// requestID -> recoverer -> accessLog -> otelhttp("gps-api").
func NewRouter(d Deps) http.Handler {
	r := chi.NewRouter()

	r.Get("/healthz", healthz)
	r.Get("/readyz", NewReadyHandler(d.Ready))
	r.Get("/api/v1/server-info", serverInfoHandler(d.ServerInfo, d.Version, d.Log))

	var h http.Handler = r
	// otelhttp >=0.69 defaults the span name to the HTTP method; keep naming the
	// server span after the operation ("gps-api") to preserve the span-name contract.
	h = otelhttp.NewHandler(h, "gps-api",
		otelhttp.WithSpanNameFormatter(func(operation string, _ *http.Request) string {
			return operation
		}),
	)
	h = accessLog(d.Log)(h)
	h = recoverer(d.Log)(h)
	h = requestID(h)
	return h
}
