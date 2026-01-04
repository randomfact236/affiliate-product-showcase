#!/usr/bin/env sh
set -e
URL=${1:-http://localhost:8000}
if command -v curl >/dev/null 2>&1; then
  curl -fsS "$URL" >/dev/null || exit 1
elif command -v wget >/dev/null 2>&1; then
  wget -qO- "$URL" >/dev/null || exit 1
else
  echo "no http client (curl/wget) available." >&2
  exit 2
fi
echo "ok"
#!/usr/bin/env sh
# Simple healthcheck helper for containers (called from host if needed)
set -e

URL=${1:-http://localhost:8080}

if command -v curl >/dev/null 2>&1; then
  curl -fsS "$URL" >/dev/null || exit 1
elif command -v wget >/dev/null 2>&1; then
  wget -qO- "$URL" >/dev/null || exit 1
else
  echo "no http client (curl/wget) available." >&2
  exit 2
fi

echo "ok"
