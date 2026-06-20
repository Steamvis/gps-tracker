# GPS Fleet Tracker v2 — M0 (Foundation) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up the "walking skeleton" of the rewrite — a modular-monolith Go backend (chi + pgx + sqlc, clean architecture, OpenTelemetry) talking to Postgres+PostGIS, a React/Vite/Tailwind frontend, and a full local docker-compose stack with observability — proving one end-to-end request path with green telemetry and CI.

**Architecture:** One Go module (`/backend`) with clean layering — `internal/platform` (config, logging, OTel), `internal/adapter` (postgres via pgx/sqlc), `internal/usecase` (ports + services), `internal/transport/http` (chi handlers + middleware). A single binary `cmd/api` for M0 (`cmd/ingest`/`cmd/worker` arrive in M3). The React SPA (`/frontend`) calls the API through an nginx reverse proxy. Everything runs via `deploy/docker-compose.yml`; telemetry flows app → OTel Collector → Tempo (traces) / Prometheus (metrics) / Loki (logs) → Grafana.

**Tech Stack:** Go 1.22 · chi v5 · pgx v5 / pgxpool · sqlc (v2) · goose (embedded migrations) · OpenTelemetry Go SDK + otelslog + otelhttp · testcontainers-go · PostgreSQL 16 + PostGIS 3.4 · React 18 + Vite + TypeScript + TailwindCSS + TanStack Query v5 · nginx · Docker / docker-compose · OTel Collector (contrib) · Tempo · Loki · Prometheus · Grafana · go-task (Taskfile) · GitHub Actions · golangci-lint.

## Global Constraints

These apply to **every** task; each task's requirements implicitly include this section.

- **Branch:** all work lands on `feat/gps-tracker-v2` (already checked out).
- **Go module path:** `github.com/Steamvis/gps-tracker/backend` · **Go version:** 1.22.
- **Env vars use the `GPS_` prefix.** Defaults (also the compose values):
  `GPS_ENV=dev` · `GPS_SERVICE_NAME=gps-api` · `GPS_VERSION=dev` · `GPS_HTTP_ADDR=:8080` ·
  `GPS_DATABASE_URL=postgres://gps:gps@postgres:5432/gps?sslmode=disable` ·
  `GPS_OTLP_ENDPOINT=otel-collector:4317` · `GPS_LOG_LEVEL=info`.
- **Commits:** conventional (`feat:`/`chore:`/`test:`/`ci:`/`docs:`), small and frequent (commit at the end of each task). Every commit ends with the trailer:
  `Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>`.
- **Discipline:** TDD for all Go/TS code (failing test → minimal impl → green → commit). DRY. YAGNI. No placeholders, no `TODO`s left in code.
- **No new domain features in M0** — only the `server-info` slice exists to prove the path. Auth, vehicles, ingestion, live tracking, etc. belong to M1–M6.

## Shared Contract (frozen — tasks reference these exact names)

**Repo layout (Task 1 produces it):** `/backend` (Go), `/frontend` (React), `/deploy` (compose + observability config), `/tools` (empty in M0), `/legacy` (archived PHP). `docs/superpowers/{specs,plans}` stay at repo root.

**Go packages & signatures:**

```go
// internal/platform/config
type Config struct{ Env, ServiceName, Version, HTTPAddr, DatabaseURL, OTLPEndpoint, LogLevel string }
func Load() (Config, error)                                   // GPS_ env + defaults

// internal/platform/log
func New(cfg config.Config) *slog.Logger                      // slog JSON, level from cfg.LogLevel

// internal/platform/otel
func Setup(ctx context.Context, cfg config.Config) (shutdown func(context.Context) error, err error)

// internal/adapter/postgres
type DB struct{ Pool *pgxpool.Pool; Queries *sqlcgen.Queries }
func New(ctx context.Context, cfg config.Config) (*DB, error)
func (db *DB) Ping(ctx context.Context) error
func (db *DB) Close()
func Migrate(ctx context.Context, cfg config.Config) error    // //go:embed db/migrations, goose Up
func (db *DB) ServerInfo(ctx context.Context) (serverinfo.Info, error) // satisfies serverinfo.Repository

// sqlc generated: import .../internal/adapter/postgres/sqlcgen  (package sqlcgen)
//   (*Queries).ServerInfo(ctx) (ServerInfoRow, error); ServerInfoRow{ Now time.Time; PostgisVersion string }

// internal/usecase/serverinfo
type Info struct{ Time time.Time; PostGIS string }
type Repository interface{ ServerInfo(ctx context.Context) (Info, error) }
type Service struct{ /* repo Repository */ }
func New(repo Repository) *Service
func (s *Service) Get(ctx context.Context) (Info, error)

// internal/transport/http
type ReadyCheck struct{ Name string; Check func(context.Context) error }
type Deps struct{ Log *slog.Logger; ServerInfo *serverinfo.Service; Ready []ReadyCheck; Version string }
func NewRouter(d Deps) http.Handler
type Server struct{ /* http.Server + log */ }
func NewServer(addr string, h http.Handler, log *slog.Logger) *Server
func (s *Server) Run(ctx context.Context) error              // graceful shutdown on ctx cancel
```

**HTTP endpoints:**
- `GET /healthz` → `200 {"status":"ok"}` (no deps)
- `GET /readyz` → `200 {"status":"ok","checks":{...}}` or `503 {"status":"degraded","checks":{...}}`
- `GET /api/v1/server-info` → `200 {"app":"gps-tracker","version":"<cfg.Version>","time":"<RFC3339>","postgis":"<version>"}`
- Middleware order (outer→inner): `requestID → recoverer → accessLog(slog) → otelhttp("gps-api")`
- `api -health` performs `GET http://localhost:8080/healthz`, exit 0 on 200 (Docker HEALTHCHECK).

**Compose services / ports (host:container):** postgres `postgis/postgis:16-3.4` 5432 · redis `redis:7-alpine` 6379 · mosquitto `eclipse-mosquitto:2` 1883 · minio `minio/minio` 9000/9001 (`minio`/`minio12345`) · mailpit `axllent/mailpit` 1025/8025 · otel-collector `otel/opentelemetry-collector-contrib:0.110.0` 4317/4318/8889 · tempo `grafana/tempo` 3200 (OTLP at `tempo:4317`, internal-only) · loki `grafana/loki` 3100 · prometheus `prom/prometheus` 9090 (scrapes `otel-collector:8889`) · grafana `grafana/grafana` 3000 · api `build ../backend` 8080 (runs `Migrate` on boot) · frontend `build ../frontend` 8081→80 (nginx proxies `/api`,`/ws` → `api:8080`).

**Frontend:** Vite + React 18 + TS, Tailwind, TanStack Query v5; dev proxy `/api → http://localhost:8080`; `src/api/client.ts` exports `type ServerInfo = { app; version; time; postgis }` and `getServerInfo(): Promise<ServerInfo>`; one page renders it via `useQuery({ queryKey:['server-info'], queryFn:getServerInfo })`.

---

### Task 1: Monorepo restructure & legacy archive

**Files:**
- Create: `legacy/` (directory, target for archived PHP app)
- Modify (git mv into `legacy/`): `src/` -> `legacy/src/`, `docker/` -> `legacy/docker/`, `docker-compose.yml` -> `legacy/docker-compose.yml`, `Makefile` -> `legacy/Makefile`, `environment/` -> `legacy/environment/`, `ABOUT.md` -> `legacy/ABOUT.md`, `CHANGELOG.md` -> `legacy/CHANGELOG.md`, `docs/api.md` -> `legacy/docs/api.md`
- Create: `backend/.gitkeep`, `frontend/.gitkeep`, `deploy/.gitkeep`, `tools/.gitkeep`
- Modify: `README.md` (replace legacy PHP README with v2 stub)
- Modify: `.gitignore` (append Go / Node / env block)
- Test: `git status` (clean), `ls` (new layout), `test -d legacy/src/backend && echo ok`

**Interfaces:**
- Consumes: nothing (first task; no Go packages, no contract signatures yet).
- Produces: the frozen repo layout — top-level `/backend`, `/frontend`, `/deploy`, `/tools`, `/legacy`, with `docs/superpowers/{specs,plans}` kept at repo root (NOT moved). This is the directory skeleton every later task builds into.

- [ ] **Step 1: Create the `legacy/` directory and its `docs/` subdir, then verify they exist.**
  Run the exact commands:
  ```bash
  mkdir -p legacy/docs
  test -d legacy && test -d legacy/docs && echo "legacy dirs ready"
  ```
  Expected output:
  ```
  legacy dirs ready
  ```
  (Empty dirs are not yet tracked by git; they become tracked when `git mv` populates them in the next step.)

- [ ] **Step 2: `git mv` the legacy artifacts into `legacy/`.**
  Run each move with `git mv` so history is preserved:
  ```bash
  git mv src legacy/src
  git mv docker legacy/docker
  git mv docker-compose.yml legacy/docker-compose.yml
  git mv Makefile legacy/Makefile
  git mv environment legacy/environment
  git mv ABOUT.md legacy/ABOUT.md
  git mv CHANGELOG.md legacy/CHANGELOG.md
  git mv docs/api.md legacy/docs/api.md
  ```
  Verify the moves landed and `docs/superpowers/` stayed at the repo root:
  ```bash
  test -d legacy/src/backend && test -f legacy/docker-compose.yml && test -f legacy/Makefile && \
  test -d legacy/environment && test -f legacy/ABOUT.md && test -f legacy/CHANGELOG.md && \
  test -f legacy/docs/api.md && test -d docs/superpowers/specs && ! test -e docs/api.md && \
  echo "legacy archive ok"
  ```
  Expected output:
  ```
  legacy archive ok
  ```

- [ ] **Step 3: Create the four empty top-level dirs with `.gitkeep` so git tracks them.**
  Run:
  ```bash
  mkdir -p backend frontend deploy tools
  touch backend/.gitkeep frontend/.gitkeep deploy/.gitkeep tools/.gitkeep
  ls backend/.gitkeep frontend/.gitkeep deploy/.gitkeep tools/.gitkeep
  ```
  Expected output:
  ```
  backend/.gitkeep	deploy/.gitkeep	frontend/.gitkeep	tools/.gitkeep
  ```

- [ ] **Step 4: Replace the root `README.md` with the v2 stub.**
  Overwrite `README.md` with exactly this content (the full README is authored in Task 13; this is a minimal real stub, not a TODO):
  ```markdown
  # gps-tracker

  GPS fleet-tracker: Go backend + React frontend (v2 rewrite).

  See the design spec: [docs/superpowers/specs/2026-06-20-gps-tracker-v2-design.md](docs/superpowers/specs/2026-06-20-gps-tracker-v2-design.md).

  The previous Laravel/PHP application is archived under [`legacy/`](legacy/).

  ## Quickstart

  ```bash
  task up
  ```

  (The `task up` target is added in a later task.)
  ```
  Verify the new content is in place and the old PHP README is gone:
  ```bash
  head -n 1 README.md && grep -q "GPS fleet-tracker: Go backend" README.md && ! grep -q "laravel-crm" README.md && echo "readme ok"
  ```
  Expected output:
  ```
  # gps-tracker
  readme ok
  ```

- [ ] **Step 5: Append the Go / Node / env ignore block to `.gitignore`.**
  Append exactly this block to the end of the existing `.gitignore` (keep the current `logs`, `runtime`, IDE, temp, `.DS_Store`, `*.cache` entries above it):
  ```gitignore

  # Go
  /bin/
  *.test
  coverage.out

  # Node
  node_modules/
  dist/

  # env / local
  deploy/.env
  *.local
  ```
  Verify the new entries are present and the old ones are retained:
  ```bash
  grep -qx "coverage.out" .gitignore && grep -qx "node_modules/" .gitignore && \
  grep -qx "dist/" .gitignore && grep -qx "deploy/.env" .gitignore && \
  grep -qx "*.local" .gitignore && grep -qx "logs" .gitignore && echo "gitignore ok"
  ```
  Expected output:
  ```
  gitignore ok
  ```

- [ ] **Step 6: Stage everything and confirm the new layout before committing.**
  Run:
  ```bash
  git add -A
  ls
  ```
  Expected `ls` output (alphabetical; no `src`, `docker`, `Makefile`, `ABOUT.md`, `CHANGELOG.md`, `docker-compose.yml`, `environment` at root anymore):
  ```
  README.md	backend		deploy		docs		frontend	legacy		tools
  ```
  Then confirm the legacy backend landed:
  ```bash
  test -d legacy/src/backend && echo ok
  ```
  Expected output:
  ```
  ok
  ```

- [ ] **Step 7: Commit the restructure.**
  Run:
  ```bash
  git commit -m "chore: restructure monorepo and archive legacy PHP app" \
    -m "Move src/, docker/, docker-compose.yml, Makefile, environment/, ABOUT.md, CHANGELOG.md and docs/api.md into legacy/. Scaffold backend/, frontend/, deploy/, tools/ with .gitkeep. Add v2 stub README and Go/Node/env gitignore rules. Keep docs/superpowers/ at repo root." \
    -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Verify the working tree is clean:
  ```bash
  git status --porcelain && echo "clean: $?"
  ```
  Expected output (no file lines printed; only the trailer):
  ```
  clean: 0
  ```

---

### Task 2: Go module + config + logging + liveness health endpoint

**Files:**
- Create: `backend/go.mod`
- Create: `backend/internal/platform/config/config.go`
- Create: `backend/internal/platform/log/log.go`
- Create: `backend/internal/usecase/serverinfo/serverinfo.go` (minimal `type Service struct{}` stub; Task 4 replaces it)
- Create: `backend/internal/transport/http/health.go`
- Create: `backend/internal/transport/http/router.go`
- Create: `backend/internal/transport/http/server.go`
- Create: `backend/cmd/api/main.go`
- Test: `backend/internal/platform/config/config_test.go`
- Test: `backend/internal/transport/http/health_test.go`

**Interfaces:**
- Produces: `config.Config{ Env, ServiceName, Version, HTTPAddr, DatabaseURL, OTLPEndpoint, LogLevel string }`; `func config.Load() (Config, error)` (reads `GPS_` env, contract defaults).
- Produces: `func log.New(cfg config.Config) *slog.Logger` (slog JSON handler, level from `cfg.LogLevel`).
- Produces (stub): `internal/usecase/serverinfo` — `type Service struct{}` ONLY (so `Deps.ServerInfo *serverinfo.Service` compiles). Task 4 REPLACES this file with the full `Info`/`Repository`/`Service{repo Repository}`/`New`/`Get`.
- Produces: `internal/transport/http` — declared EXACTLY ONCE here: `type ReadyCheck struct{ Name string; Check func(context.Context) error }`; `type Deps struct{ Log *slog.Logger; ServerInfo *serverinfo.Service; Ready []ReadyCheck; Version string }`; `func NewRouter(d Deps) http.Handler`; the liveness handler `healthz` (an `http.HandlerFunc`); `func writeJSON(w http.ResponseWriter, status int, v any)`; `type Server struct{...}`; `func NewServer(addr string, h http.Handler, log *slog.Logger) *Server`; `func (s *Server) Run(ctx context.Context) error`.
- Note: In THIS task `NewRouter` mounts ONLY `GET /healthz`. `Deps.ServerInfo` may be nil and `Deps.Ready` may be nil. `/readyz` is added in Task 3 (`NewReadyHandler`) and `/api/v1/server-info` in Task 4. The middleware chain is completed in Task 5; Task 2 installs only chi `RequestID` + `Recoverer` to establish the package.

- [ ] **Step 1: Initialize the Go module and add chi v5.**
  Run:
  ```
  cd backend && go mod init github.com/Steamvis/gps-tracker/backend && go mod edit -go=1.22 && go get github.com/go-chi/chi/v5@v5.1.0
  ```
  Verify:
  ```
  cd backend && grep -E 'module github.com/Steamvis/gps-tracker/backend|^go 1.22|go-chi/chi/v5' go.mod
  ```
  Expected output (three lines):
  ```
  module github.com/Steamvis/gps-tracker/backend
  go 1.22
  	github.com/go-chi/chi/v5 v5.1.0
  ```

- [ ] **Step 2: Commit the module init.**
  Run:
  ```
  cd backend && git add go.mod go.sum && git commit -m "chore: init backend go module with chi v5" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] chore: init backend go module with chi v5
  ```

- [ ] **Step 3: Write the failing config test.**
  Create `backend/internal/platform/config/config_test.go`:
  ```go
  package config_test

  import (
  	"testing"

  	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
  )

  func TestLoadDefaults(t *testing.T) {
  	t.Setenv("GPS_ENV", "")
  	t.Setenv("GPS_SERVICE_NAME", "")
  	t.Setenv("GPS_VERSION", "")
  	t.Setenv("GPS_HTTP_ADDR", "")
  	t.Setenv("GPS_DATABASE_URL", "")
  	t.Setenv("GPS_OTLP_ENDPOINT", "")
  	t.Setenv("GPS_LOG_LEVEL", "")

  	cfg, err := config.Load()
  	if err != nil {
  		t.Fatalf("Load() returned error: %v", err)
  	}

  	cases := []struct {
  		name, got, want string
  	}{
  		{"Env", cfg.Env, "dev"},
  		{"ServiceName", cfg.ServiceName, "gps-api"},
  		{"Version", cfg.Version, "dev"},
  		{"HTTPAddr", cfg.HTTPAddr, ":8080"},
  		{"DatabaseURL", cfg.DatabaseURL, "postgres://gps:gps@postgres:5432/gps?sslmode=disable"},
  		{"OTLPEndpoint", cfg.OTLPEndpoint, "otel-collector:4317"},
  		{"LogLevel", cfg.LogLevel, "info"},
  	}
  	for _, c := range cases {
  		if c.got != c.want {
  			t.Errorf("%s = %q, want %q", c.name, c.got, c.want)
  		}
  	}
  }

  func TestLoadHTTPAddrOverride(t *testing.T) {
  	t.Setenv("GPS_HTTP_ADDR", ":9999")

  	cfg, err := config.Load()
  	if err != nil {
  		t.Fatalf("Load() returned error: %v", err)
  	}
  	if cfg.HTTPAddr != ":9999" {
  		t.Errorf("HTTPAddr = %q, want %q", cfg.HTTPAddr, ":9999")
  	}
  }
  ```

- [ ] **Step 4: Run the config test and confirm it FAILS to compile.**
  Run:
  ```
  cd backend && go test ./internal/platform/config/...
  ```
  Expected FAIL output (no implementation yet):
  ```
  internal/platform/config/config_test.go:6:2: package github.com/Steamvis/gps-tracker/backend/internal/platform/config is not in std (...)
  FAIL	github.com/Steamvis/gps-tracker/backend/internal/platform/config [build failed]
  ```

- [ ] **Step 5: Write the config implementation.**
  Create `backend/internal/platform/config/config.go`:
  ```go
  // Package config loads service configuration from GPS_-prefixed environment
  // variables, applying defaults for local development.
  package config

  import "os"

  // Config holds all runtime configuration for the gps-api service.
  type Config struct {
  	Env          string
  	ServiceName  string
  	Version      string
  	HTTPAddr     string
  	DatabaseURL  string
  	OTLPEndpoint string
  	LogLevel     string
  }

  // Load reads configuration from the environment (GPS_ prefix) and applies
  // defaults for any unset or empty variable.
  func Load() (Config, error) {
  	return Config{
  		Env:          env("GPS_ENV", "dev"),
  		ServiceName:  env("GPS_SERVICE_NAME", "gps-api"),
  		Version:      env("GPS_VERSION", "dev"),
  		HTTPAddr:     env("GPS_HTTP_ADDR", ":8080"),
  		DatabaseURL:  env("GPS_DATABASE_URL", "postgres://gps:gps@postgres:5432/gps?sslmode=disable"),
  		OTLPEndpoint: env("GPS_OTLP_ENDPOINT", "otel-collector:4317"),
  		LogLevel:     env("GPS_LOG_LEVEL", "info"),
  	}, nil
  }

  // env returns the value of key, or def when key is unset or empty.
  func env(key, def string) string {
  	if v := os.Getenv(key); v != "" {
  		return v
  	}
  	return def
  }
  ```

- [ ] **Step 6: Run the config test and confirm it PASSES.**
  Run:
  ```
  cd backend && go test ./internal/platform/config/...
  ```
  Expected PASS output:
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/platform/config	0.00Xs
  ```

- [ ] **Step 7: Commit the config loader.**
  Run:
  ```
  cd backend && git add internal/platform/config && git commit -m "feat: add platform config loader with GPS_ env defaults" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat: add platform config loader with GPS_ env defaults
  ```

- [ ] **Step 8: Write the platform logger.**
  Create `backend/internal/platform/log/log.go` (pure slog wiring; exercised via compile and downstream http tests):
  ```go
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
  ```

- [ ] **Step 9: Build the logger and commit.**
  Run:
  ```
  cd backend && go build ./internal/platform/log/...
  ```
  Expected: no output (exit 0). Then:
  ```
  cd backend && git add internal/platform/log && git commit -m "feat: add platform slog JSON logger" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat: add platform slog JSON logger
  ```

- [ ] **Step 10: Create the minimal serverinfo stub so the frozen Deps signature compiles.**
  Create `backend/internal/usecase/serverinfo/serverinfo.go`. This task declares ONLY the empty `Service` type referenced by `http.Deps.ServerInfo`. Task 4 REPLACES this entire file with the full implementation (`Info`, `Repository`, `Service{repo Repository}`, `New`, `Get`):
  ```go
  // Package serverinfo is the application service exposing database server
  // metadata. Task 2 declares only the Service type so the HTTP Deps struct
  // compiles; the Info type, Repository port, New and Get arrive in Task 4,
  // which replaces this file.
  package serverinfo

  // Service serves server metadata. Fields and methods are added in Task 4.
  type Service struct{}
  ```

- [ ] **Step 11: Build the stub and commit.**
  Run:
  ```
  cd backend && go build ./internal/usecase/serverinfo/...
  ```
  Expected: no output (exit 0). Then:
  ```
  cd backend && git add internal/usecase/serverinfo && git commit -m "feat: add serverinfo service stub for http deps" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat: add serverinfo service stub for http deps
  ```

- [ ] **Step 12: Write the failing health endpoint test.**
  Create `backend/internal/transport/http/health_test.go`:
  ```go
  package http_test

  import (
  	"io"
  	"log/slog"
  	"net/http"
  	"net/http/httptest"
  	"strings"
  	"testing"

  	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
  )

  func TestHealthzOK(t *testing.T) {
  	router := transporthttp.NewRouter(transporthttp.Deps{
  		Log:     slog.New(slog.NewTextHandler(io.Discard, nil)),
  		Version: "test",
  	})

  	req := httptest.NewRequest(http.MethodGet, "/healthz", nil)
  	rec := httptest.NewRecorder()
  	router.ServeHTTP(rec, req)

  	if rec.Code != http.StatusOK {
  		t.Fatalf("status = %d, want %d", rec.Code, http.StatusOK)
  	}

  	body := strings.TrimSpace(rec.Body.String())
  	if body != `{"status":"ok"}` {
  		t.Errorf("body = %q, want %q", body, `{"status":"ok"}`)
  	}

  	if ct := rec.Header().Get("Content-Type"); ct != "application/json" {
  		t.Errorf("Content-Type = %q, want %q", ct, "application/json")
  	}
  }
  ```

