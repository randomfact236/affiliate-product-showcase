#!/usr/bin/env bash
# Simple wrapper to run WP-CLI using the official `wordpress:cli` image
# Usage: ./scripts/wpcli.sh plugin list

set -euo pipefail

if [ "$#" -lt 1 ]; then
  echo "Usage: $0 <wp-cli-args>"
  exit 2
fi

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"

docker run --rm \
  --network container:aps_wordpress \
  -v "$ROOT_DIR:/var/www/html" \
  wordpress:cli wp "$@"
