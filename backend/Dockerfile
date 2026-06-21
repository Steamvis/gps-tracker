# syntax=docker/dockerfile:1

# ---- Stage 1: build a static, CGO-free binary ----
FROM golang:1.22 AS build
WORKDIR /src

# Cache module downloads.
COPY go.mod go.sum ./
RUN go mod download

# Build.
COPY . .
ARG VERSION=dev
ENV CGO_ENABLED=0 GOOS=linux
RUN go build -trimpath -ldflags="-s -w" -o /out/api ./cmd/api

# ---- Stage 2: minimal distroless runtime ----
FROM gcr.io/distroless/static:nonroot
WORKDIR /app
COPY --from=build /out/api /app/api
USER nonroot:nonroot
EXPOSE 8080
ENTRYPOINT ["/app/api"]
HEALTHCHECK --interval=10s --timeout=5s --start-period=20s --retries=3 CMD ["/app/api", "-health"]
