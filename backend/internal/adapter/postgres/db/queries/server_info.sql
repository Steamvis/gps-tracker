-- name: ServerInfo :one
SELECT now()::timestamptz AS now, postgis_version()::text AS postgis_version;
