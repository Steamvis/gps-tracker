// Package http wires the gps-api HTTP transport: the chi router, middleware
// chain, handlers and the graceful HTTP server.
package http

import (
	"context"
	"log/slog"
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"

	"github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo"
)

// ReadyCheck is a named readiness dependency. It is consumed by /readyz, which
// is wired in Task 3; the type is declared here (once) so Deps matches the
// frozen contract.
type ReadyCheck struct {
	Name  string
	Check func(context.Context) error
}

// Deps holds everything the router needs. In Task 2 only Log and Version are
// used; ServerInfo may be nil and Ready may be nil until Tasks 4 and 3.
type Deps struct {
	Log        *slog.Logger
	ServerInfo *serverinfo.Service
	Ready      []ReadyCheck
	Version    string
}

// NewRouter builds the chi router with the standard middleware and the
// currently-available routes. Task 2 mounts only GET /healthz; /readyz and
// /api/v1/server-info are added in Tasks 3 and 4, and the full middleware
// chain is completed in Task 5.
func NewRouter(d Deps) http.Handler {
	r := chi.NewRouter()

	r.Use(middleware.RequestID)
	r.Use(middleware.Recoverer)

	r.Get("/healthz", healthz)
	r.Get("/readyz", NewReadyHandler(d.Ready))

	return r
}
