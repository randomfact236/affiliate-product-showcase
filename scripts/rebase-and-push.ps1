<#
.SYNOPSIS
  Rebase current branch onto origin/<branch> and push the result.

.DESCRIPTION
  This helper fetches origin, shows local/remote divergence, optionally asks
  for confirmation, runs `git rebase --autostash origin/<branch>`, and pushes
  the rebased branch to `origin`. If conflicts occur, it prints guidance.

.USAGE
  In PowerShell (from repository root):
    .\scripts\rebase-and-push.ps1
  To run non-interactively (auto-confirm):
    .\scripts\rebase-and-push.ps1 -Auto
#>
 
param(
  [switch]$Auto
)

Set-StrictMode -Version Latest

try {
  $cwd = Get-Location
} catch {
  Write-Error "Unable to determine current directory"
  exit 1
}

Write-Host "Repository: $cwd"

if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
  Write-Error "git not found in PATH. Install Git or run this from an environment with git available."
  exit 1
}

$branch = git rev-parse --abbrev-ref HEAD 2>$null
if ($LASTEXITCODE -ne 0 -or -not $branch) {
  Write-Error "Failed to determine current branch"
  exit 1
}

Write-Host "Current branch: $branch"

Write-Host "Fetching origin..."
git fetch origin --prune
if ($LASTEXITCODE -ne 0) {
  Write-Error "git fetch failed"
  exit 1
}

Write-Host "--- Local-only commits (origin/$branch..$branch) ---"
git --no-pager log --oneline --decorate origin/$branch..$branch -n 10

Write-Host "--- Remote-only commits ($branch..origin/$branch) ---"
git --no-pager log --oneline --decorate $branch..origin/$branch -n 10

if (-not $Auto) {
  $answer = Read-Host "Rebase local '$branch' onto 'origin/$branch'? Type 'y' to proceed"
  if ($answer -notmatch '^(y|Y|yes|YES)$') {
    Write-Host "Aborted by user."
    exit 0
  }
}

Write-Host "Starting rebase (with autostash)..."
# Use autostash to preserve any uncommitted work
git rebase --autostash origin/$branch
if ($LASTEXITCODE -ne 0) {
  Write-Error "Rebase failed or conflicts detected."
  Write-Host "Run 'git status' to inspect conflicts, resolve them, then run:"
  Write-Host "  git add <resolved-files>"
  Write-Host "  git rebase --continue"
  Write-Host "If you want to abort the rebase and restore previous state, run:"
  Write-Host "  git rebase --abort"
  git status
  exit 1
}

Write-Host "Rebase completed successfully. Pushing to origin/$branch..."
git push origin $branch
if ($LASTEXITCODE -ne 0) {
  Write-Error "Push failed. If this is expected because remote changed again, try pulling and rebasing again."
  Write-Host "If you understand the implications and really want to overwrite remote, you can run:"
  Write-Host "  git push --force-with-lease origin $branch"
  exit 1
}

Write-Host "Rebase and push completed successfully."
exit 0
