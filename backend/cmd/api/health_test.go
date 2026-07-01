package main

import (
	"net"
	"net/http"
	"net/http/httptest"
	"testing"
)

// listenerOn8080 forces an httptest server onto 127.0.0.1:8080 so that
// healthCheck()'s fixed URL (http://localhost:8080/healthz) reaches it.
func listenerOn8080(t *testing.T) net.Listener {
	t.Helper()
	ln, err := net.Listen("tcp", "127.0.0.1:8080")
	if err != nil {
		t.Skipf("cannot bind 127.0.0.1:8080 (already in use?): %v", err)
	}
	return ln
}

func TestHealthCheck_OK(t *testing.T) {
	srv := httptest.NewUnstartedServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/healthz" {
			http.NotFound(w, r)
			return
		}
		w.WriteHeader(http.StatusOK)
		_, _ = w.Write([]byte(`{"status":"ok"}`))
	}))
	srv.Listener.Close()
	srv.Listener = listenerOn8080(t)
	srv.Start()
	defer srv.Close()

	if err := healthCheck(); err != nil {
		t.Fatalf("healthCheck() = %v, want nil", err)
	}
}

func TestHealthCheck_Unhealthy(t *testing.T) {
	srv := httptest.NewUnstartedServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusInternalServerError)
	}))
	srv.Listener.Close()
	srv.Listener = listenerOn8080(t)
	srv.Start()
	defer srv.Close()

	if err := healthCheck(); err == nil {
		t.Fatal("healthCheck() = nil, want error on HTTP 500")
	}
}
