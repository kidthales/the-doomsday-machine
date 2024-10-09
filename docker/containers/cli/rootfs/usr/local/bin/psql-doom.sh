#!/bin/sh
set -e

PSQL_DOOM_USER="${1:-${POSTGRES_READER_USER:-reader}}"
PSQL_DOOM_PASS=''

case "$PSQL_DOOM_USER" in
	"${POSTGRES_READER_USER:-reader}")
		PSQL_DOOM_PASS="${POSTGRES_READER_PASSWORD:-reader}"
		;;
	"${POSTGRES_WRITER_USER:-writer}")
		PSQL_DOOM_PASS="${POSTGRES_WRITER_PASSWORD:-writer}"
		;;
	"${POSTGRES_MIGRATOR_USER:-migrator}")
		PSQL_DOOM_PASS="${POSTGRES_MIGRATOR_PASSWORD:-migrator}"
		;;
	*)
		printf 'Invalid user: %s\n' "$PSQL_DOOM_USER" >&2
		exit 1
		;;
esac

psql "postgresql://$PSQL_DOOM_USER:$PSQL_DOOM_PASS@${POSTGRES_HOST:-db}:${POSTGRES_PORT:-5432}/${POSTGRES_DB:-app}?serverVersion=${POSTGRES_VERSION:-17}&charset=${POSTGRES_CHARSET:-utf8}"
