package http

import (
	"context"
	"errors"
	"log/slog"
	"net/http"
	"time"
)

// Server wraps an http.Server with graceful shutdown driven by a context.
type Server struct {
	srv *http.Server
	log *slog.Logger
}

// NewServer constructs a Server listening on addr and serving h.
func NewServer(addr string, h http.Handler, log *slog.Logger) *Server {
	return &Server{
		srv: &http.Server{
			Addr:              addr,
			Handler:           h,
			ReadHeaderTimeout: 10 * time.Second,
		},
		log: log,
	}
}

// Run serves until ctx is canceled, then performs a graceful shutdown with a
// 10-second deadline. It returns nil on a clean shutdown.
func (s *Server) Run(ctx context.Context) error {
	errCh := make(chan error, 1)
	go func() {
		s.log.Info("http server listening", slog.String("addr", s.srv.Addr))
		if err := s.srv.ListenAndServe(); err != nil && !errors.Is(err, http.ErrServerClosed) {
			errCh <- err
			return
		}
		errCh <- nil
	}()

	select {
	case err := <-errCh:
		return err
	case <-ctx.Done():
		s.log.Info("http server shutting down")
		shutdownCtx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
		defer cancel()
		return s.srv.Shutdown(shutdownCtx)
	}
}
