#!/usr/bin/env bash
set -euo pipefail

usage(){
  echo "Usage: $0 install|activate <plugin-slug>"
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
    if wp plugin is-installed "$slug" >/dev/null 2>&1; then
      echo "Plugin '$slug' already installed. Ensuring active..."
      wp plugin activate "$slug" >/dev/null 2>&1 || wp plugin activate "$slug"
    else
      echo "Installing and activating plugin '$slug'..."
      wp plugin install "$slug" --activate
    fi
    ;;
  activate)
    if ! wp plugin is-installed "$slug" >/dev/null 2>&1; then
      echo "Plugin '$slug' is not installed. Install first: $0 install $slug" >&2
      exit 2
    fi
    if wp plugin is-active "$slug" >/dev/null 2>&1; then
      echo "Plugin '$slug' already active"
    else
      wp plugin activate "$slug"
    fi
    ;;
  *)
    usage
    ;;
esac

exit 0
