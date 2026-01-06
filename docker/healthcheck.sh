#!/usr/bin/env sh
# Robust healthcheck helper used by CI to wait for HTTP service readiness.
# Retries until the service returns HTTP 200 or the attempt limit is reached.

set -eu

URL=${1:-http://localhost:8000}
MAX_ATTEMPTS=${MAX_ATTEMPTS:-60}
SLEEP=${SLEEP:-2}

echo "healthcheck: verifying $URL (max attempts: $MAX_ATTEMPTS, sleep: ${SLEEP}s)"

attempt=1
while [ "$attempt" -le "$MAX_ATTEMPTS" ]; do
  if command -v curl >/dev/null 2>&1; then
    code=$(curl -sS -o /dev/null -w "%{http_code}" "$URL" || echo "000")
  elif command -v wget >/dev/null 2>&1; then
    # wget --spider prints headers to stderr; extract HTTP code if present
    code=$(wget --spider "$URL" 2>&1 | awk '/^  HTTP/{print $2; exit}' || echo "000")
  else
    echo "no http client (curl/wget) available." >&2
    exit 2
  fi

  echo "attempt $attempt: http $code"

  if [ "$code" = "200" ]; then
    echo "ok"
    exit 0
  fi

  attempt=$((attempt + 1))
  sleep "$SLEEP"
done

echo "service did not return HTTP 200 after $MAX_ATTEMPTS attempts" >&2
exit 1
