#!/usr/bin/env bash
set -euo pipefail

# Usage: scripts/db-backup.sh [output-file]
# Falls back to mysqldump; if wp-cli is available it will be used to detect DB settings.

BACKUP_DIR=${BACKUP_DIR:-"$(pwd)/backups"}
DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}
DB_USER=${DB_USER:-wp}
DB_PASS=${DB_PASS:-wp}
DB_NAME=${DB_NAME:-wordpress}

mkdir -p "$BACKUP_DIR"

timestamp=$(date +%Y%m%d_%H%M%S)
default_file="$BACKUP_DIR/${DB_NAME}_$timestamp.sql.gz"
out=${1:-$default_file}

echo "Backing up database '$DB_NAME' to $out"

if command -v wp >/dev/null 2>&1; then
  # Try WP-CLI to read DB constants if in WP root
  if wp db export - --add-drop-table --path=. >/dev/null 2>&1; then
    wp db export - --add-drop-table --quiet --path=. | gzip > "$out"
    echo "Backup written using WP-CLI"
    exit 0
  fi
fi

# Fallback to mysqldump
if ! command -v mysqldump >/dev/null 2>&1; then
  echo "Error: neither wp nor mysqldump available" >&2
  exit 2
fi

mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" --single-transaction --routines --triggers --events --add-drop-table "$DB_NAME" | gzip > "$out"
echo "Backup written using mysqldump"