- [ ] **Step 13: Run the health test and confirm it FAILS to compile.**
  Run:
  ```
  cd backend && go test ./internal/transport/http/...
  ```
  Expected FAIL output (no implementation yet):
  ```
  internal/transport/http/health_test.go:11:2: package github.com/Steamvis/gps-tracker/backend/internal/transport/http is not in std (...)
  FAIL	github.com/Steamvis/gps-tracker/backend/internal/transport/http [build failed]
  ```

- [ ] **Step 14: Write the liveness handler and the shared writeJSON helper.**
  Create `backend/internal/transport/http/health.go`. `writeJSON` is declared EXACTLY ONCE here and reused by Tasks 3, 4 and 5 (none of them redefines it):
  ```go
  package http

  import (
  	"encoding/json"
  	"net/http"
  )

  // healthz is the liveness probe: it always reports 200 with a static body and
  // has no dependencies. Readiness (/readyz) is added in Task 3.
  func healthz(w http.ResponseWriter, _ *http.Request) {
  	w.Header().Set("Content-Type", "application/json")
  	w.WriteHeader(http.StatusOK)
  	_, _ = w.Write([]byte(`{"status":"ok"}`))
  }

  // writeJSON writes v as an application/json response with the given status
  // code. It is the single JSON-encoding helper for the transport package and is
  // reused by the readiness and server-info handlers in later tasks.
  func writeJSON(w http.ResponseWriter, status int, v any) {
  	w.Header().Set("Content-Type", "application/json")
  	w.WriteHeader(status)
  	_ = json.NewEncoder(w).Encode(v)
  }
  ```

- [ ] **Step 15: Write the router (mounts only GET /healthz).**
  Create `backend/internal/transport/http/router.go`. This file declares the frozen `ReadyCheck` and `Deps` types EXACTLY ONCE; Tasks 3, 4 and 5 must not redeclare them:
  ```go
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

  	return r
  }
  ```

- [ ] **Step 16: Run the health test and confirm it PASSES.**
  Run:
  ```
  cd backend && go mod tidy && go test ./internal/transport/http/...
  ```
  Expected PASS output:
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.00Xs
  ```

- [ ] **Step 17: Commit the router and health handler.**
  Run:
  ```
  cd backend && git add go.mod go.sum internal/transport/http && git commit -m "feat: add chi router with liveness /healthz endpoint" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat: add chi router with liveness /healthz endpoint
  ```

- [ ] **Step 18: Write the graceful HTTP server.**
  Create `backend/internal/transport/http/server.go`:
  ```go
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
  ```

- [ ] **Step 19: Build the server and commit.**
  Run:
  ```
  cd backend && go build ./internal/transport/http/...
  ```
  Expected: no output (exit 0). Then:
  ```
  cd backend && git add internal/transport/http/server.go && git commit -m "feat: add graceful http server with context-driven shutdown" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat: add graceful http server with context-driven shutdown
  ```

- [ ] **Step 20: Write the api entrypoint (full current main.go after Task 2).**
  Create `backend/cmd/api/main.go`. It loads config, builds the logger, builds the router with only health wired (`ServerInfo` and `Ready` left nil — added in Tasks 4/3), constructs the server, and runs it under a SIGINT/SIGTERM-canceled context. NO `-health` flag and NO database wiring in this task:
  ```go
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
  ```

- [ ] **Step 21: Build and vet the whole module.**
  Run:
  ```
  cd backend && go build ./... && go vet ./...
  ```
  Expected: no output (exit 0) for both build and vet.

- [ ] **Step 22: Run the full test suite.**
  Run:
  ```
  cd backend && go test ./...
  ```
  Expected PASS output:
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/platform/config	0.00Xs
  ?   	github.com/Steamvis/gps-tracker/backend/cmd/api	[no test files]
  ?   	github.com/Steamvis/gps-tracker/backend/internal/platform/log	[no test files]
  ?   	github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo	[no test files]
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.00Xs
  ```

- [ ] **Step 23: Smoke-test the running server end to end.**
  Run:
  ```
  cd backend && go run ./cmd/api & API_PID=$!; sleep 2; curl -s localhost:8080/healthz; echo; kill -TERM $API_PID; wait $API_PID 2>/dev/null
  ```
  Expected output (the JSON body; the server then logs its graceful shutdown and exits 0):
  ```
  {"status":"ok"}
  ```

- [ ] **Step 24: Commit the entrypoint.**
  Run:
  ```
  cd backend && git add cmd/api && git commit -m "feat: add api command entrypoint with graceful shutdown" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat: add api command entrypoint with graceful shutdown
  ```

---

### Task 3: Postgres + PostGIS adapter, embedded goose migrations, sqlc, readiness

**Files:**
- Create: `backend/internal/adapter/postgres/db/migrations/0001_init.sql`
- Create: `backend/internal/adapter/postgres/db/queries/server_info.sql`
- Create: `backend/sqlc.yaml`
- Create: `backend/internal/adapter/postgres/postgres.go`
- Create: `backend/internal/adapter/postgres/sqlcgen/*` (generated by `sqlc generate`; not hand-written)
- Create: `backend/internal/transport/http/ready.go`
- Modify: `backend/internal/transport/http/router.go` (mount `GET /readyz` via `NewReadyHandler`)
- Modify: `backend/cmd/api/main.go` (run `Migrate`, build `postgres.DB`, add the `postgres` `ReadyCheck`, pass `Ready` into `Deps`)
- Test: `backend/internal/transport/http/ready_test.go`
- Test: `backend/internal/adapter/postgres/postgres_integration_test.go` (build tag `integration`)

