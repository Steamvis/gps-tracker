// Package postgres is the PostgreSQL/PostGIS adapter: a pgx connection pool, the
// sqlc-generated query set, and the embedded goose migration runner.
package postgres

import (
	"context"
	"embed"
	"fmt"

	"github.com/jackc/pgx/v5/pgxpool"
	"github.com/jackc/pgx/v5/stdlib"
	"github.com/pressly/goose/v3"

	"github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres/sqlcgen"
	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
)

//go:generate sqlc generate

//go:embed db/migrations/*.sql
var embedMigrations embed.FS

// DB bundles the pgx pool and the sqlc-generated query set.
type DB struct {
	Pool    *pgxpool.Pool
	Queries *sqlcgen.Queries
}

// New opens a pgx connection pool against cfg.DatabaseURL and verifies it with
// a Ping before returning.
func New(ctx context.Context, cfg config.Config) (*DB, error) {
	pool, err := pgxpool.New(ctx, cfg.DatabaseURL)
	if err != nil {
		return nil, fmt.Errorf("postgres: new pool: %w", err)
	}
	if err := pool.Ping(ctx); err != nil {
		pool.Close()
		return nil, fmt.Errorf("postgres: ping: %w", err)
	}
	return &DB{
		Pool:    pool,
		Queries: sqlcgen.New(pool),
	}, nil
}

// Ping checks pool connectivity; used as the "postgres" readiness check.
func (db *DB) Ping(ctx context.Context) error {
	return db.Pool.Ping(ctx)
}

// Close releases all pooled connections.
func (db *DB) Close() {
	db.Pool.Close()
}

// Migrate runs all embedded goose Up migrations against cfg.DatabaseURL. It
// uses a short-lived database/sql connection via the pgx stdlib driver, because
// goose's migration runner operates on *sql.DB.
func Migrate(ctx context.Context, cfg config.Config) error {
	connCfg, err := pgxpool.ParseConfig(cfg.DatabaseURL)
	if err != nil {
		return fmt.Errorf("postgres: parse migrate dsn: %w", err)
	}
	db := stdlib.OpenDB(*connCfg.ConnConfig)
	defer func() { _ = db.Close() }()

	if err := db.PingContext(ctx); err != nil {
		return fmt.Errorf("postgres: migrate ping: %w", err)
	}

	goose.SetBaseFS(embedMigrations)
	if err := goose.SetDialect("postgres"); err != nil {
		return fmt.Errorf("postgres: set goose dialect: %w", err)
	}
	if err := goose.UpContext(ctx, db, "db/migrations"); err != nil {
		return fmt.Errorf("postgres: run migrations: %w", err)
	}
	return nil
}
