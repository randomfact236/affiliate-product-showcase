#!/usr/bin/env bash
set -euo pipefail

# Usage: scripts/db-restore.sh [backup-file]
# If no backup-file provided, restores the newest file in backups/ directory.

BACKUP_DIR=${BACKUP_DIR:-"$(pwd)/backups"}
DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}
DB_USER=${DB_USER:-wp}
DB_PASS=${DB_PASS:-wp}
DB_NAME=${DB_NAME:-wordpress}

file=${1:-}
if [ -z "$file" ]; then
  file=$(ls -1t "$BACKUP_DIR"/*.sql.gz 2>/dev/null | head -n1 || true)
fi

if [ -z "$file" ] || [ ! -f "$file" ]; then
  echo "No backup file found to restore: $file" >&2
  exit 2
fi

echo "Restoring database '$DB_NAME' from $file"

if ! command -v gunzip >/dev/null 2>&1; then
  echo "Error: gunzip (or gzip) is required to restore" >&2
  exit 2
fi

if ! command -v mysql >/dev/null 2>&1; then
  echo "Error: mysql client not available" >&2
  exit 2
fi

gunzip -c "$file" | mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME"
echo "Restore complete"

if command -v wp >/dev/null 2>&1; then
  if wp cache flush --path=. >/dev/null 2>&1; then
    echo "WP cache flushed"
  fi
fi