**Interfaces:**
- Consumes: `config.Config` (Task 2); `type Deps`, `func NewRouter(d Deps) http.Handler`, `type ReadyCheck{ Name string; Check func(context.Context) error }`, `func writeJSON(...)` (all declared in Task 2 — reused, never redeclared).
- Produces: `internal/transport/http` — `func NewReadyHandler(checks []ReadyCheck) http.HandlerFunc` ONLY (in `ready.go`; MUST NOT redeclare `ReadyCheck`; reuses the Task 2 `ReadyCheck` type and `writeJSON`). Router gains `GET /readyz`.
- Produces: `type DB struct{ Pool *pgxpool.Pool; Queries *sqlcgen.Queries }`; `func New(ctx, cfg) (*DB, error)`; `func (db *DB) Ping(ctx) error`; `func (db *DB) Close()`; `func Migrate(ctx, cfg) error` (embedded goose Up).
- Produces (sqlc-generated, package `sqlcgen`): `type ServerInfoRow struct{ Now time.Time; PostgisVersion string }`; `func (q *Queries) ServerInfo(ctx context.Context) (ServerInfoRow, error)`.
- Endpoint: `GET /readyz` -> `200 {"status":"ok","checks":{"<name>":"ok"}}` or `503 {"status":"degraded","checks":{"<name>":"<err>"}}`.
- Note: `*DB` is made to satisfy `serverinfo.Repository` in Task 4 (the `ServerInfo(ctx) (serverinfo.Info, error)` method lives in Task 4's `postgres/serverinfo.go`). Task 3's `main.go` does NOT yet wire `Deps.ServerInfo`.

- [ ] **Step 1: Write the readiness handler unit test (failing).**
  Create `backend/internal/transport/http/ready_test.go`:
  ```go
  package http_test

  import (
  	"context"
  	"encoding/json"
  	"errors"
  	"net/http"
  	"net/http/httptest"
  	"testing"

  	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
  )

  func doReadyz(t *testing.T, checks []transporthttp.ReadyCheck) (int, map[string]any) {
  	t.Helper()
  	h := transporthttp.NewReadyHandler(checks)
  	req := httptest.NewRequest(http.MethodGet, "/readyz", nil)
  	rec := httptest.NewRecorder()
  	h.ServeHTTP(rec, req)

  	var body map[string]any
  	if err := json.Unmarshal(rec.Body.Bytes(), &body); err != nil {
  		t.Fatalf("decode body: %v (raw=%q)", err, rec.Body.String())
  	}
  	return rec.Code, body
  }

  func TestReadyHandler_AllOK(t *testing.T) {
  	checks := []transporthttp.ReadyCheck{
  		{Name: "postgres", Check: func(context.Context) error { return nil }},
  		{Name: "redis", Check: func(context.Context) error { return nil }},
  	}
  	code, body := doReadyz(t, checks)
  	if code != http.StatusOK {
  		t.Fatalf("status = %d, want %d", code, http.StatusOK)
  	}
  	if body["status"] != "ok" {
  		t.Fatalf("status field = %v, want ok", body["status"])
  	}
  	got, _ := json.Marshal(body["checks"])
  	if string(got) != `{"postgres":"ok","redis":"ok"}` {
  		t.Fatalf("checks = %s, want both ok", got)
  	}
  }

  func TestReadyHandler_OneFailing(t *testing.T) {
  	checks := []transporthttp.ReadyCheck{
  		{Name: "postgres", Check: func(context.Context) error { return errors.New("connection refused") }},
  		{Name: "redis", Check: func(context.Context) error { return nil }},
  	}
  	code, body := doReadyz(t, checks)
  	if code != http.StatusServiceUnavailable {
  		t.Fatalf("status = %d, want %d", code, http.StatusServiceUnavailable)
  	}
  	if body["status"] != "degraded" {
  		t.Fatalf("status field = %v, want degraded", body["status"])
  	}
  	checksMap, _ := body["checks"].(map[string]any)
  	if checksMap["postgres"] != "connection refused" {
  		t.Fatalf("postgres check = %v, want error string", checksMap["postgres"])
  	}
  	if checksMap["redis"] != "ok" {
  		t.Fatalf("redis check = %v, want ok", checksMap["redis"])
  	}
  }
  ```

- [ ] **Step 2: Run the test and confirm it FAILS (no handler yet).**
  Run:
  ```
  cd backend && go test ./internal/transport/http/
  ```
  Expected FAIL output (undefined symbol):
  ```
  # github.com/Steamvis/gps-tracker/backend/internal/transport/http_test
  internal/transport/http/ready_test.go:14:46: undefined: transporthttp.NewReadyHandler
  FAIL	github.com/Steamvis/gps-tracker/backend/internal/transport/http [build failed]
  ```

- [ ] **Step 3: Write the readiness handler (reuses the Task 2 ReadyCheck and writeJSON).**
  Create `backend/internal/transport/http/ready.go`. It MUST NOT redeclare `ReadyCheck` (declared in Task 2's `router.go`) and reuses `writeJSON` from Task 2's `health.go`:
  ```go
  package http

  import (
  	"net/http"
  )

  // NewReadyHandler builds the GET /readyz handler. It runs every ReadyCheck and
  // reports 200 {"status":"ok","checks":{...}} when all pass, or
  // 503 {"status":"degraded","checks":{...}} when any fails, placing the error
  // string in place of "ok" for each failing check.
  func NewReadyHandler(checks []ReadyCheck) http.HandlerFunc {
  	return func(w http.ResponseWriter, r *http.Request) {
  		results := make(map[string]string, len(checks))
  		ok := true
  		for _, c := range checks {
  			if err := c.Check(r.Context()); err != nil {
  				results[c.Name] = err.Error()
  				ok = false
  				continue
  			}
  			results[c.Name] = "ok"
  		}

  		status := http.StatusOK
  		overall := "ok"
  		if !ok {
  			status = http.StatusServiceUnavailable
  			overall = "degraded"
  		}

  		writeJSON(w, status, map[string]any{"status": overall, "checks": results})
  	}
  }
  ```

- [ ] **Step 4: Run the readiness handler test and confirm it PASSES.**
  Run:
  ```
  cd backend && go test ./internal/transport/http/
  ```
  Expected PASS output:
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.0Xs
  ```

- [ ] **Step 5: Mount /readyz in the router.**
  Edit `backend/internal/transport/http/router.go`: add the `/readyz` route immediately after the existing `/healthz` registration inside `NewRouter`. The route block becomes exactly:
  ```go
  	r.Get("/healthz", healthz)
  	r.Get("/readyz", NewReadyHandler(d.Ready))
  ```
  Leave the imports, `ReadyCheck`, `Deps`, the middleware lines, and the `return r` unchanged.

- [ ] **Step 6: Verify the router still builds and http tests pass.**
  Run:
  ```
  cd backend && go test ./internal/transport/http/
  ```
  Expected PASS output:
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.0Xs
  ```

- [ ] **Step 7: Commit the readiness handler and wiring.**
  Run:
  ```
  cd backend && git add internal/transport/http/ready.go internal/transport/http/ready_test.go internal/transport/http/router.go && git commit -m "feat(transport): add /readyz handler iterating Deps.Ready checks" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(transport): add /readyz handler iterating Deps.Ready checks
  ```

- [ ] **Step 8: Write the initial PostGIS migration at its canonical path.**
  Create `backend/internal/adapter/postgres/db/migrations/0001_init.sql`:
  ```sql
  -- +goose Up
  -- +goose StatementBegin
  CREATE EXTENSION IF NOT EXISTS postgis;
  -- +goose StatementEnd

  -- +goose Down
  -- +goose StatementBegin
  DROP EXTENSION IF EXISTS postgis;
  -- +goose StatementEnd
  ```

- [ ] **Step 9: Write the ServerInfo query at its canonical path.**
  Create `backend/internal/adapter/postgres/db/queries/server_info.sql`:
  ```sql
  -- name: ServerInfo :one
  SELECT now()::timestamptz AS now, postgis_version() AS postgis_version;
  ```

- [ ] **Step 10: Write the sqlc v2 config.**
  Create `backend/sqlc.yaml`. The `engine` MUST be `"postgresql"`; paths point at the package-local migration/query dirs; the generated package is `sqlcgen` at `internal/adapter/postgres/sqlcgen`:
  ```yaml
  version: "2"
  sql:
    - engine: "postgresql"
      queries: "internal/adapter/postgres/db/queries"
      schema: "internal/adapter/postgres/db/migrations"
      gen:
        go:
          package: "sqlcgen"
          out: "internal/adapter/postgres/sqlcgen"
          sql_package: "pgx/v5"
          emit_interface: false
          emit_json_tags: false
          emit_empty_slices: true
  ```

- [ ] **Step 11: Generate the sqlc code.**
  sqlc parses the goose-annotated migration as schema; it runs the statements after `-- +goose Up` and ignores the `-- +goose Down` section, so only `CREATE EXTENSION ... postgis` is applied. Run:
  ```
  cd backend && sqlc generate
  ```
  Expected: no output, exit code 0. This creates `backend/internal/adapter/postgres/sqlcgen/db.go`, `models.go` and `server_info.sql.go`. `server_info.sql.go` contains exactly:
  ```go
  type ServerInfoRow struct {
  	Now            time.Time
  	PostgisVersion string
  }

  func (q *Queries) ServerInfo(ctx context.Context) (ServerInfoRow, error) {
  	row := q.db.QueryRow(ctx, serverInfo)
  	var i ServerInfoRow
  	err := row.Scan(&i.Now, &i.PostgisVersion)
  	return i, err
  }
  ```
  The `now()::timestamptz` cast is what forces `Now` to `time.Time`; the cast is present in `server_info.sql`, so this holds.

- [ ] **Step 12: Add the adapter module dependencies.**
  Run:
  ```
  cd backend && go get github.com/jackc/pgx/v5@latest && go get github.com/pressly/goose/v3@latest
  ```
  Expected: `go.mod`/`go.sum` updated with `github.com/jackc/pgx/v5` and `github.com/pressly/goose/v3`, exit code 0. (`pgxpool` and `stdlib` are subpackages of `pgx/v5`; goose pulls its own deps.)

- [ ] **Step 13: Write the postgres adapter (final, compilable file).**
  Create `backend/internal/adapter/postgres/postgres.go`. Runtime queries use a pgx pool; migrations open a short-lived `database/sql` connection via the pgx stdlib driver (goose's runner operates on `*sql.DB`). The `//go:embed db/migrations/*.sql` directive is package-relative and resolves to `internal/adapter/postgres/db/migrations`. Note `goose.SetDialect` returns an error and is checked:
  ```go
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
  	defer db.Close()

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
  ```

- [ ] **Step 14: Tidy modules and confirm the whole module builds.**
  Run:
  ```
  cd backend && go mod tidy && go build ./...
  ```
  Expected: no output, exit code 0. (`stdlib.OpenDB` takes `pgx.ConnConfig` by value; `connCfg.ConnConfig` is `*pgx.ConnConfig`, so `*connCfg.ConnConfig` dereferences it correctly.)

- [ ] **Step 15: Commit the adapter, migration, queries, sqlc config and generated code.**
  Run:
  ```
  cd backend && git add sqlc.yaml internal/adapter/postgres/ && git commit -m "feat(postgres): PostGIS adapter with pgxpool, embedded goose migrations, sqlc ServerInfo" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(postgres): PostGIS adapter with pgxpool, embedded goose migrations, sqlc ServerInfo
  ```

- [ ] **Step 16: Add the testcontainers integration test (Docker-gated).**
  Create `backend/internal/adapter/postgres/postgres_integration_test.go`. The `//go:build integration` tag keeps it out of the default run. The wait strategy uses the GENERIC `testcontainers.WithWaitStrategy(...)` from the core package (the postgres module has no `WithWaitStrategy`):
  ```go
  //go:build integration

  package postgres_test

  import (
  	"context"
  	"testing"
  	"time"

  	"github.com/testcontainers/testcontainers-go"
  	tcpostgres "github.com/testcontainers/testcontainers-go/modules/postgres"
  	"github.com/testcontainers/testcontainers-go/wait"

  	"github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres"
  	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
  )

  func TestPostgres_MigrateAndServerInfo(t *testing.T) {
  	ctx := context.Background()

  	container, err := tcpostgres.Run(ctx, "postgis/postgis:16-3.4",
  		tcpostgres.WithDatabase("gps"),
  		tcpostgres.WithUsername("gps"),
  		tcpostgres.WithPassword("gps"),
  		testcontainers.WithWaitStrategy(
  			wait.ForLog("database system is ready to accept connections").
  				WithOccurrence(2).
  				WithStartupTimeout(60*time.Second),
  		),
  	)
  	if err != nil {
  		t.Fatalf("start postgis container: %v", err)
  	}
  	t.Cleanup(func() {
  		if err := testcontainers.TerminateContainer(container); err != nil {
  			t.Logf("terminate container: %v", err)
  		}
  	})

  	dsn, err := container.ConnectionString(ctx, "sslmode=disable")
  	if err != nil {
  		t.Fatalf("connection string: %v", err)
  	}

  	cfg := config.Config{DatabaseURL: dsn}

  	if err := postgres.Migrate(ctx, cfg); err != nil {
  		t.Fatalf("migrate: %v", err)
  	}

  	db, err := postgres.New(ctx, cfg)
  	if err != nil {
  		t.Fatalf("new db: %v", err)
  	}
  	t.Cleanup(db.Close)

  	if err := db.Ping(ctx); err != nil {
  		t.Fatalf("ping: %v", err)
  	}

  	info, err := db.Queries.ServerInfo(ctx)
  	if err != nil {
  		t.Fatalf("server info: %v", err)
  	}
  	if info.PostgisVersion == "" {
  		t.Fatalf("PostgisVersion is empty, want a version string")
  	}
  	if info.Now.IsZero() {
  		t.Fatalf("Now is zero, want a non-zero timestamp")
  	}
  }
  ```

- [ ] **Step 17: Add the testcontainers dependencies.**
  Run:
  ```
  cd backend && go get github.com/testcontainers/testcontainers-go@latest && go get github.com/testcontainers/testcontainers-go/modules/postgres@latest && go mod tidy
  ```
  Expected: `go.mod`/`go.sum` updated with `github.com/testcontainers/testcontainers-go` and its `modules/postgres`, exit code 0.

- [ ] **Step 18: Confirm the integration test type-checks under the build tag.**
  Run:
  ```
  cd backend && go vet -tags=integration ./internal/adapter/postgres/
  ```
  Expected: no output, exit code 0 (vet compiles the tagged file but does not start a container).

- [ ] **Step 19: Run the unit suite (no integration tag) and confirm it passes without Docker.**
  Run:
  ```
  cd backend && go test ./...
  ```
  Expected PASS output (integration file excluded by the build tag):
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/platform/config	0.00Xs
  ?   	github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres	[no test files]
  ?   	github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres/sqlcgen	[no test files]
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.0Xs
  ```

- [ ] **Step 20: Run the integration test with Docker.**
  Run:
  ```
  cd backend && go test -tags=integration ./internal/adapter/postgres/...
  ```
  Expected PASS output (testcontainers pulls `postgis/postgis:16-3.4`, runs `Migrate`, queries `ServerInfo`):
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres	1X.XXXs
  ```

- [ ] **Step 21: Commit the integration test and test dependencies.**
  Run:
  ```
  cd backend && git add internal/adapter/postgres/postgres_integration_test.go go.mod go.sum && git commit -m "test(postgres): testcontainers integration for Migrate + ServerInfo (integration tag)" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] test(postgres): testcontainers integration for Migrate + ServerInfo (integration tag)
  ```

- [ ] **Step 22: Wire postgres into main.go (full current main.go after Task 3).**
  Replace `backend/cmd/api/main.go` with the full file below. It runs `Migrate` on boot, builds the `DB`, registers the `postgres` `ReadyCheck`, and passes `Ready` into `Deps`. `Deps.ServerInfo` is still nil (wired in Task 4). NO `-health` flag, NO OTel:
  ```go
  // Command api serves the gps-api HTTP transport. In milestone M0 Task 3 it
  // exposes GET /healthz and GET /readyz, runs migrations on boot and connects to
  // Postgres; server-info wiring follows in Task 4 and OTel in Task 5.
  package main

  import (
  	"context"
  	"os"
  	"os/signal"
  	"syscall"

  	"github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres"
  	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
  	platformlog "github.com/Steamvis/gps-tracker/backend/internal/platform/log"
  	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
  )

  func main() {
  	cfg, err := config.Load()
  	if err != nil {
  		println("config load failed:", err.Error())
  		os.Exit(1)
  	}

  	logger := platformlog.New(cfg)

  	ctx, stop := signal.NotifyContext(context.Background(), syscall.SIGINT, syscall.SIGTERM)
  	defer stop()

  	// Migrations run on boot; the api container does this per the compose contract.
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

  	router := transporthttp.NewRouter(transporthttp.Deps{
  		Log:     logger,
  		Version: cfg.Version,
  		Ready: []transporthttp.ReadyCheck{
  			{Name: "postgres", Check: db.Ping},
  		},
  		// ServerInfo is wired in Task 4.
  	})

  	server := transporthttp.NewServer(cfg.HTTPAddr, router, logger)

  	if err := server.Run(ctx); err != nil {
  		logger.Error("server exited with error", "error", err)
  		os.Exit(1)
  	}
  }
  ```

- [ ] **Step 23: Build the api binary and run the full unit suite.**
  Run:
  ```
  cd backend && go build ./cmd/api/ && go test ./...
  ```
  Expected: build produces no output (exit 0); tests PASS:
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/platform/config	0.00Xs
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.0Xs
  ?   	github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres	[no test files]
  ```

- [ ] **Step 24: Commit the main.go wiring.**
  Run:
  ```
  cd backend && git add cmd/api/main.go && git commit -m "feat(api): run migrations on boot and add postgres readiness check" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(api): run migrations on boot and add postgres readiness check
  ```

---

### Task 4: server-info usecase + end-to-end /api/v1/server-info endpoint

**Files:**
- Replace: `backend/internal/usecase/serverinfo/serverinfo.go` (replaces the Task 2 stub with the full implementation)
- Create: `backend/internal/usecase/serverinfo/serverinfo_test.go`
- Create: `backend/internal/adapter/postgres/serverinfo.go`
- Create: `backend/internal/transport/http/serverinfo.go`
- Create: `backend/internal/transport/http/serverinfo_test.go`
- Modify: `backend/internal/transport/http/router.go` (mount `GET /api/v1/server-info`)
- Modify: `backend/cmd/api/main.go` (construct `serverinfo.New(db)`, pass as `Deps.ServerInfo`)
- Test: `backend/internal/adapter/postgres/serverinfo_integration_test.go` (build tag `integration`)

**Interfaces:**
- Consumes: `config.Config`; `postgres.DB{ Pool, Queries }`, `postgres.New`, `postgres.Migrate` (Task 3); `sqlcgen.ServerInfoRow{ Now time.Time; PostgisVersion string }` and `(*sqlcgen.Queries).ServerInfo(ctx) (ServerInfoRow, error)` (Task 3); `transport/http.Deps`, `NewRouter`, `writeJSON` (Task 2 — `writeJSON` is reused, never redefined).
- Produces: `serverinfo.Info{ Time time.Time; PostGIS string }`; `serverinfo.Repository interface{ ServerInfo(ctx context.Context) (Info, error) }`; `serverinfo.Service{ repo Repository }`; `func serverinfo.New(repo Repository) *Service`; `func (s *Service) Get(ctx context.Context) (Info, error)` (this file REPLACES the Task 2 stub).
- Produces: `func (db *postgres.DB) ServerInfo(ctx context.Context) (serverinfo.Info, error)` (makes `*DB` satisfy `serverinfo.Repository`).
- Produces: `internal/transport/http` — `func serverInfoHandler(svc *serverinfo.Service, version string) http.HandlerFunc` (in `serverinfo.go`; uses plain `context.Context`, no `loggerLike`, no `contextContext` alias; reuses `writeJSON`). Router gains `GET /api/v1/server-info` returning `{"app":"gps-tracker","version":"<version>","time":"<RFC3339>","postgis":"<v>"}`.

- [ ] **Step 1: Write the failing usecase test.**
  Create `backend/internal/usecase/serverinfo/serverinfo_test.go`:
  ```go
  package serverinfo

  import (
  	"context"
  	"errors"
  	"testing"
  	"time"
  )

  type fakeRepo struct {
  	info Info
  	err  error
  }

  func (f fakeRepo) ServerInfo(ctx context.Context) (Info, error) {
  	return f.info, f.err
  }

  func TestServiceGetReturnsRepoInfo(t *testing.T) {
  	want := Info{Time: time.Date(2026, 6, 20, 12, 0, 0, 0, time.UTC), PostGIS: "3.4 USE_GEOS=1"}
  	svc := New(fakeRepo{info: want})

  	got, err := svc.Get(context.Background())
  	if err != nil {
  		t.Fatalf("Get returned error: %v", err)
  	}
  	if !got.Time.Equal(want.Time) {
  		t.Errorf("Time = %v, want %v", got.Time, want.Time)
  	}
  	if got.PostGIS != want.PostGIS {
  		t.Errorf("PostGIS = %q, want %q", got.PostGIS, want.PostGIS)
  	}
  }

  func TestServiceGetPropagatesError(t *testing.T) {
  	wantErr := errors.New("db down")
  	svc := New(fakeRepo{err: wantErr})

  	_, err := svc.Get(context.Background())
  	if !errors.Is(err, wantErr) {
  		t.Fatalf("Get error = %v, want %v", err, wantErr)
  	}
  }
  ```

- [ ] **Step 2: Run the test and confirm it FAILS (compile error against the stub).**
  Run:
  ```
  cd backend && go test ./internal/usecase/serverinfo/
  ```
  Expected FAIL output (the Task 2 stub declares only `type Service struct{}`):
  ```
  # github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo [github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo.test]
  ./serverinfo_test.go:11:7: undefined: Info
  ./serverinfo_test.go:23:9: undefined: New
  FAIL	github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo [build failed]
  ```

- [ ] **Step 3: Replace the stub with the full usecase implementation.**
  Overwrite `backend/internal/usecase/serverinfo/serverinfo.go` (replacing the Task 2 `type Service struct{}` stub) with the complete file:
  ```go
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
  ```

- [ ] **Step 4: Run the test and confirm it PASSES.**
  Run:
  ```
  cd backend && go test ./internal/usecase/serverinfo/
  ```
  Expected PASS output:
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo	0.00Xs
  ```

- [ ] **Step 5: Commit the usecase.**
  Run:
  ```
  cd backend && git add internal/usecase/serverinfo/serverinfo.go internal/usecase/serverinfo/serverinfo_test.go && git commit -m "feat(usecase): replace serverinfo stub with Service and Repository port" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(usecase): replace serverinfo stub with Service and Repository port
  ```

- [ ] **Step 6: Write the postgres adapter method (makes *DB satisfy serverinfo.Repository).**
  Create `backend/internal/adapter/postgres/serverinfo.go`:
  ```go
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
  ```

- [ ] **Step 7: Verify the adapter compiles and the interface assertion holds.**
  Run:
  ```
  cd backend && go build ./internal/adapter/postgres/
  ```
  Expected: no output, exit 0. If `*DB` did not satisfy `serverinfo.Repository` the build would fail at `var _ serverinfo.Repository = (*DB)(nil)`.

- [ ] **Step 8: Commit the adapter method.**
  Run:
  ```
  cd backend && git add internal/adapter/postgres/serverinfo.go && git commit -m "feat(postgres): implement serverinfo.Repository via ServerInfo query" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(postgres): implement serverinfo.Repository via ServerInfo query
  ```

- [ ] **Step 9: Write the failing HTTP handler test.**
  Create `backend/internal/transport/http/serverinfo_test.go`. It is in the external `http_test` package and drives the real router via `NewRouter`:
  ```go
  package http_test

  import (
  	"context"
  	"encoding/json"
  	"io"
  	"log/slog"
  	"net/http"
  	"net/http/httptest"
  	"testing"
  	"time"

  	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
  	"github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo"
  )

  type fakeServerInfoRepo struct {
  	info serverinfo.Info
  }

  func (f fakeServerInfoRepo) ServerInfo(ctx context.Context) (serverinfo.Info, error) {
  	return f.info, nil
  }

  func TestServerInfoEndpointJSON(t *testing.T) {
  	ts := time.Date(2026, 6, 20, 12, 30, 0, 0, time.UTC)
  	svc := serverinfo.New(fakeServerInfoRepo{info: serverinfo.Info{Time: ts, PostGIS: "3.4 USE_GEOS=1"}})

  	router := transporthttp.NewRouter(transporthttp.Deps{
  		Log:        slog.New(slog.NewJSONHandler(io.Discard, nil)),
  		ServerInfo: svc,
  		Version:    "1.2.3",
  	})

  	rr := httptest.NewRecorder()
  	req := httptest.NewRequest(http.MethodGet, "/api/v1/server-info", nil)
  	router.ServeHTTP(rr, req)

  	if rr.Code != http.StatusOK {
  		t.Fatalf("status = %d, want %d (body %s)", rr.Code, http.StatusOK, rr.Body.String())
  	}
  	if ct := rr.Header().Get("Content-Type"); ct != "application/json" {
  		t.Errorf("Content-Type = %q, want application/json", ct)
  	}

  	var got map[string]string
  	if err := json.Unmarshal(rr.Body.Bytes(), &got); err != nil {
  		t.Fatalf("invalid JSON: %v (body %s)", err, rr.Body.String())
  	}
  	want := map[string]string{
  		"app":     "gps-tracker",
  		"version": "1.2.3",
  		"time":    ts.Format(time.RFC3339),
  		"postgis": "3.4 USE_GEOS=1",
  	}
  	for k, v := range want {
  		if got[k] != v {
  			t.Errorf("field %q = %q, want %q", k, got[k], v)
  		}
  	}
  	if len(got) != len(want) {
  		t.Errorf("response has %d fields, want %d: %v", len(got), len(want), got)
  	}
  }
  ```

- [ ] **Step 10: Run the test and confirm it FAILS.**
  Run:
  ```
  cd backend && go test ./internal/transport/http/ -run TestServerInfoEndpointJSON
  ```
  Expected FAIL output (route not mounted yet — chi returns 404):
  ```
  --- FAIL: TestServerInfoEndpointJSON (0.00s)
      serverinfo_test.go:40: status = 404, want 200 (body 404 page not found
          )
  FAIL
  FAIL	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.00Xs
  ```

- [ ] **Step 11: Write the HTTP handler (plain context.Context, reuses writeJSON).**
  Create `backend/internal/transport/http/serverinfo.go`. It uses `*slog.Logger` directly and plain `context.Context`; it does NOT define `writeJSON` (reused from Task 2's `health.go`):
  ```go
  package http

  import (
  	"log/slog"
  	"net/http"
  	"time"

  	"github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo"
  )

  // serverInfoResponse is the exact wire shape of GET /api/v1/server-info.
  type serverInfoResponse struct {
  	App     string `json:"app"`
  	Version string `json:"version"`
  	Time    string `json:"time"`
  	PostGIS string `json:"postgis"`
  }

  // serverInfoHandler returns the GET /api/v1/server-info handler bound to svc,
  // version and log. On a repository error it logs and responds 500.
  func serverInfoHandler(svc *serverinfo.Service, version string, log *slog.Logger) http.HandlerFunc {
  	return func(w http.ResponseWriter, r *http.Request) {
  		info, err := svc.Get(r.Context())
  		if err != nil {
  			log.ErrorContext(r.Context(), "server-info failed", slog.Any("error", err))
  			writeJSON(w, http.StatusInternalServerError, map[string]string{"status": "error"})
  			return
  		}
  		writeJSON(w, http.StatusOK, serverInfoResponse{
  			App:     "gps-tracker",
  			Version: version,
  			Time:    info.Time.Format(time.RFC3339),
  			PostGIS: info.PostGIS,
  		})
  	}
  }
  ```

- [ ] **Step 12: Mount the route in router.go.**
  Edit `backend/internal/transport/http/router.go`: add the `/api/v1/server-info` route immediately after the existing `/readyz` registration inside `NewRouter`. The route block becomes exactly:
  ```go
  	r.Get("/healthz", healthz)
  	r.Get("/readyz", NewReadyHandler(d.Ready))
  	r.Get("/api/v1/server-info", serverInfoHandler(d.ServerInfo, d.Version, d.Log))
  ```
  Leave the imports, `ReadyCheck`, `Deps`, the middleware lines and `return r` unchanged. (No new imports are needed — `serverinfo` is already imported by `Deps`.)

- [ ] **Step 13: Run the handler test and confirm it PASSES.**
  Run:
  ```
  cd backend && go test ./internal/transport/http/ -run TestServerInfoEndpointJSON
  ```
  Expected PASS output:
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.00Xs
  ```

- [ ] **Step 14: Wire the service in main.go (full current main.go after Task 4).**
  Replace `backend/cmd/api/main.go` with the full file below. It constructs `serverinfo.New(db)` and passes it as `Deps.ServerInfo`. NO `-health` flag, NO OTel yet:
  ```go
  // Command api serves the gps-api HTTP transport. In milestone M0 Task 4 it
  // serves the full end-to-end /api/v1/server-info slice; OTel wiring follows in
  // Task 5 and the -health subcommand in Task 6.
  package main

  import (
  	"context"
  	"os"
  	"os/signal"
  	"syscall"

  	"github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres"
  	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
  	platformlog "github.com/Steamvis/gps-tracker/backend/internal/platform/log"
  	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
  	"github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo"
  )

  func main() {
  	cfg, err := config.Load()
  	if err != nil {
  		println("config load failed:", err.Error())
  		os.Exit(1)
  	}

  	logger := platformlog.New(cfg)

  	ctx, stop := signal.NotifyContext(context.Background(), syscall.SIGINT, syscall.SIGTERM)
  	defer stop()

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
  ```

- [ ] **Step 15: Build the whole module and run all unit tests.**
  Run:
  ```
  cd backend && go build ./... && go test ./...
  ```
  Expected PASS output (the `integration`-tagged file in Step 16 is excluded):
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo	0.00Xs
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.00Xs
  ok  	github.com/Steamvis/gps-tracker/backend/internal/platform/config	0.00Xs
  ?   	github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres	[no test files]
  ```

- [ ] **Step 16: Write the end-to-end integration test (real DB via testcontainers).**
  Create `backend/internal/adapter/postgres/serverinfo_integration_test.go`. It boots PostGIS, runs `Migrate`, builds the real `*DB`, drives the production handler path through `NewRouter`, and asserts a non-empty `postgis`. The wait strategy uses the GENERIC `testcontainers.WithWaitStrategy(...)`:
  ```go
  //go:build integration

  package postgres_test

  import (
  	"context"
  	"encoding/json"
  	"io"
  	"log/slog"
  	"net/http"
  	"net/http/httptest"
  	"testing"
  	"time"

  	"github.com/testcontainers/testcontainers-go"
  	tcpostgres "github.com/testcontainers/testcontainers-go/modules/postgres"
  	"github.com/testcontainers/testcontainers-go/wait"

  	"github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres"
  	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
  	transporthttp "github.com/Steamvis/gps-tracker/backend/internal/transport/http"
  	"github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo"
  )

  func TestServerInfoEndToEnd(t *testing.T) {
  	ctx := context.Background()

  	container, err := tcpostgres.Run(ctx, "postgis/postgis:16-3.4",
  		tcpostgres.WithDatabase("gps"),
  		tcpostgres.WithUsername("gps"),
  		tcpostgres.WithPassword("gps"),
  		testcontainers.WithWaitStrategy(
  			wait.ForLog("database system is ready to accept connections").
  				WithOccurrence(2).
  				WithStartupTimeout(60*time.Second),
  		),
  	)
  	if err != nil {
  		t.Fatalf("start postgis container: %v", err)
  	}
  	t.Cleanup(func() {
  		if err := testcontainers.TerminateContainer(container); err != nil {
  			t.Logf("terminate container: %v", err)
  		}
  	})

  	dsn, err := container.ConnectionString(ctx, "sslmode=disable")
  	if err != nil {
  		t.Fatalf("connection string: %v", err)
  	}

  	cfg := config.Config{
  		Env:         "test",
  		ServiceName: "gps-api",
  		Version:     "test-1.0.0",
  		DatabaseURL: dsn,
  		LogLevel:    "info",
  	}

  	if err := postgres.Migrate(ctx, cfg); err != nil {
  		t.Fatalf("migrate: %v", err)
  	}

  	db, err := postgres.New(ctx, cfg)
  	if err != nil {
  		t.Fatalf("postgres.New: %v", err)
  	}
  	t.Cleanup(db.Close)

  	router := transporthttp.NewRouter(transporthttp.Deps{
  		Log:        slog.New(slog.NewJSONHandler(io.Discard, nil)),
  		ServerInfo: serverinfo.New(db),
  		Version:    cfg.Version,
  	})

  	rr := httptest.NewRecorder()
  	req := httptest.NewRequest(http.MethodGet, "/api/v1/server-info", nil)
  	router.ServeHTTP(rr, req)

  	if rr.Code != http.StatusOK {
  		t.Fatalf("status = %d, want 200 (body %s)", rr.Code, rr.Body.String())
  	}

  	var got struct {
  		App     string `json:"app"`
  		Version string `json:"version"`
  		Time    string `json:"time"`
  		PostGIS string `json:"postgis"`
  	}
  	if err := json.Unmarshal(rr.Body.Bytes(), &got); err != nil {
  		t.Fatalf("decode body: %v (%s)", err, rr.Body.String())
  	}
  	if got.App != "gps-tracker" {
  		t.Errorf("app = %q, want gps-tracker", got.App)
  	}
  	if got.Version != cfg.Version {
  		t.Errorf("version = %q, want %q", got.Version, cfg.Version)
  	}
  	if _, err := time.Parse(time.RFC3339, got.Time); err != nil {
  		t.Errorf("time %q is not RFC3339: %v", got.Time, err)
  	}
  	if got.PostGIS == "" {
  		t.Error("postgis field is empty, want a non-empty PostGIS version")
  	}
  }
  ```

- [ ] **Step 17: Run the integration test and confirm it PASSES.**
  Run (Docker daemon must be running):
  ```
  cd backend && go test -tags integration -run TestServerInfoEndToEnd ./internal/adapter/postgres/
  ```
  Expected PASS output:
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres	1X.XXXs
  ```

- [ ] **Step 18: Commit the handler, wiring and tests.**
  Run:
  ```
  cd backend && git add internal/transport/http/serverinfo.go internal/transport/http/serverinfo_test.go internal/transport/http/router.go cmd/api/main.go internal/adapter/postgres/serverinfo_integration_test.go && git commit -m "feat(api): end-to-end GET /api/v1/server-info slice" -m "Wires handler -> serverinfo.Service -> Repository port -> postgres adapter -> DB, with unit and testcontainers integration tests." -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(api): end-to-end GET /api/v1/server-info slice
  ```

---

### Task 5: OpenTelemetry (traces+metrics+logs) + HTTP middleware chain

**Files:**
- Create: `backend/internal/platform/otel/otel.go`
- Create: `backend/internal/platform/otel/otel_test.go`
- Create: `backend/internal/transport/http/middleware.go`
- Create: `backend/internal/transport/http/middleware_test.go`
- Modify: `backend/internal/transport/http/router.go` (wrap the chain in contract order)
- Modify: `backend/internal/platform/log/log.go` (fan slog out to the otelslog bridge)
- Modify: `backend/cmd/api/main.go` (call `otel.Setup` early, defer shutdown)

**Interfaces:**
- Consumes: `config.Config`; `serverinfo.Service`; `Deps`, `NewRouter`, `ReadyCheck`, `writeJSON`, `healthz`, `NewReadyHandler`, `serverInfoHandler` (all from Tasks 2/3/4 — reused, never redeclared). MUST NOT rewrite the handlers, redefine `writeJSON`, or redeclare `ReadyCheck`/`Deps`.
- Produces: `func otel.Setup(ctx context.Context, cfg config.Config) (shutdown func(context.Context) error, err error)` installing global tracer/meter/logger providers over OTLP gRPC.
- Produces: `internal/transport/http` — `func requestID(next http.Handler) http.Handler`, `func requestIDFromContext(ctx context.Context) string`, `func recoverer(log *slog.Logger) func(http.Handler) http.Handler`, `func accessLog(log *slog.Logger) func(http.Handler) http.Handler` (in `middleware.go`). `NewRouter` is EDITED so the returned handler is wrapped outer->inner: `requestID -> recoverer -> accessLog -> otelhttp("gps-api")`.
- Version set (coherent, kept consistent across go.mod): OTel core **v1.31.0** (`go.opentelemetry.io/otel`, `otel/sdk`, `otel/sdk/metric`, `otlptracegrpc`, `otlpmetricgrpc`); OTel log experimental **v0.7.0** (`otel/sdk/log`, `otlploggrpc`, `otel/log/global`); `otelhttp` **v0.56.0**; `otelslog` **v0.6.0**; `semconv` **v1.27.0** (exports the non-deprecated `DeploymentEnvironmentName`); `github.com/google/uuid` **v1.6.0**.

- [ ] **Step 1: Write the failing requestID middleware test.**
  Create `backend/internal/transport/http/middleware_test.go`. It is in the internal `http` package so it can call the unexported middleware:
  ```go
  package http

  import (
  	"context"
  	"encoding/json"
  	"io"
  	"log/slog"
  	"net/http"
  	"net/http/httptest"
  	"testing"

  	"go.opentelemetry.io/contrib/instrumentation/net/http/otelhttp"
  	sdktrace "go.opentelemetry.io/otel/sdk/trace"
  	"go.opentelemetry.io/otel/sdk/trace/tracetest"
  )

  func discardLogger() *slog.Logger {
  	return slog.New(slog.NewJSONHandler(io.Discard, nil))
  }

  func TestRequestIDMiddlewareSetsHeader(t *testing.T) {
  	var seen string
  	h := requestID(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
  		seen = requestIDFromContext(r.Context())
  		w.WriteHeader(http.StatusOK)
  	}))

  	rec := httptest.NewRecorder()
  	req := httptest.NewRequest(http.MethodGet, "/healthz", nil)
  	h.ServeHTTP(rec, req)

  	got := rec.Header().Get("X-Request-Id")
  	if got == "" {
  		t.Fatalf("expected X-Request-Id response header to be set, got empty")
  	}
  	if seen != got {
  		t.Fatalf("context request id %q does not match header %q", seen, got)
  	}
  }

  func TestRequestIDMiddlewareHonorsIncomingHeader(t *testing.T) {
  	h := requestID(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
  		w.WriteHeader(http.StatusOK)
  	}))
  	rec := httptest.NewRecorder()
  	req := httptest.NewRequest(http.MethodGet, "/healthz", nil)
  	req.Header.Set("X-Request-Id", "abc-123")
  	h.ServeHTTP(rec, req)
  	if got := rec.Header().Get("X-Request-Id"); got != "abc-123" {
  		t.Fatalf("expected incoming request id to be reused, got %q", got)
  	}
  }

  func TestRecovererReturns500AndDoesNotCrash(t *testing.T) {
  	h := recoverer(discardLogger())(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
  		panic("boom")
  	}))

  	rec := httptest.NewRecorder()
  	req := httptest.NewRequest(http.MethodGet, "/api/v1/server-info", nil)
  	h.ServeHTTP(rec, req) // must not panic

  	if rec.Code != http.StatusInternalServerError {
  		t.Fatalf("expected status 500, got %d", rec.Code)
  	}
  	var body map[string]string
  	if err := json.Unmarshal(rec.Body.Bytes(), &body); err != nil {
  		t.Fatalf("response body is not JSON: %v", err)
  	}
  	if body["status"] != "error" {
  		t.Fatalf(`expected {"status":"error"}, got %v`, body)
  	}
  }

  func TestAccessLogRunsAndPassesThrough(t *testing.T) {
  	called := false
  	h := requestID(accessLog(discardLogger())(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
  		called = true
  		w.WriteHeader(http.StatusTeapot)
  	})))

  	rec := httptest.NewRecorder()
  	req := httptest.NewRequest(http.MethodGet, "/healthz", nil)
  	h.ServeHTTP(rec, req)

  	if !called {
  		t.Fatalf("inner handler was not called through accessLog")
  	}
  	if rec.Code != http.StatusTeapot {
  		t.Fatalf("expected status 418 to pass through, got %d", rec.Code)
  	}
  }

  func TestOtelHTTPRecordsSpan(t *testing.T) {
  	sr := tracetest.NewSpanRecorder()
  	tp := sdktrace.NewTracerProvider(sdktrace.WithSpanProcessor(sr))
  	t.Cleanup(func() { _ = tp.Shutdown(context.Background()) })

  	inner := http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
  		w.WriteHeader(http.StatusOK)
  	})
  	h := otelhttp.NewHandler(inner, "gps-api", otelhttp.WithTracerProvider(tp))

  	rec := httptest.NewRecorder()
  	req := httptest.NewRequest(http.MethodGet, "/healthz", nil)
  	h.ServeHTTP(rec, req)

  	spans := sr.Ended()
  	if len(spans) != 1 {
  		t.Fatalf("expected exactly 1 recorded span, got %d", len(spans))
  	}
  	if spans[0].Name() != "gps-api" {
  		t.Fatalf("expected span name %q, got %q", "gps-api", spans[0].Name())
  	}
  }
  ```

- [ ] **Step 2: Add the otelhttp + uuid + sdk/trace deps and run the test (expect FAIL).**
  Run:
  ```
  cd backend && go get go.opentelemetry.io/contrib/instrumentation/net/http/otelhttp@v0.56.0 && go get go.opentelemetry.io/otel/sdk@v1.31.0 && go get github.com/google/uuid@v1.6.0
  ```
  Then run the test:
  ```
  cd backend && go test ./internal/transport/http/ -run 'TestRequestIDMiddleware|TestRecoverer|TestAccessLog'
  ```
  Expected FAIL output (`requestID`/`recoverer`/`accessLog` undefined):
  ```
  # github.com/Steamvis/gps-tracker/backend/internal/transport/http
  ./middleware_test.go:23:7: undefined: requestID
  ./middleware_test.go:60:7: undefined: recoverer
  ./middleware_test.go:75:18: undefined: accessLog
  FAIL	github.com/Steamvis/gps-tracker/backend/internal/transport/http [build failed]
  ```

- [ ] **Step 3: Commit the deps.**
  Run:
  ```
  cd backend && git add go.mod go.sum && git commit -m "chore(backend): add otelhttp, otel sdk and uuid deps" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] chore(backend): add otelhttp, otel sdk and uuid deps
  ```

- [ ] **Step 4: Implement the middleware.**
  Create `backend/internal/transport/http/middleware.go`:
  ```go
  package http

  import (
  	"context"
  	"log/slog"
  	"net/http"
  	"time"

  	"github.com/google/uuid"
  )

  type ctxKey int

  const requestIDKey ctxKey = iota

  // requestIDFromContext returns the request id stored by the requestID
  // middleware, or "" if none is present.
  func requestIDFromContext(ctx context.Context) string {
  	if v, ok := ctx.Value(requestIDKey).(string); ok {
  		return v
  	}
  	return ""
  }

  // requestID ensures every request carries an X-Request-Id. It reuses an
  // incoming header if present, otherwise generates a new UUID, stores it on the
  // context and echoes it on the response.
  func requestID(next http.Handler) http.Handler {
  	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
  		id := r.Header.Get("X-Request-Id")
  		if id == "" {
  			id = uuid.NewString()
  		}
  		w.Header().Set("X-Request-Id", id)
  		ctx := context.WithValue(r.Context(), requestIDKey, id)
  		next.ServeHTTP(w, r.WithContext(ctx))
  	})
  }

  // recoverer recovers from panics in downstream handlers, logs the panic via
  // slog and responds 500 {"status":"error"}.
  func recoverer(log *slog.Logger) func(http.Handler) http.Handler {
  	return func(next http.Handler) http.Handler {
  		return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
  			defer func() {
  				if rec := recover(); rec != nil {
  					log.ErrorContext(r.Context(), "panic recovered",
  						slog.Any("panic", rec),
  						slog.String("method", r.Method),
  						slog.String("path", r.URL.Path),
  						slog.String("request_id", requestIDFromContext(r.Context())),
  					)
  					w.Header().Set("Content-Type", "application/json")
  					w.WriteHeader(http.StatusInternalServerError)
  					_, _ = w.Write([]byte(`{"status":"error"}`))
  				}
  			}()
  			next.ServeHTTP(w, r)
  		})
  	}
  }

  // statusRecorder captures the response status code for access logging.
  type statusRecorder struct {
  	http.ResponseWriter
  	status int
  }

  func (s *statusRecorder) WriteHeader(code int) {
  	s.status = code
  	s.ResponseWriter.WriteHeader(code)
  }

  // accessLog logs method, path, status, duration and request_id for every
  // request via slog.
  func accessLog(log *slog.Logger) func(http.Handler) http.Handler {
  	return func(next http.Handler) http.Handler {
  		return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
  			start := time.Now()
  			rec := &statusRecorder{ResponseWriter: w, status: http.StatusOK}
  			next.ServeHTTP(rec, r)
  			log.InfoContext(r.Context(), "http request",
  				slog.String("method", r.Method),
  				slog.String("path", r.URL.Path),
  				slog.Int("status", rec.status),
  				slog.Duration("duration", time.Since(start)),
  				slog.String("request_id", requestIDFromContext(r.Context())),
  			)
  		})
  	}
  }
  ```

- [ ] **Step 5: Run the middleware tests (expect PASS).**
  Run:
  ```
  cd backend && go test ./internal/transport/http/ -run 'TestRequestIDMiddleware|TestRecoverer|TestAccessLog|TestOtelHTTP' -v
  ```
  Expected PASS output (5 tests):
  ```
  --- PASS: TestRequestIDMiddlewareSetsHeader (0.00s)
  --- PASS: TestRequestIDMiddlewareHonorsIncomingHeader (0.00s)
  --- PASS: TestRecovererReturns500AndDoesNotCrash (0.00s)
  --- PASS: TestAccessLogRunsAndPassesThrough (0.00s)
  --- PASS: TestOtelHTTPRecordsSpan (0.00s)
  PASS
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.0Xs
  ```

- [ ] **Step 6: Commit the middleware.**
  Run:
  ```
  cd backend && git add internal/transport/http/middleware.go internal/transport/http/middleware_test.go && git commit -m "feat(transport): add requestID, recoverer and accessLog middleware" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(transport): add requestID, recoverer and accessLog middleware
  ```

- [ ] **Step 7: Wrap the router in the contract middleware chain (scoped edit to router.go).**
  Edit `backend/internal/transport/http/router.go`. Do NOT touch `ReadyCheck`, `Deps`, the route handlers (`healthz`, `NewReadyHandler`, `serverInfoHandler`) or `writeJSON`. (1) Add the otelhttp import; (2) replace the chi `r.Use(...)` lines and the final `return r` with the wrapped chain. After the edit the file reads exactly:
  ```go
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
  	h = otelhttp.NewHandler(h, "gps-api")
  	h = accessLog(d.Log)(h)
  	h = recoverer(d.Log)(h)
  	h = requestID(h)
  	return h
  }
  ```
  Note: the chi `middleware` import is removed because chi's `RequestID`/`Recoverer` are replaced by the package's own contract middleware.

- [ ] **Step 8: Run the full transport package tests (expect PASS).**
  Run:
  ```
  cd backend && go test ./internal/transport/http/ -v
  ```
  Expected PASS output (the Task 2/3/4 endpoint tests plus the Task 5 middleware tests all pass):
  ```
  PASS
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.0Xs
  ```

- [ ] **Step 9: Commit the router chain.**
  Run:
  ```
  cd backend && git add internal/transport/http/router.go && git commit -m "feat(transport): wrap router in requestID->recoverer->accessLog->otelhttp chain" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(transport): wrap router in requestID->recoverer->accessLog->otelhttp chain
  ```

- [ ] **Step 10: Write the failing otel.Setup test.**
  Create `backend/internal/platform/otel/otel_test.go`:
  ```go
  package otel_test

  import (
  	"context"
  	"testing"
  	"time"

  	"go.opentelemetry.io/otel"

  	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
  	platformotel "github.com/Steamvis/gps-tracker/backend/internal/platform/otel"
  )

  func TestSetupInstallsGlobalProvidersAndShutsDown(t *testing.T) {
  	cfg := config.Config{
  		Env:          "test",
  		ServiceName:  "gps-api",
  		Version:      "test",
  		OTLPEndpoint: "localhost:4317",
  		LogLevel:     "info",
  	}

  	ctx := context.Background()
  	shutdown, err := platformotel.Setup(ctx, cfg)
  	if err != nil {
  		t.Fatalf("Setup returned error: %v", err)
  	}
  	if shutdown == nil {
  		t.Fatalf("Setup returned a nil shutdown func")
  	}

  	// A global tracer must be usable after Setup (it returns a non-nil span).
  	_, span := otel.Tracer("test").Start(ctx, "probe")
  	span.End()

  	sctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
  	defer cancel()
  	if err := shutdown(sctx); err != nil {
  		t.Fatalf("shutdown returned error: %v", err)
  	}
  }
  ```

- [ ] **Step 11: Run the otel test (expect FAIL).**
  Run:
  ```
  cd backend && go test ./internal/platform/otel/
  ```
  Expected FAIL output (`Setup` not implemented):
  ```
  internal/platform/otel/otel_test.go:13:25: undefined: platformotel.Setup
  FAIL	github.com/Steamvis/gps-tracker/backend/internal/platform/otel [build failed]
  ```

- [ ] **Step 12: Add the OTLP gRPC exporter, SDK, semconv and otelslog deps.**
  Run:
  ```
  cd backend && go get go.opentelemetry.io/otel/exporters/otlp/otlptrace/otlptracegrpc@v1.31.0 && go get go.opentelemetry.io/otel/exporters/otlp/otlpmetric/otlpmetricgrpc@v1.31.0 && go get go.opentelemetry.io/otel/exporters/otlp/otlplog/otlploggrpc@v0.7.0 && go get go.opentelemetry.io/otel/sdk/log@v0.7.0 && go get go.opentelemetry.io/otel/sdk/metric@v1.31.0 && go get go.opentelemetry.io/contrib/bridges/otelslog@v0.6.0
  ```
  Expected: modules added to `go.mod`/`go.sum`, exit code 0. (`go.opentelemetry.io/otel/log` and `.../log/global` v0.7.0 and `semconv/v1.27.0` come in transitively; pinned by `go mod tidy` in Step 17.)

- [ ] **Step 13: Commit the OTel deps.**
  Run:
  ```
  cd backend && git add go.mod go.sum && git commit -m "chore(backend): add OTLP gRPC exporters and OTel SDK log/metric deps" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] chore(backend): add OTLP gRPC exporters and OTel SDK log/metric deps
  ```

- [ ] **Step 14: Implement otel.Setup.**
  Create `backend/internal/platform/otel/otel.go`. The resource uses the non-deprecated `semconv.DeploymentEnvironmentName` (from `semconv/v1.27.0`); the global logger provider is installed via `go.opentelemetry.io/otel/log/global`:
  ```go
  // Package otel wires the OpenTelemetry SDK (traces, metrics and logs) with OTLP
  // gRPC exporters and installs the global providers.
  package otel

  import (
  	"context"
  	"errors"

  	"go.opentelemetry.io/otel"
  	"go.opentelemetry.io/otel/exporters/otlp/otlplog/otlploggrpc"
  	"go.opentelemetry.io/otel/exporters/otlp/otlpmetric/otlpmetricgrpc"
  	"go.opentelemetry.io/otel/exporters/otlp/otlptrace/otlptracegrpc"
  	"go.opentelemetry.io/otel/log/global"
  	"go.opentelemetry.io/otel/propagation"
  	sdklog "go.opentelemetry.io/otel/sdk/log"
  	sdkmetric "go.opentelemetry.io/otel/sdk/metric"
  	"go.opentelemetry.io/otel/sdk/resource"
  	sdktrace "go.opentelemetry.io/otel/sdk/trace"
  	semconv "go.opentelemetry.io/otel/semconv/v1.27.0"

  	"github.com/Steamvis/gps-tracker/backend/internal/platform/config"
  )

  // Setup configures global Tracer, Meter and Logger providers exporting over
  // OTLP gRPC (insecure) to cfg.OTLPEndpoint. It returns a shutdown function that
  // flushes and stops all three providers, joining their errors so none is
  // silently dropped.
  func Setup(ctx context.Context, cfg config.Config) (shutdown func(context.Context) error, err error) {
  	var shutdownFuncs []func(context.Context) error

  	shutdown = func(ctx context.Context) error {
  		var errs error
  		for _, fn := range shutdownFuncs {
  			errs = errors.Join(errs, fn(ctx))
  		}
  		shutdownFuncs = nil
  		return errs
  	}

  	handleErr := func(e error) (func(context.Context) error, error) {
  		return shutdown, errors.Join(e, shutdown(ctx))
  	}

  	res, err := resource.New(ctx,
  		resource.WithAttributes(
  			semconv.ServiceName(cfg.ServiceName),
  			semconv.ServiceVersion(cfg.Version),
  			semconv.DeploymentEnvironmentName(cfg.Env),
  		),
  	)
  	if err != nil {
  		return handleErr(err)
  	}

  	otel.SetTextMapPropagator(propagation.NewCompositeTextMapPropagator(
  		propagation.TraceContext{},
  		propagation.Baggage{},
  	))

  	// Traces.
  	traceExp, err := otlptracegrpc.New(ctx,
  		otlptracegrpc.WithEndpoint(cfg.OTLPEndpoint),
  		otlptracegrpc.WithInsecure(),
  	)
  	if err != nil {
  		return handleErr(err)
  	}
  	tp := sdktrace.NewTracerProvider(
  		sdktrace.WithResource(res),
  		sdktrace.WithBatcher(traceExp),
  	)
  	shutdownFuncs = append(shutdownFuncs, tp.Shutdown)
  	otel.SetTracerProvider(tp)

  	// Metrics.
  	metricExp, err := otlpmetricgrpc.New(ctx,
  		otlpmetricgrpc.WithEndpoint(cfg.OTLPEndpoint),
  		otlpmetricgrpc.WithInsecure(),
  	)
  	if err != nil {
  		return handleErr(err)
  	}
  	mp := sdkmetric.NewMeterProvider(
  		sdkmetric.WithResource(res),
  		sdkmetric.WithReader(sdkmetric.NewPeriodicReader(metricExp)),
  	)
  	shutdownFuncs = append(shutdownFuncs, mp.Shutdown)
  	otel.SetMeterProvider(mp)

  	// Logs.
  	logExp, err := otlploggrpc.New(ctx,
  		otlploggrpc.WithEndpoint(cfg.OTLPEndpoint),
  		otlploggrpc.WithInsecure(),
  	)
  	if err != nil {
  		return handleErr(err)
  	}
  	lp := sdklog.NewLoggerProvider(
  		sdklog.WithResource(res),
  		sdklog.WithProcessor(sdklog.NewBatchProcessor(logExp)),
  	)
  	shutdownFuncs = append(shutdownFuncs, lp.Shutdown)
  	global.SetLoggerProvider(lp)

  	return shutdown, nil
  }
  ```

- [ ] **Step 15: Run the otel test (expect PASS) and commit.**
  Run:
  ```
  cd backend && go test ./internal/platform/otel/ -v
  ```
  Expected PASS output (exporter creation is lazy, so no live collector is required; shutdown flushes within the timeout):
  ```
  --- PASS: TestSetupInstallsGlobalProvidersAndShutsDown (0.00s)
  PASS
  ok  	github.com/Steamvis/gps-tracker/backend/internal/platform/otel	0.0Xs
  ```
  Then:
  ```
  cd backend && git add internal/platform/otel/otel.go internal/platform/otel/otel_test.go && git commit -m "feat(platform): add OTel SDK Setup with OTLP gRPC traces, metrics and logs" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(platform): add OTel SDK Setup with OTLP gRPC traces, metrics and logs
  ```

- [ ] **Step 16: Route slog through the OTel log bridge in log.New.**
  Replace `backend/internal/platform/log/log.go` with the full file below. The returned logger fans out to the stdout JSON handler and the `otelslog` bridge (which exports slog records as OTel logs via the global LoggerProvider installed by `otel.Setup`). The bridge is created with the plain `otelslog.NewHandler(cfg.ServiceName)` — no options:
  ```go
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
  ```

- [ ] **Step 17: Build the module, then commit the log change.**
  Run:
  ```
  cd backend && go mod tidy && go build ./... && go test ./internal/platform/log/
  ```
  Expected: build produces no output (exit 0); the log package compiles:
  ```
  ?   	github.com/Steamvis/gps-tracker/backend/internal/platform/log	[no test files]
  ```
  Then:
  ```
  cd backend && git add internal/platform/log/log.go go.mod go.sum && git commit -m "feat(platform): fan slog out to stdout JSON and the OTel log bridge" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(platform): fan slog out to stdout JSON and the OTel log bridge
  ```

- [ ] **Step 18: Call otel.Setup early in main.go (full current main.go after Task 5).**
  Replace `backend/cmd/api/main.go` with the full file below. `otel.Setup` runs before `log.New` (so the otelslog bridge resolves the global LoggerProvider) and its shutdown is deferred. NO `-health` flag (that is Task 6):
  ```go
  // Command api serves the gps-api HTTP transport. Milestone M0 Task 5 adds OTel
  // (traces, metrics, logs) wired before logging; the -health subcommand is added
  // in Task 6.
  package main

  import (
  	"context"
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
  		_ = otelShutdown(shutdownCtx)
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
  ```
  Note: the `time` import is consumed by the deferred OTel shutdown's `context.WithTimeout(context.Background(), 5*time.Second)`, so no extra reference is needed.

- [ ] **Step 19: Tidy, build and run the whole suite.**
  Run:
  ```
  cd backend && go mod tidy && go build ./... && go vet ./... && go test ./...
  ```
  Expected: build and vet produce no output (exit 0); tests PASS:
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/internal/platform/config	0.00Xs
  ok  	github.com/Steamvis/gps-tracker/backend/internal/platform/otel	0.0Xs
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.0Xs
  ok  	github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo	0.00Xs
  ?   	github.com/Steamvis/gps-tracker/backend/internal/platform/log	[no test files]
  ?   	github.com/Steamvis/gps-tracker/backend/internal/adapter/postgres	[no test files]
  ```

- [ ] **Step 20: Commit the main.go OTel wiring.**
  Run:
  ```
  cd backend && git add cmd/api/main.go go.mod go.sum && git commit -m "feat(api): initialize OTel early and defer provider shutdown in main" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(api): initialize OTel early and defer provider shutdown in main
  ```

---

### Task 6: Backend Dockerfile (multi-stage, distroless) + -health subcommand

**Files:**
- Modify: `backend/cmd/api/main.go` (add the `-health` flag branch + `func healthCheck() error`)
- Create: `backend/cmd/api/health_test.go`
- Create: `backend/Dockerfile`
- Create: `backend/.dockerignore`

**Interfaces:**
- Consumes: `config.Load`, `transporthttp.NewRouter`/`NewServer`/`(*Server).Run`, `postgres.Migrate`/`New`, `serverinfo.New`, `otel.Setup` (all wired in `main.go` by Task 5 — this task only inserts the `-health` branch ahead of normal startup; it does NOT change the existing startup logic).
- Produces: `func healthCheck() error` in package `main` (GET `http://localhost:8080/healthz`, nil only on HTTP 200); the `-health` flag (the ONLY `flag.Bool("health", ...)` in the codebase) that exits 0/1 per `healthCheck()`; `backend/Dockerfile` (multi-stage, distroless, `HEALTHCHECK ["/app/api","-health"]`); `backend/.dockerignore`.

- [ ] **Step 1: Write the failing test for healthCheck().**
  Create `backend/cmd/api/health_test.go`. It forces an `httptest.Server` onto `127.0.0.1:8080` so `healthCheck()`'s fixed URL reaches it:
  ```go
  package main

  import (
  	"net"
  	"net/http"
  	"net/http/httptest"
  	"testing"
  )

  // listenerOn8080 forces an httptest server onto 127.0.0.1:8080 so that
  // healthCheck()'s fixed URL (http://localhost:8080/healthz) reaches it.
  func listenerOn8080(t *testing.T) net.Listener {
  	t.Helper()
  	ln, err := net.Listen("tcp", "127.0.0.1:8080")
  	if err != nil {
  		t.Skipf("cannot bind 127.0.0.1:8080 (already in use?): %v", err)
  	}
  	return ln
  }

  func TestHealthCheck_OK(t *testing.T) {
  	srv := httptest.NewUnstartedServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
  		if r.URL.Path != "/healthz" {
  			http.NotFound(w, r)
  			return
  		}
  		w.WriteHeader(http.StatusOK)
  		_, _ = w.Write([]byte(`{"status":"ok"}`))
  	}))
  	srv.Listener.Close()
  	srv.Listener = listenerOn8080(t)
  	srv.Start()
  	defer srv.Close()

  	if err := healthCheck(); err != nil {
  		t.Fatalf("healthCheck() = %v, want nil", err)
  	}
  }

  func TestHealthCheck_Unhealthy(t *testing.T) {
  	srv := httptest.NewUnstartedServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
  		w.WriteHeader(http.StatusInternalServerError)
  	}))
  	srv.Listener.Close()
  	srv.Listener = listenerOn8080(t)
  	srv.Start()
  	defer srv.Close()

  	if err := healthCheck(); err == nil {
  		t.Fatal("healthCheck() = nil, want error on HTTP 500")
  	}
  }
  ```

- [ ] **Step 2: Run the test and confirm it FAILS to compile.**
  Run:
  ```
  cd backend && go test ./cmd/api/ -run TestHealthCheck
  ```
  Expected FAIL output (the symbol does not exist yet):
  ```
  # github.com/Steamvis/gps-tracker/backend/cmd/api [github.com/Steamvis/gps-tracker/backend/cmd/api.test]
  ./health_test.go:34:6: undefined: healthCheck
  ./health_test.go:52:6: undefined: healthCheck
  FAIL	github.com/Steamvis/gps-tracker/backend/cmd/api [build failed]
  ```

- [ ] **Step 3: Add healthCheck() and the -health flag branch to main.go (full current main.go after Task 6).**
  Replace `backend/cmd/api/main.go` with the full final file below. It adds `flag`/`fmt`/`net/http` imports, the `-health` branch as the FIRST statements in `main()` (so it never reaches normal startup), and `func healthCheck() error`. This is the ONLY `flag.Bool("health", ...)` in the codebase:
  ```go
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

  	otelShutdown, err := platformotel.Setup(ctx, cfg)
  	if err != nil {
  		println("otel setup failed:", err.Error())
  		os.Exit(1)
  	}
  	defer func() {
  		shutdownCtx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
  		defer cancel()
  		_ = otelShutdown(shutdownCtx)
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
  	defer resp.Body.Close()
  	if resp.StatusCode != http.StatusOK {
  		return fmt.Errorf("healthz returned status %d", resp.StatusCode)
  	}
  	return nil
  }
  ```

- [ ] **Step 4: Run the test and confirm it PASSES.**
  Run:
  ```
  cd backend && go test ./cmd/api/ -run TestHealthCheck -v
  ```
  Expected PASS output:
  ```
  === RUN   TestHealthCheck_OK
  --- PASS: TestHealthCheck_OK (0.00s)
  === RUN   TestHealthCheck_Unhealthy
  --- PASS: TestHealthCheck_Unhealthy (0.00s)
  PASS
  ok  	github.com/Steamvis/gps-tracker/backend/cmd/api	0.0Xs
  ```

- [ ] **Step 5: Verify the whole backend still builds and tests pass.**
  Run:
  ```
  cd backend && go build ./... && go test ./...
  ```
  Expected: build produces no output (exit 0); tests PASS:
  ```
  ok  	github.com/Steamvis/gps-tracker/backend/cmd/api	0.0Xs
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.0Xs
  ok  	github.com/Steamvis/gps-tracker/backend/internal/platform/otel	0.0Xs
  ```

- [ ] **Step 6: Commit the -health subcommand and its test.**
  Run:
  ```
  cd backend && git add cmd/api/main.go cmd/api/health_test.go && git commit -m "feat(api): add -health subcommand for container HEALTHCHECK" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] feat(api): add -health subcommand for container HEALTHCHECK
  ```

- [ ] **Step 7: Create backend/.dockerignore.**
  Create `backend/.dockerignore`:
  ```
  # VCS / editor
  .git
  .gitignore
  .idea
  .vscode

  # Go build / test artifacts
  bin/
  *.test
  *.out
  coverage.out

  # Docs / tooling not needed in the image build
  *.md
  Dockerfile
  .dockerignore
  ```

- [ ] **Step 8: Create backend/Dockerfile (multi-stage, distroless).**
  Create `backend/Dockerfile`. Stage 1 builds a static, CGO-disabled `api` binary from `./cmd/api`; Stage 2 is `gcr.io/distroless/static:nonroot`, copies the binary to `/app/api`, runs as `nonroot`, exposes 8080, and wires the `-health` subcommand as the `HEALTHCHECK` (exec form — distroless has no shell):
  ```dockerfile
  # syntax=docker/dockerfile:1

  # ---- Stage 1: build a static, CGO-free binary ----
  FROM golang:1.22 AS build
  WORKDIR /src

  # Cache module downloads.
  COPY go.mod go.sum ./
  RUN go mod download

  # Build.
  COPY . .
  ARG VERSION=dev
  ENV CGO_ENABLED=0 GOOS=linux
  RUN go build -trimpath -ldflags="-s -w" -o /out/api ./cmd/api

  # ---- Stage 2: minimal distroless runtime ----
  FROM gcr.io/distroless/static:nonroot
  WORKDIR /app
  COPY --from=build /out/api /app/api
  USER nonroot:nonroot
  EXPOSE 8080
  ENTRYPOINT ["/app/api"]
  HEALTHCHECK --interval=10s --timeout=5s --start-period=20s --retries=3 CMD ["/app/api", "-health"]
  ```

- [ ] **Step 9: Build the image.**
  Run from the repo root:
  ```
  docker build -t gps-api ./backend
  ```
  Expected: both stages complete and the build ends with (exit 0):
  ```
  => => naming to docker.io/library/gps-api
  ```

- [ ] **Step 10: Verify /healthz works without a database.**
  `/healthz` has no dependencies, so the container needs no DB. Start it, probe, and stop it:
  ```
  docker run -d --rm --name gps-api-smoke -p 8080:8080 gps-api && sleep 2 && curl -fsS localhost:8080/healthz; echo
  ```
  Expected output:
  ```
  {"status":"ok"}
  ```
  (Note: `/readyz` and `/api/v1/server-info` would return 503 / fail here because no Postgres is wired — that is expected for this DB-less smoke test. The container's own startup will retry migrations against the missing DB; only `/healthz` is asserted here.)

- [ ] **Step 11: Verify the in-container -health subcommand and Docker health status.**
  With the container from Step 10 still running, exec the same probe Docker's HEALTHCHECK uses:
  ```
  docker exec gps-api-smoke /app/api -health; echo "exit=$?"
  ```
  Expected output:
  ```
  exit=0
  ```
  Confirm Docker reports the container healthy:
  ```
  docker inspect --format '{{.State.Health.Status}}' gps-api-smoke
  ```
  Expected output:
  ```
  healthy
  ```
  Then tear down:
  ```
  docker stop gps-api-smoke
  ```
  Expected output: `gps-api-smoke` (exit code 0).

- [ ] **Step 12: Commit the Dockerfile and .dockerignore.**
  Run:
  ```
  cd backend && git add Dockerfile .dockerignore && git commit -m "chore(api): multi-stage distroless Dockerfile with health probe" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (first line):
  ```
  [feat/gps-tracker-v2 <hash>] chore(api): multi-stage distroless Dockerfile with health probe
  ```

---

### Task 7: Frontend skeleton (Vite + React + TS + Tailwind + TanStack Query)

**Files:**
- Create: `frontend/package.json`
- Create: `frontend/index.html`
- Create: `frontend/vite.config.ts`
- Create: `frontend/tsconfig.json`
- Create: `frontend/tsconfig.node.json`
- Create: `frontend/tailwind.config.js`
- Create: `frontend/postcss.config.js`
- Create: `frontend/.eslintrc.cjs`
- Create: `frontend/.gitignore`
- Create: `frontend/vitest.setup.ts`
- Create: `frontend/src/vite-env.d.ts`
- Create: `frontend/src/index.css`
- Create: `frontend/src/main.tsx`
- Create: `frontend/src/App.tsx`
- Create: `frontend/src/api/client.ts`
- Create: `frontend/src/features/serverInfo/ServerInfoPanel.tsx`
- Test: `frontend/src/api/client.test.ts`
- Test: `frontend/src/features/serverInfo/ServerInfoPanel.test.tsx`

**Interfaces:**
- Produces: `src/api/client.ts` → `export type ServerInfo = { app: string; version: string; time: string; postgis: string }`
- Produces: `src/api/client.ts` → `export async function getServerInfo(): Promise<ServerInfo>` (GET `/api/v1/server-info`)
- Produces: `frontend` app + build output `dist/` (consumed by Task 8 Dockerfile)
- Consumes: backend `GET /api/v1/server-info -> 200 {"app":"gps-tracker","version":"<cfg.Version>","time":"<RFC3339>","postgis":"<version>"}` (Task 6), via Vite dev `server.proxy '/api' -> http://localhost:8080`

- [ ] **Step 1: Write `frontend/package.json`** — full contents:
```json
{
  "name": "gps-tracker-frontend",
  "private": true,
  "version": "0.0.0",
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "tsc -b && vite build",
    "lint": "eslint . --ext ts,tsx --max-warnings 0",
    "test": "vitest run",
    "preview": "vite preview"
  },
  "dependencies": {
    "@tanstack/react-query": "^5.59.0",
    "react": "^18.3.1",
    "react-dom": "^18.3.1"
  },
  "devDependencies": {
    "@testing-library/jest-dom": "^6.5.0",
    "@testing-library/react": "^16.0.1",
    "@types/react": "^18.3.11",
    "@types/react-dom": "^18.3.1",
    "@typescript-eslint/eslint-plugin": "^7.18.0",
    "@typescript-eslint/parser": "^7.18.0",
    "@vitejs/plugin-react": "^4.3.2",
    "autoprefixer": "^10.4.20",
    "eslint": "^8.57.1",
    "eslint-plugin-react-hooks": "^4.6.2",
    "eslint-plugin-react-refresh": "^0.4.12",
    "jsdom": "^25.0.1",
    "postcss": "^8.4.47",
    "tailwindcss": "^3.4.13",
    "typescript": "^5.6.2",
    "vite": "^5.4.8",
    "vitest": "^2.1.2"
  }
}
```

- [ ] **Step 2: Write `frontend/.gitignore`** — full contents:
```gitignore
node_modules
dist
*.local
.DS_Store
```

- [ ] **Step 3: Write `frontend/index.html`** — full contents:
```html
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GPS Tracker</title>
  </head>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>
```

- [ ] **Step 4: Write `frontend/tsconfig.json`** — full contents:
```json
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "Bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "react-jsx",
    "strict": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true,
    "types": ["vitest/globals", "@testing-library/jest-dom"]
  },
  "include": ["src", "vitest.setup.ts"],
  "references": [{ "path": "./tsconfig.node.json" }]
}
```

- [ ] **Step 5: Write `frontend/tsconfig.node.json`** — full contents (so `tsc -b` can type-check `vite.config.ts`):
```json
{
  "compilerOptions": {
    "composite": true,
    "skipLibCheck": true,
    "module": "ESNext",
    "moduleResolution": "Bundler",
    "allowSyntheticDefaultImports": true,
    "strict": true,
    "types": ["node"]
  },
  "include": ["vite.config.ts"]
}
```

- [ ] **Step 6: Write `frontend/vite.config.ts`** — full contents (react plugin + `/api` proxy to `http://localhost:8080` + vitest jsdom config):
```ts
/// <reference types="vitest/config" />
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true,
      },
    },
  },
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: './vitest.setup.ts',
  },
})
```

- [ ] **Step 7: Write `frontend/vitest.setup.ts`** — full contents (registers jest-dom matchers):
```ts
import '@testing-library/jest-dom/vitest'
```

- [ ] **Step 8: Write `frontend/src/vite-env.d.ts`** — full contents:
```ts
/// <reference types="vite/client" />
```

- [ ] **Step 9: Write `frontend/tailwind.config.js`** — full contents:
```js
/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

- [ ] **Step 10: Write `frontend/postcss.config.js`** — full contents:
```js
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
```

- [ ] **Step 11: Write `frontend/src/index.css`** — full contents (Tailwind v3 directives):
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

- [ ] **Step 12: Write `frontend/.eslintrc.cjs`** — full contents:
```cjs
module.exports = {
  root: true,
  env: { browser: true, es2020: true },
  extends: [
    'eslint:recommended',
    'plugin:@typescript-eslint/recommended',
  ],
  parser: '@typescript-eslint/parser',
  parserOptions: { ecmaVersion: 'latest', sourceType: 'module' },
  plugins: ['react-hooks', 'react-refresh'],
  ignorePatterns: ['dist', 'node_modules', '.eslintrc.cjs', 'vite.config.ts', 'postcss.config.js', 'tailwind.config.js'],
  rules: {
    ...require('eslint-plugin-react-hooks').configs.recommended.rules,
    'react-refresh/only-export-components': ['warn', { allowConstantExport: true }],
  },
}
```

- [ ] **Step 13: Install dependencies** — run:
```bash
cd frontend && npm install
```
Expected: dependency tree resolves and a `package-lock.json` is created; final line ends with `added <N> packages` and no `npm error` lines.

- [ ] **Step 14: Write the failing test `frontend/src/api/client.test.ts`** — full contents:
```ts
import { afterEach, describe, expect, it, vi } from 'vitest'
import { getServerInfo, type ServerInfo } from './client'

afterEach(() => {
  vi.restoreAllMocks()
})

describe('getServerInfo', () => {
  it('GETs /api/v1/server-info and parses the JSON body', async () => {
    const payload: ServerInfo = {
      app: 'gps-tracker',
      version: 'dev',
      time: '2026-06-20T10:00:00Z',
      postgis: '3.4 USE_GEOS=1',
    }
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      status: 200,
      json: async () => payload,
    })
    vi.stubGlobal('fetch', fetchMock)

    const result = await getServerInfo()

    expect(fetchMock).toHaveBeenCalledWith('/api/v1/server-info')
    expect(result).toEqual(payload)
  })

  it('throws when the response is not ok', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: false,
      status: 503,
      json: async () => ({}),
    })
    vi.stubGlobal('fetch', fetchMock)

    await expect(getServerInfo()).rejects.toThrow('server-info request failed: 503')
  })
})
```

- [ ] **Step 15: Run the test and confirm it FAILS** — run:
```bash
cd frontend && npm run test
```
Expected FAIL: Vitest reports a collection/transform error because `./client` does not exist yet, e.g. `Error: Failed to resolve import "./client" from "src/api/client.test.ts"` and the run exits non-zero.

- [ ] **Step 16: Write `frontend/src/api/client.ts`** — full contents (exact contract type + function):
```ts
export type ServerInfo = {
  app: string
  version: string
  time: string
  postgis: string
}

