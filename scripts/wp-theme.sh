#!/usr/bin/env bash
set -euo pipefail

usage(){
  echo "Usage: $0 install|activate <theme-slug>"
  exit 1
}

cmd=${1:-}
slug=${2:-}

if [[ -z "$cmd" || -z "$slug" ]]; then
  usage
fi

if ! command -v wp >/dev/null 2>&1; then
  echo "wp CLI not found; please install WP-CLI or run this inside the container." >&2
  exit 1
fi

case "$cmd" in
  install)
    if wp theme is-installed "$slug" >/dev/null 2>&1; then
      echo "Theme '$slug' already installed. Ensuring active..."
      wp theme activate "$slug" >/dev/null 2>&1 || wp theme activate "$slug"
    else
      echo "Installing and activating theme '$slug'..."
      wp theme install "$slug" --activate
    fi
    ;;
  activate)
    if ! wp theme is-installed "$slug" >/dev/null 2>&1; then
      echo "Theme '$slug' is not installed. Install first: $0 install $slug" >&2
      exit 2
    fi
    if wp theme status "$slug" | grep -q "Active"; then
      echo "Theme '$slug' already active"
    else
      wp theme activate "$slug"
    fi
    ;;
  *)
    usage
    ;;
esac

exit 0
