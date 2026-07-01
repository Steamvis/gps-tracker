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
