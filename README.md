# GPS Fleet Tracker v2

[![CI](https://github.com/Steamvis/gps-tracker/actions/workflows/ci.yml/badge.svg?branch=feat/gps-tracker-v2)](https://github.com/Steamvis/gps-tracker/actions/workflows/ci.yml)

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

_M0 ships the `cmd/api` walking skeleton; `cmd/ingest` and `cmd/worker` land in M3
(see the roadmap below)._

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

Working on the code directly (outside the containers) expects **Go 1.26+** and
**Node 20+**. Common tasks (run `task --list` for the full set):

| Command         | Description                                            |
| --------------- | ------------------------------------------------------ |
| `task up`       | Build and start the full stack via docker-compose      |
| `task be:test`  | Run the Go backend unit tests (`be:test:int` adds integration) |
| `task fe:test`  | Run the frontend test suite (vitest)                   |
| `task smoke`    | Smoke-check the running stack (health + server-info)   |

CI (GitHub Actions, `.github/workflows/ci.yml`) runs the same gates — backend
lint/vet/test, sqlc drift, frontend lint/test/build, and Trivy image scans.

## Project layout

```text
.
├── backend/    Go module github.com/Steamvis/gps-tracker/backend (cmd/api today; cmd/ingest + cmd/worker in M3)
├── frontend/   Vite + React 18 + TypeScript SPA (Tailwind, TanStack Query; MapLibre map in M4)
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
