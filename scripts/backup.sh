#!/usr/bin/env bash
set -euo pipefail

# Simple backup script for local development (POSIX / Linux / macOS)
# Requires: docker, docker-compose, a local .env with MYSQL_* vars (not committed)

OUT_DIR="backups/$(date -u +%Y%m%dT%H%M%SZ)"
mkdir -p "$OUT_DIR"

# Load .env if present (non-invasive)
if [ -f .env ]; then
  set -a; . .env; set +a
fi

DB_NAME="${MYSQL_DATABASE:-${WORDPRESS_DB_NAME:-wordpress}}"
DB_USER="${MYSQL_USER:-${WORDPRESS_DB_USER:-root}}"
DB_PASS="${MYSQL_PASSWORD:-${WORDPRESS_DB_PASSWORD:-}}"

echo "Dumping database $DB_NAME to $OUT_DIR/db.sql"
docker exec aps_db sh -c "exec mysqldump --single-transaction -u\"$DB_USER\" -p\"$DB_PASS\" \"$DB_NAME\"" > "$OUT_DIR/db.sql"

echo "Archiving wp-content to $OUT_DIR/wp-content.tar.gz"
tar -C . -czf "$OUT_DIR/wp-content.tar.gz" wp-content

echo "Backup completed: $OUT_DIR"
