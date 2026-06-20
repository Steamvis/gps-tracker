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
