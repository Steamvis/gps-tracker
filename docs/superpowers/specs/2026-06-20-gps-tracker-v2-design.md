# GPS Fleet Tracker v2 — Design

> Status: **Approved** (brainstorm) · Date: 2026-06-20 · Author: alexey
>
> Rewrite of a 2020 Laravel 7 GPS-tracking dashboard (author's first project, written as a
> junior) into a Go backend + React frontend, redesigned to "flagship portfolio" quality:
> a reimagined domain plus senior-level platform engineering.

## 1. Context & Goal

### 1.1 What exists today (legacy, `/legacy`)
Laravel 7 / PHP 7.4 / MySQL 5.7 / Redis, Blade + jQuery + Bootstrap 4 + Yandex Maps, built
on docker-compose (gateway / backend / frontend / node / redis / mysql).

Domain: fleet GPS tracking.
- `User` → email-verified registration; `Company` (one owner per company, `users.company_id UNIQUE`);
  `Car` (truck with unique `api_code`, soft-delete, makes/characteristics, photo).
- `CarRoute` (trip) → `CarRouteSection` (segment) → `CarPoint` (lat/lon `double`). Points linked
  to sections via a `points_section` join table.
- GPS ingest: `POST /api/v1/gps/{lat}/{lon}/{apiCode_carID}/{start}_{end}` — no auth, coords and
  car id baked into the URL.
- Computes route/segment distance and "moving time" — **stored as a pre-rendered string**
  (`moving_time_ru/en`) in the DB.
- Dashboard: Yandex map with markers + polylines; vehicle list with connected/disconnected
  (last point < 5 min). **No real-time** — static server render; queue used only for emails.

### 1.2 Legacy weak spots (explicitly fixed in v2)
- Coordinates as `double` with no spatial/time indexes.
- `CarRoute.start_time/end_time` stored as **strings**.
- "Moving time" presentation strings stored in the DB (`moving_time_ru/en`).
- Company counters maintained by hand.
- `company_id UNIQUE` breaks the advertised "staff" concept (one user per company).
- `User.level` defined but unused for real RBAC.
- Ingestion endpoint unauthenticated; device identity = car id concatenated into the token.
- No real-time updates.

### 1.3 Goal
Flagship-portfolio rewrite: reimagine the domain *and* demonstrate platform-engineering craft.
Not a 1:1 port — fix the modeling mistakes, add the features a real fleet tracker needs
(real device ingestion, live tracking, multi-tenancy with staff, geofences/alerts), and wrap it
in a clean, observable, CI-driven, container-native stack that runs locally with one command.

## 2. Decisions (locked during brainstorm)

| Area | Decision |
|---|---|
| Goal | Flagship portfolio: reimagined domain + senior engineering |
| Maps | MapLibre GL + OpenStreetMap |
| GPS ingest | JSON HTTP + MQTT, per-device tokens, batch |
| Go stack | chi + pgx + sqlc, clean architecture, REST/JSON + OpenAPI |
| User auth | httpOnly cookie sessions + CSRF |
| Real-time | WebSocket (fan-out via Redis pub/sub) |
| Infra depth | Containers + CI + observability (OTel → Prometheus/Grafana/Loki/Tempo); **no** k8s/Terraform |
| Runtime | Local: docker-compose / kind |

### Veto-able defaults (chosen, changeable)
- Message bus: **Redis Streams** (alt: NATS) — keeps the stack lean since Redis is already present.
- Frontend UI kit: **Tailwind + shadcn/ui** (alt: MUI).
- MQTT broker: **Mosquitto**. Object storage: **MinIO** (S3-compatible). Mail (dev): **mailpit**.
- Migrations: **goose**. Legacy PHP: **archived under `/legacy`, not deleted**.

## 3. Architecture — modular monolith + worker

Recommended over microservices (ops overhead with no k8s) and over a single do-everything binary
(no scaling/failure isolation). One Go module, three binaries, clean module boundaries.

```
                 ┌──────────────┐      WebSocket (live)      ┌─────────────┐
   React SPA ───▶│   cmd/api    │◀──────────────────────────│   Browser   │
  (cookie auth)  │ REST + WS    │                            └─────────────┘
                 └──────┬───────┘
                        │ reads/writes
   devices ──HTTP──▶ ┌──┴──────────┐   Redis Streams   ┌──────────────┐
   devices ──MQTT──▶ │  cmd/ingest │ ────────────────▶ │  cmd/worker  │
 (device token)      │ accept+auth │   (raw positions) │ trips/metrics│
                     └─────────────┘                   └──────┬───────┘
                                                              │ writes positions/trips/events
                            ┌─────────────────────────────────┴───────────┐
                            ▼                  ▼               ▼            ▼
                    Postgres+PostGIS        Redis          MinIO       Redis pub/sub
                    (domain + geo)     (streams/cache/    (photos)   (live fan-out → api → WS)
                                        sessions)
```

