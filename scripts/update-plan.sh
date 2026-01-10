#!/bin/sh
set -e
# Regenerate plan files from plan_source.md and commit them using the generator
ROOT=$(dirname "$0")/..
cd "$ROOT"

if ! command -v node >/dev/null 2>&1; then
  echo "ERROR: node is required to run the plan generator." >&2
  exit 2
fi

echo "Regenerating plan files (single source of truth)..."
node plan/manage-plan.js regenerate

echo "Done. Review staged changes and commit when ready."

exit 0
