// Package serverinfo is the application service for the server-info endpoint:
// it exposes a Repository port and a Service that fetches database/server facts.
package serverinfo

import (
	"context"
	"time"
)

// Info is the server/database fact returned to callers.
type Info struct {
	Time    time.Time
	PostGIS string
}

// Repository is the driven port the Service depends on.
type Repository interface {
	ServerInfo(ctx context.Context) (Info, error)
}

// Service orchestrates the server-info use case.
type Service struct {
	repo Repository
}

// New builds a Service backed by repo.
func New(repo Repository) *Service {
	return &Service{repo: repo}
}

// Get returns the current server Info from the repository.
func (s *Service) Get(ctx context.Context) (Info, error) {
	return s.repo.ServerInfo(ctx)
}
