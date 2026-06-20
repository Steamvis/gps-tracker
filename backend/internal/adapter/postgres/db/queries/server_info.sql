-- name: ServerInfo :one
SELECT now()::timestamptz AS now, postgis_version() AS postgis_version;
