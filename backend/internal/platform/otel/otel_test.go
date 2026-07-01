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