export async function getServerInfo(): Promise<ServerInfo> {
  const response = await fetch('/api/v1/server-info')
  if (!response.ok) {
    throw new Error(`server-info request failed: ${response.status}`)
  }
  return (await response.json()) as ServerInfo
}
```

- [ ] **Step 17: Run the client test and confirm it PASSES** — run:
```bash
cd frontend && npx vitest run src/api/client.test.ts
```
Expected PASS: `Test Files  1 passed (1)` and `Tests  2 passed (2)`, exit code 0.

- [ ] **Step 18: Write the failing test `frontend/src/features/serverInfo/ServerInfoPanel.test.tsx`** — full contents (mocks `getServerInfo`, asserts fields render and a loading state):
```tsx
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen } from '@testing-library/react'
import type { ReactNode } from 'react'
import { afterEach, describe, expect, it, vi } from 'vitest'
import type { ServerInfo } from '../../api/client'
import { ServerInfoPanel } from './ServerInfoPanel'

vi.mock('../../api/client', () => ({
  getServerInfo: vi.fn(),
}))

import { getServerInfo } from '../../api/client'

const mockedGetServerInfo = vi.mocked(getServerInfo)

function renderWithClient(ui: ReactNode) {
  const client = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  })
  return render(<QueryClientProvider client={client}>{ui}</QueryClientProvider>)
}

