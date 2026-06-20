package postgres

import (
	"context"
	"fmt"

	"github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo"
)

// compile-time assertion that *DB satisfies the serverinfo.Repository port.
var _ serverinfo.Repository = (*DB)(nil)

// ServerInfo runs the sqlc ServerInfo query and maps the row to serverinfo.Info.
func (db *DB) ServerInfo(ctx context.Context) (serverinfo.Info, error) {
	row, err := db.Queries.ServerInfo(ctx)
	if err != nil {
		return serverinfo.Info{}, fmt.Errorf("postgres: server info: %w", err)
	}
	return serverinfo.Info{
		Time:    row.Now,
		PostGIS: row.PostgisVersion,
	}, nil
}
