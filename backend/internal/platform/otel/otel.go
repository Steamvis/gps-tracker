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

	// Traces — retry disabled so the batcher goroutine exits quickly when no
	// collector is reachable, preventing tp.Shutdown from blocking until the
	// context deadline.
	traceExp, err := otlptracegrpc.New(ctx,
		otlptracegrpc.WithEndpoint(cfg.OTLPEndpoint),
		otlptracegrpc.WithInsecure(),
		otlptracegrpc.WithRetry(otlptracegrpc.RetryConfig{Enabled: false}),
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

	// Metrics — retry disabled for the same reason; the periodic reader's
	// flush on shutdown fails fast rather than retrying until context deadline.
	metricExp, err := otlpmetricgrpc.New(ctx,
		otlpmetricgrpc.WithEndpoint(cfg.OTLPEndpoint),
		otlpmetricgrpc.WithInsecure(),
		otlpmetricgrpc.WithRetry(otlpmetricgrpc.RetryConfig{Enabled: false}),
	)
	if err != nil {
		return handleErr(err)
	}
	mp := sdkmetric.NewMeterProvider(
		sdkmetric.WithResource(res),
		sdkmetric.WithReader(sdkmetric.NewPeriodicReader(metricExp)),
	)
	shutdownFuncs = append(shutdownFuncs, func(ctx context.Context) error {
		if err := mp.Shutdown(ctx); err != nil {
			otel.Handle(err) // best-effort: log but do not propagate
		}
		return nil
	})
	otel.SetMeterProvider(mp)

	// Logs — retry disabled; best-effort shutdown wrapper consistent with
	// the metric provider above.
	logExp, err := otlploggrpc.New(ctx,
		otlploggrpc.WithEndpoint(cfg.OTLPEndpoint),
		otlploggrpc.WithInsecure(),
		otlploggrpc.WithRetry(otlploggrpc.RetryConfig{Enabled: false}),
	)
	if err != nil {
		return handleErr(err)
	}
	lp := sdklog.NewLoggerProvider(
		sdklog.WithResource(res),
		sdklog.WithProcessor(sdklog.NewBatchProcessor(logExp)),
	)
	shutdownFuncs = append(shutdownFuncs, func(ctx context.Context) error {
		if err := lp.Shutdown(ctx); err != nil {
			otel.Handle(err) // best-effort: log but do not propagate
		}
		return nil
	})
	global.SetLoggerProvider(lp)

	return shutdown, nil
}