afterEach(() => {
  vi.clearAllMocks()
})

describe('ServerInfoPanel', () => {
  it('shows a loading state while the query is pending', () => {
    mockedGetServerInfo.mockReturnValue(new Promise<ServerInfo>(() => {}))
    renderWithClient(<ServerInfoPanel />)
    expect(screen.getByText('Loading server info…')).toBeInTheDocument()
  })

  it('renders app, version, time and postgis once loaded', async () => {
    const info: ServerInfo = {
      app: 'gps-tracker',
      version: 'dev',
      time: '2026-06-20T10:00:00Z',
      postgis: '3.4 USE_GEOS=1',
    }
    mockedGetServerInfo.mockResolvedValue(info)
    renderWithClient(<ServerInfoPanel />)

    expect(await screen.findByText('gps-tracker')).toBeInTheDocument()
    expect(screen.getByText('dev')).toBeInTheDocument()
    expect(screen.getByText('2026-06-20T10:00:00Z')).toBeInTheDocument()
    expect(screen.getByText('3.4 USE_GEOS=1')).toBeInTheDocument()
  })
})
```

- [ ] **Step 19: Run the panel test and confirm it FAILS** — run:
```bash
cd frontend && npx vitest run src/features/serverInfo/ServerInfoPanel.test.tsx
```
Expected FAIL: `Error: Failed to resolve import "./ServerInfoPanel" from "src/features/serverInfo/ServerInfoPanel.test.tsx"` and the run exits non-zero.

- [ ] **Step 20: Write `frontend/src/features/serverInfo/ServerInfoPanel.tsx`** — full contents (useQuery, loading + error + success states):
```tsx
import { useQuery } from '@tanstack/react-query'
import { getServerInfo } from '../../api/client'

function Field({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex justify-between gap-4 border-b border-slate-200 py-2 last:border-0">
      <dt className="font-medium text-slate-500">{label}</dt>
      <dd className="font-mono text-slate-900">{value}</dd>
    </div>
  )
}

export function ServerInfoPanel() {
  const { isPending, isError, data, error } = useQuery({
    queryKey: ['server-info'],
    queryFn: getServerInfo,
  })

  if (isPending) {
    return <p className="text-slate-500">Loading server info…</p>
  }

  if (isError) {
    return (
      <p className="text-red-600">
        Failed to load server info: {error instanceof Error ? error.message : 'unknown error'}
      </p>
    )
  }

  return (
    <dl className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
      <Field label="app" value={data.app} />
      <Field label="version" value={data.version} />
      <Field label="time" value={data.time} />
      <Field label="postgis" value={data.postgis} />
    </dl>
  )
}
```

- [ ] **Step 21: Run the panel test and confirm it PASSES** — run:
```bash
cd frontend && npx vitest run src/features/serverInfo/ServerInfoPanel.test.tsx
```
Expected PASS: `Test Files  1 passed (1)` and `Tests  2 passed (2)`, exit code 0.

- [ ] **Step 22: Write `frontend/src/App.tsx`** — full contents (renders `ServerInfoPanel`):
```tsx
import { ServerInfoPanel } from './features/serverInfo/ServerInfoPanel'

export default function App() {
  return (
    <main className="mx-auto max-w-xl px-4 py-12">
      <h1 className="mb-6 text-2xl font-semibold text-slate-900">GPS Tracker</h1>
      <ServerInfoPanel />
    </main>
  )
}
```

- [ ] **Step 23: Write `frontend/src/main.tsx`** — full contents (QueryClientProvider + render App):
```tsx
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import App from './App'
import './index.css'

const queryClient = new QueryClient()

const rootElement = document.getElementById('root')
if (!rootElement) {
  throw new Error('root element not found')
}

createRoot(rootElement).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <App />
    </QueryClientProvider>
  </StrictMode>,
)
```

- [ ] **Step 24: Run the full verification suite** — run:
```bash
cd frontend && npm run lint && npm run test && npm run build
```
Expected: `npm run lint` prints no errors and exits 0; `npm run test` reports `Test Files  2 passed (2)` and `Tests  4 passed (4)`; `npm run build` runs `tsc -b` with no type errors and Vite prints `✓ built in <time>` plus `dist/index.html`, `dist/assets/index-*.js` and `dist/assets/index-*.css`, exit code 0.

- [ ] **Step 25: Confirm the build output exists** — run:
```bash
ls frontend/dist && ls frontend/dist/assets
```
Expected: `frontend/dist` contains `index.html` and an `assets` directory; `frontend/dist/assets` lists a hashed `index-*.js` and `index-*.css` (this `dist/` is consumed by the Task 8 frontend Dockerfile).

- [ ] **Step 26: Commit** — run:
```bash
git add frontend
git commit -m "feat(frontend): scaffold Vite + React + TS + Tailwind + TanStack Query skeleton" -m "Add the frontend app with src/api/client.ts (getServerInfo/ServerInfo), a ServerInfoPanel feature that fetches GET /api/v1/server-info via useQuery, Tailwind styling, and Vitest tests for the API client and the panel (loading + populated states). Vite dev proxies /api -> http://localhost:8080. Scripts: dev, build, lint, test, preview." -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```
Expected: a single commit recording all `frontend/` files; `git status` is clean afterward.

---

### Task 8: Frontend Dockerfile (nginx) + reverse proxy config

**Files:**
- Create: `frontend/Dockerfile`
- Create: `frontend/nginx.conf`
- Create: `frontend/.dockerignore`
- Test: manual container verification (`docker build` + `docker run` + `curl`); the `/api` and `/ws` proxies are exercised end-to-end against the compose network in Task 9.

**Interfaces:**
- Consumes: the `frontend/` Vite + React 18 + TS project (produced by Task 6/7); `npm ci && npm run build` emits static assets into `frontend/dist/` (Vite default build output containing `index.html` + hashed `assets/`).
- Consumes: the compose `api` service reachable at host `api`, port `8080` (contract: `api 8080:8080`; the frontend nginx upstream is `http://api:8080`).
- Produces: `frontend/Dockerfile` + `frontend/nginx.conf`, referenced by the compose `frontend` service (`build context ../frontend`, `8081:80`, nginx proxies `/api` and `/ws` to `api:8080`, SPA fallback to `/index.html`).

- [ ] **Step 1: Write `frontend/.dockerignore`**

  Create `frontend/.dockerignore` with the full contents below so the build context stays small and the host's `node_modules`/`dist` never leak into the image (the image rebuilds them from a clean `npm ci`):

  ```
  node_modules
  dist
  .git
  .gitignore
  Dockerfile
  .dockerignore
  npm-debug.log
  *.local
  .env
  .env.*
  .DS_Store
  ```

- [ ] **Step 2: Write `frontend/nginx.conf`**

  Create `frontend/nginx.conf` with the full contents below. It serves the SPA on `:80` from `/usr/share/nginx/html`, proxies `/api/` and `/ws` to the compose `api` service at `http://api:8080`, adds the `Upgrade`/`Connection` headers and `proxy_http_version 1.1` required for the WebSocket endpoint, and falls back to `/index.html` for client-side routing:

  ```nginx
  map $http_upgrade $connection_upgrade {
      default upgrade;
      ''      close;
  }

  server {
      listen 80;
      server_name _;

      root /usr/share/nginx/html;
      index index.html;

      # REST API -> api service (compose network hostname "api", port 8080)
      location /api/ {
          proxy_pass http://api:8080;
          proxy_http_version 1.1;
          proxy_set_header Host $host;
          proxy_set_header X-Real-IP $remote_addr;
          proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
          proxy_set_header X-Forwarded-Proto $scheme;
      }

      # WebSocket live updates -> api service
      location /ws {
          proxy_pass http://api:8080;
          proxy_http_version 1.1;
          proxy_set_header Upgrade $http_upgrade;
          proxy_set_header Connection $connection_upgrade;
          proxy_set_header Host $host;
          proxy_set_header X-Real-IP $remote_addr;
          proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
          proxy_set_header X-Forwarded-Proto $scheme;
          proxy_read_timeout 3600s;
      }

      # SPA fallback: serve index.html for any client-side route
      location / {
          try_files $uri /index.html;
      }
  }
  ```

- [ ] **Step 3: Write `frontend/Dockerfile`**

  Create `frontend/Dockerfile` with the full contents below — a two-stage build: stage 1 (`node:20-alpine`) installs deps with `npm ci` and runs `npm run build` to produce `dist/`; stage 2 (`nginx:alpine`) copies the built assets into `/usr/share/nginx/html` and the proxy config into the nginx `conf.d` directory:

  ```dockerfile
  # ---- Stage 1: build static assets ----
  FROM node:20-alpine AS build
  WORKDIR /app
  COPY package.json package-lock.json ./
  RUN npm ci
  COPY . .
  RUN npm run build

  # ---- Stage 2: serve via nginx ----
  FROM nginx:alpine
  RUN rm /etc/nginx/conf.d/default.conf
  COPY nginx.conf /etc/nginx/conf.d/app.conf
  COPY --from=build /app/dist /usr/share/nginx/html
  EXPOSE 80
  CMD ["nginx", "-g", "daemon off;"]
  ```

