#!/bin/sh
set -e
# Regenerate plan files from plan_source.md and commit them using the generator
ROOT=$(dirname "$0")/..
cd "$ROOT"

if ! command -v node >/dev/null 2>&1; then
  echo "ERROR: node is required to run the plan generator." >&2
  exit 2
fi

echo "Regenerating plan files..."
node plan/plan_sync_todos.cjs

git add plan/plan_sync.md plan/plan_sync_todo.md plan/plan_todos.json plan/plan_state.json || true

export PLAN_GENERATOR=1
if git commit -m "[plan-generator] regenerate plan files from plan/plan_source.md"; then
  echo "Committed regenerated plan files." 
else
  echo "No changes to commit." 
fi

exit 0
