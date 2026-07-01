# syntax=docker/dockerfile:1

# ---- Stage 1: build a static, CGO-free binary ----
# Run the toolchain natively on the build host (BUILDPLATFORM) and cross-compile
# to the requested target, so the same Dockerfile yields a correct binary on an
# arm64 workstation and an amd64 CI runner alike. Pinned to a patched Go release
# (Go 1.22 is EOL; its stdlib carries unfixable CVEs flagged by the image scan).
FROM --platform=$BUILDPLATFORM golang:1.26.4 AS build
WORKDIR /src

# Cache module downloads.
COPY go.mod go.sum ./
RUN go mod download

# Build.
COPY . .
ARG VERSION=dev
# TARGETOS/TARGETARCH are provided by BuildKit; default to linux/native when the
# image is built without an explicit --platform.
ARG TARGETOS=linux TARGETARCH
ENV CGO_ENABLED=0 GOOS=${TARGETOS} GOARCH=${TARGETARCH}
RUN go build -trimpath -ldflags="-s -w" -o /out/api ./cmd/api

# ---- Stage 2: minimal distroless runtime ----
FROM gcr.io/distroless/static:nonroot
WORKDIR /app
COPY --from=build /out/api /app/api
USER nonroot:nonroot
EXPOSE 8080
ENTRYPOINT ["/app/api"]
HEALTHCHECK --interval=10s --timeout=5s --start-period=20s --retries=3 CMD ["/app/api", "-health"]
