#!/usr/bin/env bash
set -euo pipefail

# Usage: scripts/db-seed.sh
# Runs test DB seeder; idempotent where possible.

DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}
DB_USER=${DB_USER:-wp}
DB_PASS=${DB_PASS:-wp}
DB_NAME=${DB_NAME:-wordpress}

echo "Seeding database '$DB_NAME'"

# Prefer existing PHP seeder
if [ -f "tests/db-seed.php" ]; then
  php tests/db-seed.php && echo "Seeder script executed" && exit 0
fi

# Else attempt with WP-CLI option add (idempotent)
if command -v wp >/dev/null 2>&1; then
  if wp option get aps_test_seed --path=. >/dev/null 2>&1; then
    echo "aps_test_seed already present via WP-CLI"
    exit 0
  fi
  wp option add aps_test_seed 1 --skip-plugins --skip-themes --path=. || true
  echo "Seeded via WP-CLI"
  exit 0
fi

# Fallback to direct MySQL insert (idempotent via INSERT IGNORE)
if command -v mysql >/dev/null 2>&1; then
  sql="INSERT IGNORE INTO wp_options (option_name, option_value, autoload) VALUES ('aps_test_seed', '1', 'no');"
  mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$sql"
  echo "Seeded via mysql client"
  exit 0
fi

echo "No method available to seed the DB (php, wp, or mysql required)" >&2
exit 2
