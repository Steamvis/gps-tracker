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
