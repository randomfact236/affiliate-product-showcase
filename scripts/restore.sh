#!/usr/bin/env bash
set -euo pipefail

# Simple restore script for local development (POSIX)
# Usage: scripts/restore.sh backups/<ts>/db.sql backups/<ts>/wp-content.tar.gz

DB_SQL=${1:-}
WP_TAR=${2:-}

if [ -z "$DB_SQL" ] || [ -z "$WP_TAR" ]; then
  echo "Usage: $0 <db-sql-file> <wp-content-tar>" >&2
  exit 2
fi

if [ -f .env ]; then
  set -a; . .env; set +a
fi

DB_NAME="${MYSQL_DATABASE:-${WORDPRESS_DB_NAME:-wordpress}}"
DB_USER="${MYSQL_USER:-${WORDPRESS_DB_USER:-root}}"
DB_PASS="${MYSQL_PASSWORD:-${WORDPRESS_DB_PASSWORD:-}}"

echo "Restoring database $DB_NAME from $DB_SQL"
cat "$DB_SQL" | docker exec -i aps_db sh -c "mysql -u\"$DB_USER\" -p\"$DB_PASS\" \"$DB_NAME\""

echo "Restoring wp-content from $WP_TAR"
tar -C . -xzf "$WP_TAR"

echo "Restore completed"
