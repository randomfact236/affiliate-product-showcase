#!/usr/bin/env bash
set -euo pipefail

# Repo root
repo_root=$(git rev-parse --show-toplevel)
cd "$repo_root"

# Collect description from args
desc="$*"
if [ -z "$desc" ]; then
  echo "Usage: git backup <description>"
  exit 1
fi

timestamp=$(date +%Y-%m-%d-%H_%M_%S)
branch="backup-$timestamp"

echo "Staging all changes..."
git add -A

echo "Committing with message: Backup: $desc"
if git commit -m "Backup: $desc"; then
  echo "Commit created."
else
  echo "Nothing to commit (no changes). Exiting."
  exit 0
fi

echo "Creating branch $branch..."
git branch "$branch"

echo "Pushing $branch to origin..."
git push -u origin "$branch"

# Determine default branch (origin/HEAD -> origin/main or origin/master)
default_branch=""
default_branch=$(git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null | sed 's@^refs/remotes/origin/@@' || true)
if [ -z "$default_branch" ]; then
  if git show-ref --verify --quiet refs/heads/main; then
    default_branch=main
  elif git show-ref --verify --quiet refs/heads/master; then
    default_branch=master
  else
    default_branch=$(git rev-parse --abbrev-ref HEAD)
  fi
fi

echo "Checking out $default_branch..."
git checkout "$default_branch"

echo "Backup complete: pushed $branch and returned to $default_branch"
