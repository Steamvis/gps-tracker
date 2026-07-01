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