- [ ] **Step 4: Build the image**

  Run the exact verification command from the project root:

  ```bash
  docker build -t gps-frontend ./frontend
  ```

  Expected output: the build runs both stages and ends with a success line, e.g.:

  ```
  => [build 4/6] RUN npm ci
  => [build 6/6] RUN npm run build
  => [stage-1 3/4] COPY nginx.conf /etc/nginx/conf.d/app.conf
  => [stage-1 4/4] COPY --from=build /app/dist /usr/share/nginx/html
  => exporting to image
  => => naming to docker.io/library/gps-frontend
  ```

  Exit code `0`.

- [ ] **Step 5: Run the container and verify the SPA is served**

  Start the container, probe the root, then stop it:

  ```bash
  docker run -d --rm --name gps-frontend-test -p 8081:80 gps-frontend
  sleep 1
  curl -sI localhost:8081
  curl -s localhost:8081 | grep -o '<div id="root">' || curl -s localhost:8081 | head -5
  docker stop gps-frontend-test
  ```

  Expected output: the headers show a `200` from nginx and the served document is `index.html`, e.g.:

  ```
  HTTP/1.1 200 OK
  Server: nginx
  Content-Type: text/html
  ...
  <div id="root">
  ```

  Note: hitting `localhost:8081/api/...` here returns `502 Bad Gateway` because there is no `api` upstream outside the compose network — that path is verified end-to-end in Task 9 with the full compose stack. The `200` on `/` and SPA fallback are the success criteria for this task.

- [ ] **Step 6: Verify the SPA fallback for a client-side route**

  Confirm an unknown path still returns the SPA (so deep links / client routes work):

  ```bash
  docker run -d --rm --name gps-frontend-test -p 8081:80 gps-frontend
  sleep 1
  curl -sI localhost:8081/some/client/route
  docker stop gps-frontend-test
  ```

  Expected output: `HTTP/1.1 200 OK` with `Content-Type: text/html` (nginx `try_files $uri /index.html` served `index.html`, not a `404`).

- [ ] **Step 7: Commit**

  ```bash
  git add frontend/Dockerfile frontend/nginx.conf frontend/.dockerignore
  git commit -m "feat: containerize frontend with nginx reverse proxy" -m "Two-stage Dockerfile (node:20-alpine build -> nginx:alpine serve); nginx.conf serves the SPA on :80 from /usr/share/nginx/html, proxies /api/ and /ws to api:8080 (WebSocket Upgrade headers, proxy_http_version 1.1), and falls back to /index.html for client-side routes. .dockerignore excludes node_modules and dist.

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```

---

### Task 9: App-tier docker-compose (datastores + api + frontend + collector)

**Files:**
- Create: `deploy/docker-compose.yml`
- Create: `deploy/mosquitto/mosquitto.conf`
- Create: `deploy/otel-collector.yaml`
- Create: `deploy/.env.example`
- Modify: `.gitignore` (ignore local `deploy/.env`)
- Test: (infra task — verified end-to-end with `docker compose up -d --build` + `curl`; no Go unit test file)

**Interfaces:**
- Consumes: `api` image built from `../backend` (Task 6) whose entrypoint `/app/api` runs `postgres.Migrate(ctx, cfg)` on boot then serves `NewServer(cfg.HTTPAddr, NewRouter(Deps{...}), log).Run(ctx)`; HEALTHCHECK `["/app/api","-health"]` performs `GET http://localhost:8080/healthz` and exits 0 on 200.
- Consumes: `frontend` image built from `../frontend` (Task 8) — nginx serving the SPA on container port 80, proxying `/api` and `/ws` to `api:8080`.
- Consumes (env, GPS_ prefix, exact defaults set ONCE on the api service via an explicit `environment:` map — the api service does NOT use `env_file`): `GPS_ENV=dev`, `GPS_SERVICE_NAME=gps-api`, `GPS_VERSION=dev`, `GPS_HTTP_ADDR=:8080`, `GPS_DATABASE_URL=postgres://gps:gps@postgres:5432/gps?sslmode=disable`, `GPS_OTLP_ENDPOINT=otel-collector:4317`, `GPS_LOG_LEVEL=info`.
- Produces: `deploy/docker-compose.yml` (extended in Task 10), `deploy/otel-collector.yaml` (debug-sink only; extended in Task 10), `deploy/.env.example` (committed), `deploy/mosquitto/mosquitto.conf`.
- Produces (HTTP, served by the `api` service on `8080:8080`): `GET /api/v1/server-info -> 200 {"app":"gps-tracker","version":"dev","time":"<RFC3339>","postgis":"<version>"}`; `GET /readyz -> 200 {"status":"ok","checks":{"postgres":"ok"}}`; `frontend` on `8081:80`.

- [ ] **Step 1: Create the Mosquitto dev config.**
  Write `deploy/mosquitto/mosquitto.conf` with the EXACT contents below (anonymous listener on 1883, dev only):
  ```conf
  # Mosquitto dev configuration — anonymous, plaintext. NOT for production.
  listener 1883 0.0.0.0
  allow_anonymous true
  persistence false
  log_dest stdout
  ```
  Verify it exists and is non-empty:
  ```bash
  test -s deploy/mosquitto/mosquitto.conf && grep -q 'allow_anonymous true' deploy/mosquitto/mosquitto.conf && echo OK
  ```
  Expected output:
  ```
  OK
  ```

- [ ] **Step 2: Create the OTel Collector config (debug sink only — Task 10 adds tempo/prometheus/loki).**
  At this stage the collector's only exporter is `debug`, so the app has a working telemetry sink and we can prove spans are received. Write `deploy/otel-collector.yaml` with the EXACT contents below:
  ```yaml
  # deploy/otel-collector.yaml — M0 Task 9 stage: receive OTLP and print to the debug
  # exporter so the app has a working sink. Task 10 rewires this to Tempo/Prometheus/Loki.
  receivers:
    otlp:
      protocols:
        grpc:
          endpoint: 0.0.0.0:4317
        http:
          endpoint: 0.0.0.0:4318

  processors:
    batch: {}

  exporters:
    debug:
      verbosity: detailed

  service:
    telemetry:
      logs:
        level: info
    pipelines:
      traces:
        receivers: [otlp]
        processors: [batch]
        exporters: [debug]
      metrics:
        receivers: [otlp]
        processors: [batch]
        exporters: [debug]
      logs:
        receivers: [otlp]
        processors: [batch]
        exporters: [debug]
  ```

- [ ] **Step 3: Validate the collector config with the contrib image (no full stack needed).**
  Run the exact command:
  ```bash
  docker run --rm -v "$PWD/deploy/otel-collector.yaml:/etc/otelcol/config.yaml:ro" \
    otel/opentelemetry-collector-contrib:0.110.0 validate --config=/etc/otelcol/config.yaml && echo CONFIG_OK
  ```
  Expected output (config parses with no error; the final echo confirms success):
  ```
  CONFIG_OK
  ```

- [ ] **Step 4: Create `deploy/.env.example` (committed) with every key the compose file consumes.**
  The api service hard-codes its `GPS_` values via an `environment:` map (Step 6), so these `GPS_` lines document the contract defaults; the `POSTGRES_*`/`MINIO_*` lines are the only ones interpolated into the compose file. Write `deploy/.env.example` with the EXACT contents below:
  ```dotenv
  # --- App (GPS_ prefix) — documents the values the api service sets directly in compose ---
  GPS_ENV=dev
  GPS_SERVICE_NAME=gps-api
  GPS_VERSION=dev
  GPS_HTTP_ADDR=:8080
  GPS_DATABASE_URL=postgres://gps:gps@postgres:5432/gps?sslmode=disable
  GPS_OTLP_ENDPOINT=otel-collector:4317
  GPS_LOG_LEVEL=info

  # --- Postgres (postgis/postgis) — interpolated by compose ---
  POSTGRES_USER=gps
  POSTGRES_PASSWORD=gps
  POSTGRES_DB=gps

  # --- MinIO — interpolated by compose ---
  MINIO_ROOT_USER=minio
  MINIO_ROOT_PASSWORD=minio12345
  ```
  Seed the working `.env` so interpolation resolves on first run:
  ```bash
  cp deploy/.env.example deploy/.env && echo ENV_SEEDED
  ```
  Expected output:
  ```
  ENV_SEEDED
  ```
  Note: `deploy/.env` is git-ignored (Step 5); only `deploy/.env.example` is committed.

- [ ] **Step 5: Ignore the local `deploy/.env` secret file.**
  Append `deploy/.env` to the repo `.gitignore` if absent. Run the exact command:
  ```bash
  grep -qxF 'deploy/.env' .gitignore || printf '\n# Local compose env (copied from deploy/.env.example)\ndeploy/.env\n' >> .gitignore
  grep -qxF 'deploy/.env' .gitignore && echo IGNORED
  ```
  Expected output:
  ```
  IGNORED
  ```

- [ ] **Step 6: Write the app-tier compose file.**
  Write `deploy/docker-compose.yml` with the EXACT contents below. `version:` is intentionally omitted (obsolete in Compose v2). All ports are `host:container`. The `api` service sets every `GPS_` variable EXACTLY ONCE via the explicit `environment:` map with literal values — it does NOT use `env_file`, so there is no double definition. It waits for `postgres` healthy and `otel-collector` started, runs `Migrate` on boot, then serves.
  ```yaml
  name: gps-tracker

  services:
    postgres:
      image: postgis/postgis:16-3.4
      restart: unless-stopped
      environment:
        POSTGRES_USER: ${POSTGRES_USER}
        POSTGRES_PASSWORD: ${POSTGRES_PASSWORD}
        POSTGRES_DB: ${POSTGRES_DB}
      ports:
        - "5432:5432"
      volumes:
        - postgres-data:/var/lib/postgresql/data
      healthcheck:
        test: ["CMD-SHELL", "pg_isready -U ${POSTGRES_USER} -d ${POSTGRES_DB}"]
        interval: 5s
        timeout: 5s
        retries: 10
        start_period: 10s

    redis:
      image: redis:7-alpine
      restart: unless-stopped
      ports:
        - "6379:6379"
      volumes:
        - redis-data:/data

    mosquitto:
      image: eclipse-mosquitto:2
      restart: unless-stopped
      ports:
        - "1883:1883"
      volumes:
        - ./mosquitto/mosquitto.conf:/mosquitto/config/mosquitto.conf:ro

    minio:
      image: minio/minio
      restart: unless-stopped
      command: server /data --console-address :9001
      environment:
        MINIO_ROOT_USER: ${MINIO_ROOT_USER}
        MINIO_ROOT_PASSWORD: ${MINIO_ROOT_PASSWORD}
      ports:
        - "9000:9000"
        - "9001:9001"
      volumes:
        - minio-data:/data

    mailpit:
      image: axllent/mailpit
      restart: unless-stopped
      ports:
        - "1025:1025"
        - "8025:8025"

    otel-collector:
      image: otel/opentelemetry-collector-contrib:0.110.0
      restart: unless-stopped
      command: ["--config=/etc/otelcol/config.yaml"]
      volumes:
        - ./otel-collector.yaml:/etc/otelcol/config.yaml:ro
      ports:
        - "4317:4317"
        - "4318:4318"
        - "8889:8889"

    api:
      build:
        context: ../backend
      restart: unless-stopped
      environment:
        GPS_ENV: dev
        GPS_SERVICE_NAME: gps-api
        GPS_VERSION: dev
        GPS_HTTP_ADDR: ":8080"
        GPS_DATABASE_URL: postgres://gps:gps@postgres:5432/gps?sslmode=disable
        GPS_OTLP_ENDPOINT: otel-collector:4317
        GPS_LOG_LEVEL: info
      ports:
        - "8080:8080"
      depends_on:
        postgres:
          condition: service_healthy
        otel-collector:
          condition: service_started
      healthcheck:
        test: ["CMD", "/app/api", "-health"]
        interval: 10s
        timeout: 5s
        retries: 5
        start_period: 15s

    frontend:
      build:
        context: ../frontend
      restart: unless-stopped
      ports:
        - "8081:80"
      depends_on:
        - api

  volumes:
    postgres-data:
    redis-data:
    minio-data:
  ```

- [ ] **Step 7: Validate the compose file resolves and renders without errors.**
  Run the exact command:
  ```bash
  docker compose -f deploy/docker-compose.yml --env-file deploy/.env config --quiet && echo COMPOSE_OK
  ```
  Expected output (interpolation + schema valid; no warning about an obsolete `version` key):
  ```
  COMPOSE_OK
  ```

- [ ] **Step 8: Bring the full app tier up and build the images.**
  Run the exact command:
  ```bash
  docker compose -f deploy/docker-compose.yml --env-file deploy/.env up -d --build
  ```
  Expected output (tail — all containers created/started under the named project `gps-tracker`):
  ```
  ✔ Container gps-tracker-postgres-1        Healthy
  ✔ Container gps-tracker-redis-1           Started
  ✔ Container gps-tracker-mosquitto-1       Started
  ✔ Container gps-tracker-minio-1           Started
  ✔ Container gps-tracker-mailpit-1         Started
  ✔ Container gps-tracker-otel-collector-1  Started
  ✔ Container gps-tracker-api-1             Started
  ✔ Container gps-tracker-frontend-1        Started
  ```

- [ ] **Step 9: Wait for the `api` container to report healthy (Migrate ran, server up).**
  Run the exact command (polls container health for up to ~60s):
  ```bash
  for i in $(seq 1 30); do \
    s=$(docker inspect -f '{{.State.Health.Status}}' gps-tracker-api-1 2>/dev/null); \
    echo "api health: $s"; \
    [ "$s" = "healthy" ] && break; \
    sleep 2; \
  done
  ```
  Expected output (final line):
  ```
  api health: healthy
  ```

- [ ] **Step 10: Verify the end-to-end server-info endpoint returns all 4 fields with a non-empty PostGIS version.**
  Run the exact command:
  ```bash
  curl -s localhost:8080/api/v1/server-info
  ```
  Expected output (the `time` value is the current RFC3339 instant; `postgis` is the running PostGIS build string, non-empty):
  ```
  {"app":"gps-tracker","version":"dev","time":"2026-06-20T10:00:00Z","postgis":"3.4 USE_GEOS=1 USE_PROJ=1 USE_STATS=1"}
  ```

- [ ] **Step 11: Verify the readiness probe is green (postgres check passes).**
  Run the exact command:
  ```bash
  curl -s -o /tmp/readyz.json -w '%{http_code}\n' localhost:8080/readyz; cat /tmp/readyz.json
  ```
  Expected output:
  ```
  200
  {"status":"ok","checks":{"postgres":"ok"}}
  ```

- [ ] **Step 12: Verify the frontend (nginx) serves the SPA on host port 8081.**
  Run the exact command:
  ```bash
  curl -sI localhost:8081 | head -n 1
  ```
  Expected output:
  ```
  HTTP/1.1 200 OK
  ```

- [ ] **Step 13: Confirm the collector received the app's spans via the debug exporter.**
  The `/api/v1/server-info` request from Step 10 emits a trace; the `debug` exporter (verbosity detailed) prints a `Traces` summary line plus per-span detail. Run the exact command:
  ```bash
  docker compose -f deploy/docker-compose.yml --env-file deploy/.env logs otel-collector | grep -m1 -E 'Traces|ResourceSpans|"spans"'
  ```
  Expected output (a debug-exporter line acknowledging received spans, e.g.):
  ```
  gps-tracker-otel-collector-1  | 2026-06-20T10:00:01.234Z  info  Traces  {"otelcol.component.id": "debug", "otelcol.component.kind": "exporter", "otelcol.signal": "traces", "resource spans": 1, "spans": 1}
  ```

- [ ] **Step 14: Tear the stack down.**
  Run the exact command:
  ```bash
  docker compose -f deploy/docker-compose.yml --env-file deploy/.env down
  ```
  Expected output (tail — all containers and the default network removed; named volumes preserved):
  ```
  ✔ Container gps-tracker-frontend-1        Removed
  ✔ Container gps-tracker-api-1             Removed
  ✔ Container gps-tracker-otel-collector-1  Removed
  ✔ Container gps-tracker-mailpit-1         Removed
  ✔ Container gps-tracker-minio-1           Removed
  ✔ Container gps-tracker-mosquitto-1       Removed
  ✔ Container gps-tracker-redis-1           Removed
  ✔ Container gps-tracker-postgres-1        Removed
  ✔ Network gps-tracker_default             Removed
  ```