- **`cmd/api`** — REST/JSON for the SPA (chi) + WebSocket endpoint for live updates. Cookie sessions.
- **`cmd/ingest`** — device-facing intake: HTTP batch endpoint + MQTT subscriber. Authenticates by
  per-device token, validates, and publishes raw positions to the bus. No business logic.
- **`cmd/worker`** — consumes the bus: persists positions (PostGIS), auto-segments trips by idle gap,
  computes distance/duration, evaluates geofences/alerts, and publishes live events to Redis pub/sub
  which `api` fans out over WebSocket.
- **Bus:** Redis Streams (ingest → worker, durable, consumer groups). **Live fan-out:** Redis pub/sub.

### Clean architecture layering (per binary, shared internal packages)
- `internal/domain` — entities, value objects, domain logic (no framework imports).
- `internal/usecase` (application) — orchestration, transactions, ports (interfaces).
- `internal/adapter` — driven adapters: `postgres` (sqlc/pgx), `redis`, `mqtt`, `minio`, `mailer`.
- `internal/transport` — driving adapters: `http` (chi handlers, middleware), `ws`, `ingesthttp`.
- `internal/platform` — config, logging (slog), OTel wiring, health.

## 4. Data model (PostgreSQL + PostGIS)

Fixes: coordinates → PostGIS `geography` + GiST index; times → `timestamptz` (never strings);
distances/durations → numbers (meters/seconds), formatted client-side; **no `moving_time_ru/en` in
the DB**; device decoupled from vehicle; real multi-tenancy via memberships.

```sql
users(id uuid pk, email citext unique, password_hash text,            -- argon2id
      first_name text, last_name text, locale text,
      email_verified_at timestamptz null, prefs jsonb default '{}',
      created_at, updated_at)

organizations(id uuid pk, name text, country_id int null,             -- was "company"
              logo_url text null, created_at, updated_at)

memberships(id uuid pk, user_id uuid fk, org_id uuid fk,              -- real staff + RBAC
            role text check in (owner,admin,operator,viewer),
            created_at, unique(user_id, org_id))

vehicles(id uuid pk, org_id uuid fk, name text, make_id int null,     -- was "car"
         model text null, plate text null, vin text null, year int null,
         color text null, photo_url text null,
         deleted_at timestamptz null, created_at, updated_at)

devices(id uuid pk, org_id uuid fk, vehicle_id uuid fk null,          -- decoupled from vehicle
        name text, token_hash text, protocol text,                   -- token hashed, never stored raw
        last_seen_at timestamptz null, created_at)

positions(id bigint pk, device_id uuid fk, vehicle_id uuid fk, org_id uuid fk,
          recorded_at timestamptz, received_at timestamptz,
          geom geography(Point,4326), speed numeric null, heading numeric null,
          altitude numeric null, accuracy numeric null, attrs jsonb default '{}')
          -- indexes: GiST(geom), btree(vehicle_id, recorded_at desc), btree(org_id, recorded_at)

trips(id uuid pk, vehicle_id uuid fk, org_id uuid fk,                 -- was "route"
      started_at timestamptz, ended_at timestamptz null,
      distance_m numeric default 0, duration_s int default 0,
      status text check in (active,closed), path geography(LineString) null)

geofences(id uuid pk, org_id uuid fk, name text, area geography(Polygon))   -- NEW

events(id uuid pk, org_id uuid fk, vehicle_id uuid fk null,           -- NEW
       type text,  -- geofence_enter | geofence_exit | speeding | offline
       payload jsonb, created_at timestamptz)

-- reference: vehicle_makes(id, name, country_id), countries(id, code, name_en, name_ru, flag_url)
```

Notes:
- "Connected/disconnected" derived from `devices.last_seen_at` (or latest position), not a stored flag.
- Trip segmentation: a configurable idle gap (e.g. no movement > N minutes) closes the active trip and
  opens a new one. Explicit device start/end markers, if present, are honored too.
- Optional upgrade (M3+): make `positions` a TimescaleDB hypertable. Not required for the first cut.

## 5. API (REST/JSON, cookie sessions, OpenAPI)

