package log

import (
	"context"
	"errors"
	"log/slog"
	"testing"
)

// recordingHandler counts how many records it received and can be made to fail.
type recordingHandler struct {
	got     int
	failErr error
}

func (h *recordingHandler) Enabled(context.Context, slog.Level) bool { return true }
func (h *recordingHandler) Handle(context.Context, slog.Record) error {
	h.got++
	return h.failErr
}
func (h *recordingHandler) WithAttrs([]slog.Attr) slog.Handler { return h }
func (h *recordingHandler) WithGroup(string) slog.Handler      { return h }

// A failing handler must not stop the fan-out from reaching the others, and its
// error must propagate so the failure is observable rather than swallowed.
func TestFanoutContinuesPastFailingHandler(t *testing.T) {
	boom := errors.New("boom")
	failing := &recordingHandler{failErr: boom}
	healthy := &recordingHandler{}

	f := newFanout(failing, healthy)
	err := f.Handle(context.Background(), slog.Record{Level: slog.LevelInfo})

	if healthy.got != 1 {
		t.Fatalf("healthy handler should still receive the record, got %d deliveries", healthy.got)
	}
	if !errors.Is(err, boom) {
		t.Fatalf("expected the failing handler's error to propagate, got %v", err)
	}
}

// With every handler healthy, Handle returns nil.
func TestFanoutReturnsNilWhenAllSucceed(t *testing.T) {
	a, b := &recordingHandler{}, &recordingHandler{}
	f := newFanout(a, b)
	if err := f.Handle(context.Background(), slog.Record{Level: slog.LevelInfo}); err != nil {
		t.Fatalf("expected nil error when all handlers succeed, got %v", err)
	}
	if a.got != 1 || b.got != 1 {
		t.Fatalf("each handler should receive exactly one record, got a=%d b=%d", a.got, b.got)
	}
}
