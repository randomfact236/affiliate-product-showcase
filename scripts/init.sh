#!/usr/bin/env bash
set -euo pipefail

echo "Running project init helper..."

if ! command -v wp >/dev/null 2>&1; then
  echo "wp CLI not found; please run inside the WP container or install WP-CLI." >&2
  exit 1
fi

if wp core is-installed >/dev/null 2>&1; then
  echo "WordPress already installed. Skipping core install."
else
  echo "WordPress not installed. Please run 'wp core install' manually or set WP environment variables for automatic install." >&2
fi

# Install and activate plugins passed as arguments prefixed with 'plugin:'
# Example: ./init.sh plugin:akismet plugin:wordfence theme:twentytwentyone

for arg in "$@"; do
  case "$arg" in
    plugin:*)
      slug=${arg#plugin:}
      ./scripts/wp-plugin.sh install "$slug"
      ;;
    theme:*)
      slug=${arg#theme:}
      ./scripts/wp-theme.sh install "$slug"
      ;;
    *)
      echo "Unknown init argument: $arg" >&2
      ;;
  esac
done

echo "Init complete."