Tenant isolation enforced by middleware on every authed request (scoped to caller's `org_id`).
RBAC: `owner/admin` manage; `operator` day-to-day; `viewer` read-only.

- **Auth (people):** `register`, `verify-email`, `login`, `logout`, `me`, `password/forgot|reset`,
  CSRF token issuance. httpOnly + SameSite cookie.
- **Orgs:** create org, get, `members` CRUD (staff invites), role changes.
- **Vehicles:** CRUD + photo upload (MinIO, presigned or proxied).
- **Devices:** CRUD, issue/rotate token (shown once), assign to vehicle.
- **Trips / History:** list, list by vehicle, get trip with path (GeoJSON), history query by
  vehicle + time range, latest-snapshot across all vehicles (initial map state).
- **Geofences / Events:** geofence CRUD, events feed.
- **Live:** `GET /ws` — WebSocket; subscribe to the org's live positions/events.
- **Ingestion (devices, separate device-token auth):** `POST /ingest/positions` (single or batch JSON)
  and MQTT topic `org/{org}/device/{device}/position`.

API is documented with an OpenAPI spec; types shared with the frontend where practical.

## 6. Frontend (React)

- **Vite + React + TypeScript.**
- **Data:** TanStack Query (server state) + Zustand (UI / map state).
- **UI:** Tailwind + shadcn/ui.
- **Map:** MapLibre GL via `react-map-gl` — live markers updated over WebSocket, trip path as a
  GeoJSON layer, history playback, geofence drawing.
- **Forms:** react-hook-form + zod. **i18n:** i18next (RU/EN) — replaces both the DB-bilingual
  columns and the Blade `__()` approach.
- **Screens:** Landing → Auth (login/register/verify/reset) → Onboarding (create org) →
  Dashboard map (live) → Vehicles (list/detail/edit + photo) → Devices → Trips/History (playback) →
  Geofences → Events/Alerts → Members/Org settings → User settings.

## 7. Platform / DevOps (scoped showcase)

- **Monorepo:** `/backend` (Go), `/frontend` (React), `/deploy` (compose + observability config),
  `/legacy` (archived PHP, kept for reference), `/tools` (device simulator).
- **Docker:** multi-stage builds (Go → distroless/scratch, frontend → nginx static). `docker-compose`
  brings up the full stack: Postgres+PostGIS, Redis, Mosquitto (MQTT), MinIO, mailpit, the three Go
  services, the frontend, the observability stack, and the device simulator.
- **Observability:** OpenTelemetry in Go (traces + metrics + logs via `slog`); OTel Collector →
  Prometheus (metrics), Tempo (traces), Loki (logs); prebuilt Grafana dashboards; health/readiness.
- **CI (GitHub Actions):** golangci-lint + eslint, `go test` (testcontainers-go) + vitest,
  `sqlc generate` / migration drift checks, image builds, trivy scan.
- **Migrations:** goose. **Device simulator:** a Go tool emulating trackers over HTTP/MQTT — removes
  the "no hardware" problem, feeds the live map for demos, and exercises the ingestion path.
- **DX:** Taskfile/Makefile; `task up` boots everything.

## 8. Milestones (each is its own spec → plan → implementation cycle)

This design is the overall vision. Only **M0** goes into the detailed spec → plan now.

| Milestone | Scope |
|---|---|
| **M0 — Foundation** | monorepo, Go skeleton (clean arch, chi, config, slog + OTel), Postgres+PostGIS + sqlc + goose, docker-compose (pg/redis/mqtt/minio/observability), CI, health, React+Vite+TS+Tailwind skeleton, one end-to-end endpoint with observability green |
| **M1 — Identity & tenancy** | users, orgs, memberships/RBAC, cookie sessions + CSRF, email verification (mailpit), password reset, React auth + onboarding |
| **M2 — Fleet** | vehicles CRUD + photos (MinIO), devices CRUD + tokens, reference data, screens |
| **M3 — Ingestion & pipeline** | device HTTP + MQTT, per-device auth, Redis Streams, worker → positions (PostGIS), auto trip segmentation, metrics, simulator |
| **M4 — Live & history** | WebSocket live map (MapLibre), snapshot, history + playback |
| **M5 — Geofences & events** | geofence CRUD + drawing, enter/exit + speeding/offline, events feed, notifications |
| **M6 — Polish** | Grafana dashboards, demo seed, e2e (Playwright), docs/README |

## 9. Out of scope (YAGNI for now)
- Kubernetes / Helm / Terraform and cloud deployment (local-only by decision).
- Raw TCP hardware tracker protocols (GT06/TK103) — HTTP+MQTT only for the first cut; a TCP decoder
  could be a later milestone.
- Billing, advanced reporting/BI, mobile apps.
- TimescaleDB (optional upgrade, not required initially).
