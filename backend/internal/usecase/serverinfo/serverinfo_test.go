package serverinfo

import (
	"context"
	"errors"
	"testing"
	"time"
)

type fakeRepo struct {
	info Info
	err  error
}

func (f fakeRepo) ServerInfo(ctx context.Context) (Info, error) {
	return f.info, f.err
}

func TestServiceGetReturnsRepoInfo(t *testing.T) {
	want := Info{Time: time.Date(2026, 6, 20, 12, 0, 0, 0, time.UTC), PostGIS: "3.4 USE_GEOS=1"}
	svc := New(fakeRepo{info: want})

	got, err := svc.Get(context.Background())
	if err != nil {
		t.Fatalf("Get returned error: %v", err)
	}
	if !got.Time.Equal(want.Time) {
		t.Errorf("Time = %v, want %v", got.Time, want.Time)
	}
	if got.PostGIS != want.PostGIS {
		t.Errorf("PostGIS = %q, want %q", got.PostGIS, want.PostGIS)
	}
}

func TestServiceGetPropagatesError(t *testing.T) {
	wantErr := errors.New("db down")
	svc := New(fakeRepo{err: wantErr})

	_, err := svc.Get(context.Background())
	if !errors.Is(err, wantErr) {
		t.Fatalf("Get error = %v, want %v", err, wantErr)
	}
}