- [ ] **Step 15: Commit the app-tier compose, collector config, mosquitto config, and env example.**
  Run the exact command:
  ```bash
  git add deploy/docker-compose.yml deploy/otel-collector.yaml deploy/mosquitto/mosquitto.conf deploy/.env.example .gitignore
  git commit \
    -m "feat(deploy): app-tier docker-compose with datastores, api, frontend and otel-collector" \
    -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output (summary line lists the four new files plus the `.gitignore` change):
  ```
  [feat/gps-tracker-v2 <hash>] feat(deploy): app-tier docker-compose with datastores, api, frontend and otel-collector
   5 files changed, NNN insertions(+)
  ```

---

### Task 10: Observability backends (Tempo/Loki/Prometheus/Grafana) + collector exporters + dashboard

**Files:**
- Create: `deploy/tempo.yaml`
- Create: `deploy/loki.yaml`
- Create: `deploy/prometheus.yml`
- Create: `deploy/grafana/provisioning/datasources/datasources.yml`
- Create: `deploy/grafana/provisioning/dashboards/dashboards.yml`
- Create: `deploy/grafana/provisioning/dashboards/gps-api.json`
- Create: `deploy/smoke.sh`
- Modify: `deploy/otel-collector.yaml` (rewrite to FULL final form: `otlp/tempo`, `prometheus`, `otlphttp/loki` exporters; rewired pipelines)
- Modify: `deploy/docker-compose.yml` (add `tempo`, `loki`, `prometheus`, `grafana` services and their named volumes — full edited service blocks shown below)
- Test: `deploy/smoke.sh` (end-to-end verification script; no Go unit test — infra task)

**Interfaces:**
- Consumes: telemetry from `internal/platform/otel.Setup` (global TracerProvider + MeterProvider + LoggerProvider, OTLP gRPC insecure to `cfg.OTLPEndpoint` = `otel-collector:4317`, resource `service.name=cfg.ServiceName` = `gps-api`, `service.version=cfg.Version`) and the HTTP metrics/traces from the `otelhttp` middleware (operation `gps-api`) mounted by `internal/transport/http.NewRouter` (Task 5).
- Consumes: the `otel-collector` service (host `4317`/`4318`/`8889`, config `deploy/otel-collector.yaml`) and the `api` service (`8080:8080`, `GET /api/v1/server-info -> 200 {...}`) from Task 9.
- Produces: `tempo` (`grafana/tempo:latest`, `3200:3200`, config `deploy/tempo.yaml`, OTLP at `tempo:4317` internal-only — NOT host-mapped), `loki` (`grafana/loki:latest`, `3100:3100`, config `deploy/loki.yaml`, OTLP ingestion at `/otlp` with `allow_structured_metadata`), `prometheus` (`prom/prometheus:latest`, `9090:9090`, config `deploy/prometheus.yml`, scrapes `otel-collector:8889`), `grafana` (`grafana/grafana:latest`, `3000:3000`, provisioning `deploy/grafana/provisioning/**`, anonymous-admin dev). Collector pipelines: `traces->[otlp/tempo]`, `metrics->[prometheus]`, `logs->[otlphttp/loki]`. Plus `deploy/smoke.sh`.

- [ ] **Step 1: Write `deploy/tempo.yaml` (full final form).**
  Single-binary (monolithic) Tempo for local dev. The collector pushes to `tempo:4317`; this OTLP port is internal-only (NOT host-mapped — host `4317` belongs to the collector). Tempo's HTTP query API is on `3200`. The config is deliberately minimal — `server`, `distributor` OTLP receivers, and local `storage.trace` with a WAL — so it stays valid across `grafana/tempo:latest` (monolithic mode manages ingester/live-store/compactor internally; explicit blocks for those are not required and can break across versions). `stream_over_http_enabled` keeps search streaming on. Write the complete file:
  ```yaml
  # deploy/tempo.yaml — single-binary (monolithic) Tempo for local dev.
  stream_over_http_enabled: true

  server:
    http_listen_port: 3200
    grpc_listen_port: 9095

  distributor:
    receivers:
      otlp:
        protocols:
          grpc:
            endpoint: 0.0.0.0:4317

  storage:
    trace:
      backend: local
      wal:
        path: /var/tempo/wal
      local:
        path: /var/tempo/blocks

  usage_report:
    reporting_enabled: false
  ```
  Verify it is valid YAML:
  ```bash
  python3 -c "import yaml; yaml.safe_load(open('deploy/tempo.yaml')); print('tempo.yaml: valid YAML')"
  ```
  Expected output:
  ```
  tempo.yaml: valid YAML
  ```
  Commit:
  ```bash
  git add deploy/tempo.yaml
  git commit -m "feat(deploy): add minimal single-binary Tempo config" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```

- [ ] **Step 2: Write `deploy/loki.yaml` (full final form).**
  Minimal single-process Loki 3.x with filesystem storage, `tsdb` v13 schema (required so `allow_structured_metadata` can be enabled), native OTLP ingestion on `/otlp`, and structured-metadata enabled (required when the collector forwards OTel logs over OTLP — Loki rejects the payload otherwise). Write the complete file:
  ```yaml
  # deploy/loki.yaml — single-process Loki 3.x for local dev (filesystem, no auth, OTLP-in).
  auth_enabled: false

  server:
    http_listen_port: 3100
    grpc_listen_port: 9096
    log_level: warn

  common:
    instance_addr: 127.0.0.1
    path_prefix: /loki
    storage:
      filesystem:
        chunks_directory: /loki/chunks
        rules_directory: /loki/rules
    replication_factor: 1
    ring:
      kvstore:
        store: inmemory

  schema_config:
    configs:
      - from: 2024-01-01
        store: tsdb
        object_store: filesystem
        schema: v13
        index:
          prefix: index_
          period: 24h

  limits_config:
    allow_structured_metadata: true     # required for native OTLP log ingestion
    volume_enabled: true
    retention_period: 24h

  compactor:
    working_directory: /loki/compactor
    delete_request_store: filesystem
    retention_enabled: true

  analytics:
    reporting_enabled: false
  ```
  Verify it is valid YAML and Loki accepts it:
  ```bash
  python3 -c "import yaml; yaml.safe_load(open('deploy/loki.yaml')); print('loki.yaml: valid YAML')"
  docker run --rm -v "$PWD/deploy/loki.yaml:/etc/loki/config.yaml:ro" grafana/loki:latest -config.file=/etc/loki/config.yaml -verify-config 2>&1 | tail -5 || true
  ```
  Expected output (the python line is authoritative; `-verify-config` prints no fatal error):
  ```
  loki.yaml: valid YAML
  ```
  Commit:
  ```bash
  git add deploy/loki.yaml
  git commit -m "feat(deploy): add minimal single-process Loki config with OTLP ingestion" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```

- [ ] **Step 3: Write `deploy/prometheus.yml` (full final form).**
  Single scrape job `otel-collector` targeting the collector's Prometheus exporter at `otel-collector:8889` (path `/metrics`). Write the complete file:
  ```yaml
  # deploy/prometheus.yml — scrapes the OTel Collector's Prometheus exporter.
  global:
    scrape_interval: 5s
    evaluation_interval: 5s

  scrape_configs:
    - job_name: otel-collector
      static_configs:
        - targets: ['otel-collector:8889']
  ```
  Verify with promtool:
  ```bash
  docker run --rm -v "$PWD/deploy/prometheus.yml:/etc/prometheus/prometheus.yml:ro" --entrypoint promtool prom/prometheus:latest check config /etc/prometheus/prometheus.yml
  ```
  Expected output:
  ```
  Checking /etc/prometheus/prometheus.yml
   SUCCESS: 0 rule files found
  ```
  Commit:
  ```bash
  git add deploy/prometheus.yml
  git commit -m "feat(deploy): add Prometheus scrape config for otel-collector" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```

- [ ] **Step 4: Write `deploy/grafana/provisioning/datasources/datasources.yml` (full final form).**
  Provision Prometheus (default), Tempo, and Loki at the in-network service hostnames. Write the complete file:
  ```yaml
  # deploy/grafana/provisioning/datasources/datasources.yml
  apiVersion: 1

  datasources:
    - name: Prometheus
      type: prometheus
      uid: prometheus
      access: proxy
      url: http://prometheus:9090
      isDefault: true
      editable: false

    - name: Tempo
      type: tempo
      uid: tempo
      access: proxy
      url: http://tempo:3200
      editable: false

    - name: Loki
      type: loki
      uid: loki
      access: proxy
      url: http://loki:3100
      editable: false
  ```

- [ ] **Step 5: Write `deploy/grafana/provisioning/dashboards/dashboards.yml` (full final form).**
  Dashboard provider that loads JSON dashboards from the mounted folder. Write the complete file:
  ```yaml
  # deploy/grafana/provisioning/dashboards/dashboards.yml
  apiVersion: 1

  providers:
    - name: gps-tracker
      orgId: 1
      folder: ''
      type: file
      disableDeletion: false
      updateIntervalSeconds: 10
      allowUiUpdates: false
      options:
        path: /etc/grafana/provisioning/dashboards
        foldersFromFilesStructure: false
  ```

- [ ] **Step 6: Write `deploy/grafana/provisioning/dashboards/gps-api.json` (full final form).**
  Three panels built from the `otelhttp` server metrics the Go SDK emits. The OTel metric `http.server.request.duration` (a seconds histogram) is exported by the collector's Prometheus exporter as `http_server_request_duration_seconds_*` (dots sanitized to underscores; the `_seconds` unit suffix and `_count`/`_bucket` histogram suffixes are appended). Because the collector's `prometheus` exporter has `resource_to_telemetry_conversion: enabled: true` (Step 8), the resource attribute `service.name=gps-api` is surfaced as the metric label `service_name="gps-api"`, so `http_server_request_duration_seconds_count{service_name="gps-api"}` matches. The third panel is a Loki logs panel filtered to `service_name="gps-api"`. Write the complete file:
  ```json
  {
    "annotations": { "list": [] },
    "editable": true,
    "fiscalYearStartMonth": 0,
    "graphTooltip": 0,
    "links": [],
    "liveNow": false,
    "panels": [
      {
        "datasource": { "type": "prometheus", "uid": "prometheus" },
        "fieldConfig": {
          "defaults": { "unit": "reqps", "custom": { "drawStyle": "line", "fillOpacity": 10 } },
          "overrides": []
        },
        "gridPos": { "h": 8, "w": 12, "x": 0, "y": 0 },
        "id": 1,
        "options": { "legend": { "displayMode": "list", "placement": "bottom" }, "tooltip": { "mode": "multi" } },
        "targets": [
          {
            "datasource": { "type": "prometheus", "uid": "prometheus" },
            "expr": "sum by (http_response_status_code) (rate(http_server_request_duration_seconds_count{service_name=\"gps-api\"}[1m]))",
            "legendFormat": "{{http_response_status_code}}",
            "refId": "A"
          }
        ],
        "title": "HTTP request rate (gps-api)",
        "type": "timeseries"
      },
      {
        "datasource": { "type": "prometheus", "uid": "prometheus" },
        "fieldConfig": {
          "defaults": { "unit": "s", "custom": { "drawStyle": "line", "fillOpacity": 10 } },
          "overrides": []
        },
        "gridPos": { "h": 8, "w": 12, "x": 12, "y": 0 },
        "id": 2,
        "options": { "legend": { "displayMode": "list", "placement": "bottom" }, "tooltip": { "mode": "multi" } },
        "targets": [
          {
            "datasource": { "type": "prometheus", "uid": "prometheus" },
            "expr": "histogram_quantile(0.95, sum by (le) (rate(http_server_request_duration_seconds_bucket{service_name=\"gps-api\"}[5m])))",
            "legendFormat": "p95",
            "refId": "A"
          }
        ],
        "title": "HTTP p95 latency (gps-api)",
        "type": "timeseries"
      },
      {
        "datasource": { "type": "loki", "uid": "loki" },
        "gridPos": { "h": 9, "w": 24, "x": 0, "y": 8 },
        "id": 3,
        "options": { "dedupStrategy": "none", "enableLogDetails": true, "showTime": true, "sortOrder": "Descending", "wrapLogMessage": true },
        "targets": [
          {
            "datasource": { "type": "loki", "uid": "loki" },
            "expr": "{service_name=\"gps-api\"}",
            "refId": "A"
          }
        ],
        "title": "gps-api logs",
        "type": "logs"
      }
    ],
    "schemaVersion": 39,
    "tags": ["gps-tracker"],
    "templating": { "list": [] },
    "time": { "from": "now-15m", "to": "now" },
    "timezone": "",
    "title": "GPS API Overview",
    "uid": "gps-api-overview",
    "version": 1
  }
  ```

- [ ] **Step 7: Verify the Grafana provisioning files are well-formed and commit them.**
  Run:
  ```bash
  python3 -c "import yaml; yaml.safe_load(open('deploy/grafana/provisioning/datasources/datasources.yml')); yaml.safe_load(open('deploy/grafana/provisioning/dashboards/dashboards.yml')); print('grafana provisioning yaml: valid')"
  python3 -c "import json; json.load(open('deploy/grafana/provisioning/dashboards/gps-api.json')); print('gps-api.json: valid JSON')"
  ```
  Expected output:
  ```
  grafana provisioning yaml: valid
  gps-api.json: valid JSON
  ```
  Commit:
  ```bash
  git add deploy/grafana/provisioning/datasources/datasources.yml deploy/grafana/provisioning/dashboards/dashboards.yml deploy/grafana/provisioning/dashboards/gps-api.json
  git commit -m "feat(deploy): provision Grafana datasources and gps-api dashboard" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```

- [ ] **Step 8: Rewrite `deploy/otel-collector.yaml` to its FULL final form (fan telemetry into the backends).**
  Replace the whole file. Traces go to Tempo (`otlp/tempo`, `tempo:4317`, `tls.insecure: true`); metrics are exposed for Prometheus to scrape (`prometheus` on `0.0.0.0:8889`, with `resource_to_telemetry_conversion: enabled: true` so `service.name` surfaces as the `service_name` label the dashboard queries); logs are pushed to Loki's native OTLP endpoint via `otlphttp/loki` (`http://loki:3100/otlp`). The dedicated `loki` exporter is deprecated/removed from the contrib distribution — Loki 3.x ingests OTLP natively, so `otlphttp` pointed at `/otlp` is the current correct approach. The `debug` exporter stays on every pipeline for local troubleshooting. Write the complete file:
  ```yaml
  # deploy/otel-collector.yaml — receives OTLP from the Go services, fans out to Tempo/Prometheus/Loki.
  receivers:
    otlp:
      protocols:
        grpc:
          endpoint: 0.0.0.0:4317
        http:
          endpoint: 0.0.0.0:4318

  processors:
    batch:
      timeout: 5s
    memory_limiter:
      check_interval: 1s
      limit_percentage: 80
      spike_limit_percentage: 20

  exporters:
    debug:
      verbosity: basic
    otlp/tempo:
      endpoint: tempo:4317
      tls:
        insecure: true
    prometheus:
      endpoint: 0.0.0.0:8889
      resource_to_telemetry_conversion:
        enabled: true       # surface service.name etc. as metric labels for the dashboard
    otlphttp/loki:
      endpoint: http://loki:3100/otlp

  service:
    telemetry:
      logs:
        level: info
    pipelines:
      traces:
        receivers: [otlp]
        processors: [memory_limiter, batch]
        exporters: [otlp/tempo, debug]
      metrics:
        receivers: [otlp]
        processors: [memory_limiter, batch]
        exporters: [prometheus, debug]
      logs:
        receivers: [otlp]
        processors: [memory_limiter, batch]
        exporters: [otlphttp/loki, debug]
  ```
  Verify the collector config is valid (the contrib image's `validate` subcommand parses the full pipeline graph and component configs without starting the collector):
  ```bash
  python3 -c "import yaml; yaml.safe_load(open('deploy/otel-collector.yaml')); print('otel-collector.yaml: valid YAML')"
  docker run --rm -v "$PWD/deploy/otel-collector.yaml:/etc/otelcol/config.yaml:ro" otel/opentelemetry-collector-contrib:0.110.0 validate --config=/etc/otelcol/config.yaml && echo "collector config OK"
  ```
  Expected output:
  ```
  otel-collector.yaml: valid YAML
  collector config OK
  ```
  Commit:
  ```bash
  git add deploy/otel-collector.yaml
  git commit -m "feat(deploy): export collector traces/metrics/logs to Tempo/Prometheus/Loki" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```

- [ ] **Step 9: Rewrite `deploy/docker-compose.yml` to its FULL final form (add the four backend services + volumes).**
  Overwrite the whole file with the contents below — it is the Task 9 file plus the `tempo`, `loki`, `prometheus`, and `grafana` services and their named volumes. The `api` service's `depends_on` already includes `otel-collector` (added in Task 9), so the collector is up before the api emits telemetry. Grafana mounts the provisioning tree read-only and runs anonymous-admin for dev. Write the complete file:
  ```yaml
  name: gps-tracker

  services:
    postgres:
      image: postgis/postgis:16-3.4
      restart: unless-stopped
      environment:
        POSTGRES_USER: ${POSTGRES_USER}
        POSTGRES_PASSWORD: ${POSTGRES_PASSWORD}
        POSTGRES_DB: ${POSTGRES_DB}
      ports:
        - "5432:5432"
      volumes:
        - postgres-data:/var/lib/postgresql/data
      healthcheck:
        test: ["CMD-SHELL", "pg_isready -U ${POSTGRES_USER} -d ${POSTGRES_DB}"]
        interval: 5s
        timeout: 5s
        retries: 10
        start_period: 10s

    redis:
      image: redis:7-alpine
      restart: unless-stopped
      ports:
        - "6379:6379"
      volumes:
        - redis-data:/data

    mosquitto:
      image: eclipse-mosquitto:2
      restart: unless-stopped
      ports:
        - "1883:1883"
      volumes:
        - ./mosquitto/mosquitto.conf:/mosquitto/config/mosquitto.conf:ro

    minio:
      image: minio/minio
      restart: unless-stopped
      command: server /data --console-address :9001
      environment:
        MINIO_ROOT_USER: ${MINIO_ROOT_USER}
        MINIO_ROOT_PASSWORD: ${MINIO_ROOT_PASSWORD}
      ports:
        - "9000:9000"
        - "9001:9001"
      volumes:
        - minio-data:/data

    mailpit:
      image: axllent/mailpit
      restart: unless-stopped
      ports:
        - "1025:1025"
        - "8025:8025"

    otel-collector:
      image: otel/opentelemetry-collector-contrib:0.110.0
      restart: unless-stopped
      command: ["--config=/etc/otelcol/config.yaml"]
      volumes:
        - ./otel-collector.yaml:/etc/otelcol/config.yaml:ro
      ports:
        - "4317:4317"
        - "4318:4318"
        - "8889:8889"
      depends_on:
        - tempo
        - loki

    tempo:
      image: grafana/tempo:latest
      restart: unless-stopped
      command: ["-config.file=/etc/tempo.yaml"]
      volumes:
        - ./tempo.yaml:/etc/tempo.yaml:ro
        - tempo-data:/var/tempo
      ports:
        - "3200:3200"   # Tempo HTTP API; 4317 stays internal (the collector owns host 4317)

    loki:
      image: grafana/loki:latest
      restart: unless-stopped
      command: ["-config.file=/etc/loki/config.yaml"]
      volumes:
        - ./loki.yaml:/etc/loki/config.yaml:ro
        - loki-data:/loki
      ports:
        - "3100:3100"

    prometheus:
      image: prom/prometheus:latest
      restart: unless-stopped
      command:
        - "--config.file=/etc/prometheus/prometheus.yml"
        - "--storage.tsdb.path=/prometheus"
      volumes:
        - ./prometheus.yml:/etc/prometheus/prometheus.yml:ro
        - prometheus-data:/prometheus
      ports:
        - "9090:9090"
      depends_on:
        - otel-collector

    grafana:
      image: grafana/grafana:latest
      restart: unless-stopped
      environment:
        GF_AUTH_ANONYMOUS_ENABLED: "true"
        GF_AUTH_ANONYMOUS_ORG_ROLE: "Admin"
        GF_AUTH_DISABLE_LOGIN_FORM: "true"
        GF_USERS_DEFAULT_THEME: "dark"
      volumes:
        - ./grafana/provisioning:/etc/grafana/provisioning:ro
        - grafana-data:/var/lib/grafana
      ports:
        - "3000:3000"
      depends_on:
        - prometheus
        - tempo
        - loki

    api:
      build:
        context: ../backend
      restart: unless-stopped
      environment:
        GPS_ENV: dev
        GPS_SERVICE_NAME: gps-api
        GPS_VERSION: dev
        GPS_HTTP_ADDR: ":8080"
        GPS_DATABASE_URL: postgres://gps:gps@postgres:5432/gps?sslmode=disable
        GPS_OTLP_ENDPOINT: otel-collector:4317
        GPS_LOG_LEVEL: info
      ports:
        - "8080:8080"
      depends_on:
        postgres:
          condition: service_healthy
        otel-collector:
          condition: service_started
      healthcheck:
        test: ["CMD", "/app/api", "-health"]
        interval: 10s
        timeout: 5s
        retries: 5
        start_period: 15s

    frontend:
      build:
        context: ../frontend
      restart: unless-stopped
      ports:
        - "8081:80"
      depends_on:
        - api

  volumes:
    postgres-data:
    redis-data:
    minio-data:
    tempo-data:
    loki-data:
    prometheus-data:
    grafana-data:
  ```
  Verify the compose file parses with all services present:
  ```bash
  docker compose -f deploy/docker-compose.yml --env-file deploy/.env config --quiet && echo "compose config OK"
  docker compose -f deploy/docker-compose.yml --env-file deploy/.env config --services | sort | tr '\n' ' '; echo
  ```
  Expected output (every contract service is present):
  ```
  compose config OK
  api frontend grafana loki mailpit minio mosquitto otel-collector postgres prometheus redis tempo
  ```
  Commit:
  ```bash
  git add deploy/docker-compose.yml
  git commit -m "feat(deploy): add Tempo/Loki/Prometheus/Grafana to compose stack" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```

- [ ] **Step 10: Write `deploy/smoke.sh` (full final form).**
  End-to-end smoke test: it brings the stack up, drives traffic against `GET /api/v1/server-info`, then asserts a trace landed in Tempo (`GET /api/search?limit=1` returns a `traceID`), the http metric exists in Prometheus (`http_server_request_duration_seconds_count{service_name="gps-api"}` query returns a non-empty result), and Grafana answers `200`. It polls with retries because the backends take time to ingest, and prints a clear PASS/FAIL. Write the complete file:
  ```bash
  #!/usr/bin/env bash
  # deploy/smoke.sh — boot the stack, drive traffic, and assert the observability path is green.
  set -euo pipefail

  cd "$(dirname "$0")"
  COMPOSE=(docker compose -f docker-compose.yml --env-file .env)

  fail() { echo "SMOKE FAIL: $*" >&2; exit 1; }

  # Retry an assertion command until it succeeds or the deadline passes.
  retry() {
    local desc="$1"; shift
    local deadline=$((SECONDS + 120))
    until "$@"; do
      [ "$SECONDS" -lt "$deadline" ] || fail "$desc (timed out after 120s)"
      sleep 3
    done
    echo "OK: $desc"
  }

  echo "==> Bringing up the stack"
  "${COMPOSE[@]}" up -d --build

  echo "==> Waiting for the API to answer /healthz"
  retry "api /healthz -> 200" bash -c 'curl -fsS -o /dev/null localhost:8080/healthz'

  echo "==> Generating traffic"
  for i in $(seq 1 10); do curl -s localhost:8080/api/v1/server-info >/dev/null; done

  echo "==> Asserting a trace reached Tempo"
  retry "tempo has at least one trace" bash -c \
    'curl -s "http://localhost:3200/api/search?limit=1" | grep -q traceID'

  echo "==> Asserting the http metric exists in Prometheus"
  retry "prometheus has http_server_request_duration_seconds_count for gps-api" bash -c \
    'curl -s --get "http://localhost:9090/api/v1/query" --data-urlencode "query=http_server_request_duration_seconds_count{service_name=\"gps-api\"}" | grep -q "\"status\":\"success\"" && curl -s --get "http://localhost:9090/api/v1/query" --data-urlencode "query=http_server_request_duration_seconds_count{service_name=\"gps-api\"}" | grep -q "\"result\":\[{"'

  echo "==> Asserting Grafana is serving"
  retry "grafana returns HTTP 200" bash -c \
    '[ "$(curl -s -o /dev/null -w "%{http_code}" localhost:3000)" = "200" ]'

  echo "SMOKE PASS: observability stack is green"
  ```
  Make it executable and lint it:
  ```bash
  chmod +x deploy/smoke.sh
  bash -n deploy/smoke.sh && echo "smoke.sh: syntax OK"
  ```
  Expected output:
  ```
  smoke.sh: syntax OK
  ```

- [ ] **Step 11: Run the full smoke test end-to-end.**
  Run:
  ```bash
  ./deploy/smoke.sh
  ```
  Expected output (final lines):
  ```
  OK: api /healthz -> 200
  OK: tempo has at least one trace
  OK: prometheus has http_server_request_duration_seconds_count for gps-api
  OK: grafana returns HTTP 200
  SMOKE PASS: observability stack is green
  ```

- [ ] **Step 12: Tear the stack down (clean volumes) and confirm exit code.**
  Run:
  ```bash
  docker compose -f deploy/docker-compose.yml --env-file deploy/.env down -v && echo "stack down, volumes removed (exit $?)"
  ```
  Expected output (containers and the `*-data` volumes removed):
  ```
  stack down, volumes removed (exit 0)
  ```

- [ ] **Step 13: Commit `deploy/smoke.sh`.**
  Run:
  ```bash
  git add deploy/smoke.sh
  git commit -m "test(deploy): add observability stack smoke test" -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output:
  ```
  [feat/gps-tracker-v2 <hash>] test(deploy): add observability stack smoke test
   1 file changed, NN insertions(+)
   create mode 100755 deploy/smoke.sh
  ```

---

### Task 11: Taskfile (developer experience)

**Files:**
- Create: `/Users/alexey/Projects/gps-tracker/Taskfile.yml`
- Modify: (none — the `ci` task is added here in full; Task 12 does NOT re-touch it)
- Test: (infra task — verified by running `task --list`, `task be:test`, `task be:migrate-new`; no automated test file)

**Interfaces:**
- Consumes: `deploy/docker-compose.yml` (Tasks 9/10), `deploy/smoke.sh` (Task 10), backend `go test ./...` / `sqlc generate` / `golangci-lint run` and goose-managed migrations at `internal/adapter/postgres/db/migrations` (Tasks 2–6), frontend `npm run test|lint|build` (Task 7).
- Produces: `Taskfile.yml` (go-task v3 schema) at repo root, referenced by README + CI-local. Tasks: `up`, `down`, `down-v`, `logs`, `smoke`, `be:test`, `be:test:int`, `be:lint`, `be:sqlc`, `be:migrate-new` (NAME var), `fe:test`, `fe:lint`, `fe:build`, `fmt`, `ci`.

- [ ] **Step 1: Verify go-task is installed.**
  Run the exact command:
  ```bash
  task --version
  ```
  Expected output (any v3.x line is fine, e.g.):
  ```
  Task version: v3.39.2 (h1:...)
  ```
  If `task: command not found`, install it first (`brew install go-task` on macOS, or `go install github.com/go-task/task/v3/cmd/task@latest`), then re-run until a `Task version: v3.x` line prints. Do not commit anything in this step.

- [ ] **Step 2: Write the complete `Taskfile.yml` at repo root.**
  Create `/Users/alexey/Projects/gps-tracker/Taskfile.yml` with the full contents below. `COMPOSE` is a global var so the long compose invocation is written once; `be:migrate-new` declares `NAME` as a required var and calls goose's `create` subcommand against the migrations directory the embedded migrations live in — `internal/adapter/postgres/db/migrations` (per the resolution registry; there is NO `backend/db/` directory). The `ci` task is the local equivalent of the GitHub Actions pipeline.
  ```yaml
  # https://taskfile.dev — go-task v3 schema.
  # Everyday developer commands for the gps-tracker monorepo.
  version: '3'

  vars:
    COMPOSE: docker compose -f deploy/docker-compose.yml --env-file deploy/.env

  tasks:
    up:
      desc: Build and start the full local stack (detached).
      cmds:
        - '{{.COMPOSE}} up -d --build'

    down:
      desc: Stop and remove the stack containers.
      cmds:
        - '{{.COMPOSE}} down'

    down-v:
      desc: Stop the stack and delete its volumes (DESTROYS data).
      cmds:
        - '{{.COMPOSE}} down -v'

    logs:
      desc: Follow logs from all stack services.
      cmds:
        - '{{.COMPOSE}} logs -f'

    smoke:
      desc: Run the end-to-end smoke test against the running stack.
      cmds:
        - bash deploy/smoke.sh

    be:test:
      desc: Run the backend Go unit tests.
      dir: backend
      cmds:
        - go test ./...

    be:test:int:
      desc: Run the backend Go integration tests (build tag 'integration').
      dir: backend
      cmds:
        - go test -tags=integration ./...

    be:lint:
      desc: Lint the backend with golangci-lint.
      dir: backend
      cmds:
        - golangci-lint run

    be:sqlc:
      desc: Regenerate the sqlc query code.
      dir: backend
      cmds:
        - sqlc generate

    be:migrate-new:
      desc: "Create a new goose SQL migration. Usage: task be:migrate-new NAME=add_devices_table"
      dir: backend
      requires:
        vars: [NAME]
      cmds:
        - goose -dir internal/adapter/postgres/db/migrations create {{.NAME}} sql

    fe:test:
      desc: Run the frontend unit tests.
      dir: frontend
      cmds:
        - npm run test

    fe:lint:
      desc: Lint the frontend.
      dir: frontend
      cmds:
        - npm run lint

    fe:build:
      desc: Build the frontend production bundle.
      dir: frontend
      cmds:
        - npm run build

    fmt:
      desc: Format Go code and auto-fix frontend lint issues.
      cmds:
        - cd backend && gofmt -w .
        - cd frontend && npm run lint -- --fix

    ci:
      desc: Run the full local CI gate (backend lint+vet+test, sqlc drift, frontend lint+test+build).
      cmds:
        - task: be:lint
        - cmd: go vet ./...
          dir: backend
        - task: be:test
        - cmd: sqlc generate
          dir: backend
        - cmd: git diff --exit-code
        - task: fe:lint
        - task: fe:test
        - task: fe:build
  ```

- [ ] **Step 3: Verify `task --list` enumerates every task.**
  Run the exact command from repo root:
  ```bash
  task --list
  ```
  Expected output (alphabetical by task name; descriptions as written above):
  ```
  task: Available tasks for this project:
  * be:lint:           Lint the backend with golangci-lint.
  * be:migrate-new:    Create a new goose SQL migration. Usage: task be:migrate-new NAME=add_devices_table
  * be:sqlc:           Regenerate the sqlc query code.
  * be:test:           Run the backend Go unit tests.
  * be:test:int:       Run the backend Go integration tests (build tag 'integration').
  * ci:                Run the full local CI gate (backend lint+vet+test, sqlc drift, frontend lint+test+build).
  * down:              Stop and remove the stack containers.
  * down-v:            Stop the stack and delete its volumes (DESTROYS data).
  * fe:build:          Build the frontend production bundle.
  * fe:lint:           Lint the frontend.
  * fe:test:           Run the frontend unit tests.
  * fmt:               Format Go code and auto-fix frontend lint issues.
  * logs:              Follow logs from all stack services.
  * smoke:             Run the end-to-end smoke test against the running stack.
  * up:                Build and start the full local stack (detached).
  ```
  All 15 tasks must appear. If `task` reports `yaml: ...` or `task: Failed to parse`, fix the YAML and re-run until the list prints.

- [ ] **Step 4: Verify `task be:test` runs the Go tests through the Taskfile.**
  Run the exact command from repo root:
  ```bash
  task be:test
  ```
  Expected output (Task echoes the command, then `go test` runs in `backend/` and passes; package list reflects Tasks 2–6):
  ```
  task: [be:test] go test ./...
  ok  	github.com/Steamvis/gps-tracker/backend/internal/usecase/serverinfo	0.012s
  ok  	github.com/Steamvis/gps-tracker/backend/internal/transport/http	0.031s
  ...
  ```
  Exit code must be 0. This confirms the `dir: backend` + `go test ./...` wiring; if `task` prints `dir ".../backend" does not exist` or `go: command not found`, fix the path/PATH and re-run.

- [ ] **Step 5: Verify `task be:migrate-new` validates its required NAME var.**
  Run the exact command from repo root (intentionally omitting NAME to confirm the `requires` guard, so no stray migration file is created):
  ```bash
  task be:migrate-new
  ```
  Expected output (Task refuses to run because the required var is missing; exit code non-zero):
  ```
  task: Task "be:migrate-new" cancelled because it is missing required variables:
    - NAME
  ```
  This confirms the guard and the `internal/adapter/postgres/db/migrations` target path are wired without generating a committed-looking migration. Do not run it with a real NAME here.

- [ ] **Step 6: Commit the Taskfile.**
  Run the exact commands from repo root:
  ```bash
  git add Taskfile.yml
  git commit -m "chore: add root Taskfile for developer workflows" -m "Adds go-task v3 Taskfile.yml with up/down/down-v/logs/smoke, backend (be:test, be:test:int, be:lint, be:sqlc, be:migrate-new targeting internal/adapter/postgres/db/migrations), frontend (fe:test, fe:lint, fe:build), fmt, and an aggregate ci task mirroring the GitHub Actions pipeline." -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output:
  ```
  [feat/gps-tracker-v2 <hash>] chore: add root Taskfile for developer workflows
   1 file changed, NN insertions(+)
   create mode 100644 Taskfile.yml
  ```

---

### Task 12: CI pipeline (GitHub Actions) + golangci-lint config

**Files:**
- Create: `/Users/alexey/Projects/gps-tracker/backend/.golangci.yml`
- Create: `/Users/alexey/Projects/gps-tracker/.github/workflows/ci.yml`
- Modify: (none — the local `ci` task already lives in `Taskfile.yml` from Task 11)
- Test: none (infra/config task — verification is `yaml.safe_load` + the authoritative run on push)

**Interfaces:**
- Consumes: Go module `github.com/Steamvis/gps-tracker/backend` (go 1.22) at `./backend`; `backend/sqlc.yaml` with queries at `internal/adapter/postgres/db/queries` and schema at `internal/adapter/postgres/db/migrations`, generating `internal/adapter/postgres/sqlcgen` (Task 3); `backend/Dockerfile` (Task 6) and `frontend/Dockerfile` (Task 8); `./frontend` package.json scripts `lint`, `test`, `build`; integration tests guarded by `//go:build integration` run via `go test -tags=integration ./...` (Docker is available on GitHub `ubuntu-latest` runners).
- Produces: `.github/workflows/ci.yml` with jobs `backend`, `sqlc-drift`, `frontend`, and `images`; `backend/.golangci.yml`. Triggers: `on.push.branches: [master, feat/gps-tracker-v2]` and `on.pull_request`.

- [ ] **Step 1: Write the complete `backend/.golangci.yml` (golangci-lint v1.x / schema v1).**
  Per the resolution registry the CI pins `golangci/golangci-lint-action@v6`, which runs golangci-lint **v1.x** (pinned `v1.64.8`, the last v1 release). The config therefore uses the **v1 schema** (NOT the v2 `version: "2"` schema introduced by action v7 / lint v2). The sqlc-generated package is excluded from linting. Write the full file:
  ```yaml
  # golangci-lint v1.x configuration (schema v1).
  # Pinned linter version in CI: v1.64.8 (see .github/workflows/ci.yml).
  run:
    timeout: 5m
    tests: true

  output:
    formats:
      - format: colored-line-number

  linters:
    disable-all: true
    enable:
      - govet
      - staticcheck
      - errcheck
      - gofmt
      - goimports
      - revive
      - ineffassign

  linters-settings:
    govet:
      enable-all: true
    goimports:
      local-prefixes: github.com/Steamvis/gps-tracker/backend
    revive:
      severity: warning
      rules:
        - name: blank-imports
        - name: context-as-argument
        - name: context-keys-type
        - name: dot-imports
        - name: error-return
        - name: error-strings
        - name: error-naming
        - name: exported
        - name: increment-decrement
        - name: var-declaration
        - name: package-comments
        - name: range
        - name: receiver-naming
        - name: time-naming
        - name: unexported-return
        - name: indent-error-flow
        - name: errorf
        - name: empty-block
        - name: superfluous-else
        - name: unreachable-code
        - name: redefines-builtin-id

  issues:
    max-issues-per-linter: 0
    max-same-issues: 0
    exclude-rules:
      # sqlc-generated code is not ours to lint.
      - path: internal/adapter/postgres/sqlcgen/
        linters:
          - revive
          - errcheck
          - staticcheck
      # Relax exported-symbol comments and errcheck in tests.
      - path: _test\.go
        linters:
          - revive
          - errcheck
  ```
  Verify it is valid YAML:
  ```bash
  python3 -c "import yaml; yaml.safe_load(open('/Users/alexey/Projects/gps-tracker/backend/.golangci.yml')); print('YAML OK')"
  ```
  Expected output:
  ```
  YAML OK
  ```
  Optional (only if golangci-lint v1.64.x is installed locally; CI is authoritative):
  ```bash
  cd /Users/alexey/Projects/gps-tracker/backend && golangci-lint config verify
  ```
  Expected when run with a v1.6x binary:
  ```
  No errors found
  ```
  Commit:
  ```bash
  cd /Users/alexey/Projects/gps-tracker && git add backend/.golangci.yml
  git commit -m "chore: add golangci-lint v1.6x config for backend" \
    -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```

- [ ] **Step 2: Write the complete `.github/workflows/ci.yml`.**
  Four jobs: `backend` (golangci-lint, go vet, unit tests, integration tests), `sqlc-drift` (regenerate then fail on diff), `frontend` (npm ci/lint/test/build), and `images` (build both Dockerfiles into the local daemon, then Trivy-scan the built images and fail on HIGH/CRITICAL — spec §7). Action versions confirmed current: `actions/checkout@v4`, `actions/setup-go@v5`, `actions/setup-node@v4`, `golangci/golangci-lint-action@v6` (runs lint v1.x — matches the v1 `.golangci.yml`), `sqlc-dev/setup-sqlc@v3`, `docker/setup-buildx-action@v3`, `docker/build-push-action@v6` (`load: true` builds into the local daemon without pushing), `aquasecurity/trivy-action@0.36.0` (current stable release; the 0.0.1–0.34.2 tags were affected by the March 2026 supply-chain incident, so 0.36.0 is the pinned safe version). Triggers fire on push to `[master, feat/gps-tracker-v2]` and on every pull_request. Write the full file:
  ```yaml
  name: ci

  on:
    push:
      branches: [master, feat/gps-tracker-v2]
    pull_request:

  permissions:
    contents: read

  concurrency:
    group: ci-${{ github.ref }}
    cancel-in-progress: true

  jobs:
    backend:
      name: backend
      runs-on: ubuntu-latest
      steps:
        - uses: actions/checkout@v4

        - uses: actions/setup-go@v5
          with:
            go-version: "1.22"
            cache: true
            cache-dependency-path: backend/go.sum

        - name: golangci-lint
          uses: golangci/golangci-lint-action@v6
          with:
            version: v1.64.8
            working-directory: backend
            args: --timeout=5m

        - name: go vet
          working-directory: backend
          run: go vet ./...

        - name: unit tests
          working-directory: backend
          run: go test ./...

        - name: integration tests
          working-directory: backend
          run: go test -tags=integration ./...

    sqlc-drift:
      name: sqlc-drift
      runs-on: ubuntu-latest
      steps:
        - uses: actions/checkout@v4

        - uses: sqlc-dev/setup-sqlc@v3
          with:
            sqlc-version: "1.27.0"

        - name: sqlc generate
          working-directory: backend
          run: sqlc generate

        - name: fail on generated-code drift
          run: git diff --exit-code

    frontend:
      name: frontend
      runs-on: ubuntu-latest
      steps:
        - uses: actions/checkout@v4

        - uses: actions/setup-node@v4
          with:
            node-version: "20"
            cache: npm
            cache-dependency-path: frontend/package-lock.json

        - name: install deps
          working-directory: frontend
          run: npm ci

        - name: lint
          working-directory: frontend
          run: npm run lint

        - name: test
          working-directory: frontend
          run: npm run test

        - name: build
          working-directory: frontend
          run: npm run build

    images:
      name: images
      runs-on: ubuntu-latest
      steps:
        - uses: actions/checkout@v4

        - uses: docker/setup-buildx-action@v3

        - name: build backend image
          uses: docker/build-push-action@v6
          with:
            context: backend
            file: backend/Dockerfile
            push: false
            load: true
            tags: gps-tracker/api:ci

        - name: build frontend image
          uses: docker/build-push-action@v6
          with:
            context: frontend
            file: frontend/Dockerfile
            push: false
            load: true
            tags: gps-tracker/frontend:ci

        - name: trivy scan backend image
          uses: aquasecurity/trivy-action@0.36.0
          with:
            image-ref: gps-tracker/api:ci
            format: table
            exit-code: "1"
            ignore-unfixed: true
            vuln-type: os,library
            severity: HIGH,CRITICAL

        - name: trivy scan frontend image
          uses: aquasecurity/trivy-action@0.36.0
          with:
            image-ref: gps-tracker/frontend:ci
            format: table
            exit-code: "1"
            ignore-unfixed: true
            vuln-type: os,library
            severity: HIGH,CRITICAL
  ```

- [ ] **Step 3: Verify `ci.yml` parses as valid YAML and contains the four required jobs.**
  Run:
  ```bash
  python3 -c "import yaml; d=yaml.safe_load(open('/Users/alexey/Projects/gps-tracker/.github/workflows/ci.yml')); print(sorted(d['jobs'].keys()))"
  ```
  Expected output:
  ```
  ['backend', 'frontend', 'images', 'sqlc-drift']
  ```
  Confirm the push triggers include both branches:
  ```bash
  python3 -c "import yaml; d=yaml.safe_load(open('/Users/alexey/Projects/gps-tracker/.github/workflows/ci.yml')); print(d[True]['push']['branches'])"
  ```
  Expected output (PyYAML parses the bare `on:` key as the boolean `True`):
  ```
  ['master', 'feat/gps-tracker-v2']
  ```
  If `yamllint` is available, also run:
  ```bash
  yamllint -d "{extends: default, rules: {line-length: disable, document-start: disable, truthy: {check-keys: false}}}" /Users/alexey/Projects/gps-tracker/.github/workflows/ci.yml && echo "YAMLLINT OK"
  ```
  Expected output:
  ```
  YAMLLINT OK
  ```

- [ ] **Step 4: Confirm `actionlint` (if installed) finds no workflow errors; otherwise note the CI run is authoritative.**
  Run:
  ```bash
  command -v actionlint >/dev/null 2>&1 && actionlint /Users/alexey/Projects/gps-tracker/.github/workflows/ci.yml && echo "ACTIONLINT OK" || echo "actionlint not installed — authoritative check is the pipeline run on push"
  ```
  Expected output (when actionlint is absent locally):
  ```
  actionlint not installed — authoritative check is the pipeline run on push
  ```
  Expected output (when actionlint is present and the file is well-formed):
  ```
  ACTIONLINT OK
  ```

- [ ] **Step 5: Commit the CI workflow.**
  Run:
  ```bash
  cd /Users/alexey/Projects/gps-tracker && git add .github/workflows/ci.yml
  git commit -m "ci: add GitHub Actions pipeline (backend, sqlc-drift, frontend, images+trivy)" \
    -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
  Expected output:
  ```
  [feat/gps-tracker-v2 <hash>] ci: add GitHub Actions pipeline (backend, sqlc-drift, frontend, images+trivy)
   1 file changed, NN insertions(+)
   create mode 100644 .github/workflows/ci.yml
  ```

- [ ] **Step 6: Push the branch and confirm all four jobs are green on the authoritative run.**
  Run:
  ```bash
  git push -u origin feat/gps-tracker-v2
  ```
  Then confirm the workflow run (requires the `gh` CLI; the run is the authoritative verification of the lint/test/build/scan gates):
  ```bash
  gh run list --branch feat/gps-tracker-v2 --workflow ci --limit 1
  gh run watch "$(gh run list --branch feat/gps-tracker-v2 --workflow ci --limit 1 --json databaseId --jq '.[0].databaseId')" --exit-status
  ```
  Expected output (the run completes with every job — `backend`, `sqlc-drift`, `frontend`, `images` — succeeding):
  ```
  ✓ ci · feat/gps-tracker-v2
  ✓ backend
  ✓ sqlc-drift
  ✓ frontend
  ✓ images
  ```

---

### Task 13: Root README v2 + quickstart docs

**Files:**
- Modify: `README.md` (replace the stub README created in Task 1 with the real v2 README)
- Test: none (docs task — verification is render-check + grep assertions, no unit test)

**Interfaces:**
- Consumes: the FROZEN CONTRACT — repo layout (`/backend`, `/frontend`, `/deploy`, `/tools`, `/legacy`), compose ports (api `8080`, frontend `8081`, grafana `3000`, prometheus `9090`, tempo `3200`, loki `3100`, minio `9000`/`9001`, mailpit `8025`, postgres `5432`, redis `6379`, mqtt `1883`), the Taskfile targets `up`, `be:test`, `fe:test`, `smoke` (defined in Task 11's `Taskfile.yml`), and the design spec at `docs/superpowers/specs/2026-06-20-gps-tracker-v2-design.md`.
- Produces: the project README (`README.md`) — title, description, Architecture, Ports table, Quickstart, Development, Project layout, Milestone roadmap (M0..M6), Legacy note.

- [ ] **Step 1: Write the complete `README.md`**

  Overwrite `README.md` (which currently holds the Task 1 stub) with the full v2 README below.

  `README.md`:
  ````markdown
  # GPS Fleet Tracker v2

  A multi-tenant **GPS fleet-tracking** platform: a **Go** backend (modular monolith)
  and a **React** single-page app that show live vehicle positions on a map, record
  trips, and raise geofence/speed/offline alerts. It is a ground-up rewrite of a 2020
  Laravel dashboard, rebuilt to flagship-portfolio quality — a reimagined domain on a
  clean, observable, container-native stack that boots locally with a single command.

  ## Architecture

  One Go module, three binaries with clean module boundaries:

  - **`cmd/api`** — REST/JSON for the SPA (chi) plus a WebSocket endpoint for live
    updates; httpOnly cookie sessions.
  - **`cmd/ingest`** — device-facing intake (HTTP batch + MQTT subscriber); authenticates
    per-device tokens and publishes raw positions to the bus.
  - **`cmd/worker`** — consumes the bus, persists positions, auto-segments trips,
    evaluates geofences/alerts, and emits live events.

  The bus is **Redis Streams** (ingest → worker, durable consumer groups); live fan-out
  to the SPA uses Redis pub/sub → `api` → **WebSocket**. State lives in
  **Postgres + PostGIS** (domain + geography), Redis (streams/cache/sessions), and MinIO
  (photos). The frontend renders the map with **MapLibre GL**. Everything is instrumented
  with **OpenTelemetry** (traces + metrics + logs) flowing through an OTel Collector into
  Prometheus, Tempo, Loki, and Grafana.

  The full design — domain model, API surface, and rationale — lives in
  [docs/superpowers/specs/2026-06-20-gps-tracker-v2-design.md](docs/superpowers/specs/2026-06-20-gps-tracker-v2-design.md).

  ## Ports

  All services run via `deploy/docker-compose.yml` (host ports):

  | Service        | Port(s)      | Purpose                              |
  | -------------- | ------------ | ------------------------------------ |
  | api            | 8080         | Go REST/JSON + WebSocket             |
  | frontend       | 8081         | React SPA (nginx, proxies `/api`)    |
  | grafana        | 3000         | Dashboards (anonymous admin, dev)    |
  | prometheus     | 9090         | Metrics                              |
  | tempo          | 3200         | Traces                               |
  | loki           | 3100         | Logs                                 |
  | minio          | 9000 / 9001  | Object storage / console             |
  | mailpit        | 8025         | Mail catcher UI                      |
  | postgres       | 5432         | PostgreSQL + PostGIS                  |
  | redis          | 6379         | Streams / cache / sessions           |
  | mqtt           | 1883         | Mosquitto (device ingest)            |

  ## Quickstart

  Requires Docker and [Task](https://taskfile.dev).

  ```bash
  git clone git@github.com:Steamvis/gps-tracker.git
  cd gps-tracker
  task up
  ```

  Then open:

  - **App:** <http://localhost:8081>
  - **Grafana:** <http://localhost:3000>

  ## Development

  Common tasks (run `task --list` for the full set):

  | Command         | Description                                            |
  | --------------- | ------------------------------------------------------ |
  | `task up`       | Build and start the full stack via docker-compose      |
  | `task be:test`  | Run the Go backend test suite (testcontainers-go)      |
  | `task fe:test`  | Run the frontend test suite (vitest)                   |
  | `task smoke`    | Smoke-check the running stack (health + server-info)   |

  ## Project layout

  ```text
  .
  ├── backend/    Go module github.com/Steamvis/gps-tracker/backend (cmd/api, cmd/ingest, cmd/worker)
  ├── frontend/   Vite + React 18 + TypeScript SPA (Tailwind, TanStack Query, MapLibre)
  ├── deploy/     docker-compose.yml + observability configs (otel/tempo/loki/prometheus/grafana)
  ├── tools/      device simulator (future)
  └── legacy/     archived Laravel PHP app (reference only)
  ```

  ## Milestone roadmap

  | Milestone | Scope |
  | --------- | ----- |
  | **M0 — Foundation** (current) | monorepo, Go skeleton (clean arch, chi, config, slog + OTel), Postgres+PostGIS + sqlc + goose, docker-compose, CI, health, React+Vite+TS+Tailwind skeleton, one end-to-end endpoint with observability green |
  | **M1 — Identity & tenancy** | users, orgs, memberships/RBAC, cookie sessions + CSRF, email verification, password reset, React auth + onboarding |
  | **M2 — Fleet** | vehicles CRUD + photos (MinIO), devices CRUD + tokens, reference data, screens |
  | **M3 — Ingestion & pipeline** | device HTTP + MQTT, per-device auth, Redis Streams, worker → positions (PostGIS), auto trip segmentation, metrics, simulator |
  | **M4 — Live & history** | WebSocket live map (MapLibre), snapshot, history + playback |
  | **M5 — Geofences & events** | geofence CRUD + drawing, enter/exit + speeding/offline alerts, events feed, notifications |
  | **M6 — Polish** | Grafana dashboards, demo seed, e2e (Playwright), docs/README |

  ## Legacy

  The original 2020 Laravel 7 / PHP application is archived under
  [`/legacy`](legacy/) for reference. It is no longer maintained; v2 is a clean rewrite,
  not a port.
  ````

- [ ] **Step 2: Verify the file exists and headings render**

  Run:

  ```bash
  test -f README.md && grep -nE '^#{1,2} ' README.md
  ```

  Expected output (the heading outline — one `#` title plus the `##` sections in order):

  ```text
  1:# GPS Fleet Tracker v2
  12:## Architecture
  33:## Ports
  50:## Quickstart
  64:## Development
  77:## Project layout
  90:## Milestone roadmap
  101:## Legacy
  ```

  (Line numbers are illustrative; the assertion is that `test -f` succeeds and all eight headings appear exactly once in this order.)

- [ ] **Step 3: Confirm all referenced ports match the contract**

  Run:

  ```bash
  for p in 8080 8081 3000 9090 3200 3100 9000 9001 8025 5432 6379 1883; do \
    grep -q "| $p " README.md || grep -q "$p" README.md || echo "MISSING PORT: $p"; \
  done; echo "ports checked"
  ```

  Expected output (no `MISSING PORT` lines):

  ```text
  ports checked
  ```

- [ ] **Step 4: Confirm referenced task names and the spec link resolve**

  Run:

  ```bash
  grep -q 'task up' README.md \
    && grep -q 'task be:test' README.md \
    && grep -q 'task fe:test' README.md \
    && grep -q 'task smoke' README.md \
    && grep -q 'docs/superpowers/specs/2026-06-20-gps-tracker-v2-design.md' README.md \
    && grep -q 'localhost:8081' README.md \
    && grep -q 'localhost:3000' README.md \
    && echo "refs ok"
  ```

  Expected output:

  ```text
  refs ok
  ```

- [ ] **Step 5: Commit**

  Run:

  ```bash
  git add README.md
  git commit -m "docs: replace stub README with v2 quickstart and roadmap" \
    -m "Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
  ```
