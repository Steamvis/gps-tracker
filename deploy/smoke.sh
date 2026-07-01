#!/usr/bin/env bash
# deploy/smoke.sh — boot the stack, drive traffic, and assert the observability path is green.
set -euo pipefail

cd "$(dirname "$0")"

# A fresh clone / CI runner has no .env (it is gitignored); seed it from the committed
# example so `docker compose --env-file .env` works on a clean checkout.
[ -f .env ] || cp .env.example .env

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

echo "==> Asserting logs reached Loki"
retry "loki has gps-api logs" bash -c \
  'curl -s --get "http://localhost:3100/loki/api/v1/query_range" --data-urlencode "query={service_name=\"gps-api\"}" --data-urlencode "limit=1" | grep -q "\"values\""'

echo "==> Asserting Grafana is serving"
retry "grafana returns HTTP 200" bash -c \
  '[ "$(curl -s -o /dev/null -w "%{http_code}" localhost:3000)" = "200" ]'

echo "SMOKE PASS: observability stack is green"
