#!/bin/sh
# Install git hooks from .githooks to .git/hooks (Unix)
REPO_ROOT=$(dirname "$0")/..
SOURCE="$REPO_ROOT/.githooks"
DEST="$REPO_ROOT/.git/hooks"
if [ ! -d "$DEST" ]; then
  echo ".git/hooks not found. Are you running this from the repo root?"
  exit 1
fi
for f in "$SOURCE"/*; do
  cp "$f" "$DEST/$(basename $f)"
  chmod +x "$DEST/$(basename $f)"
done
echo "Installed git hooks from .githooks to .git/hooks"
echo "Note: Hooks will reject manual edits to plan/ unless you set PLAN_GENERATOR=1 when committing."
echo "Use scripts/update-plan.sh (or scripts/update-plan.ps1) to regenerate and commit plan files."
