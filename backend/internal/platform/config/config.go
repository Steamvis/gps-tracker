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
