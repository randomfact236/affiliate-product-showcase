# Install git hooks from .githooks to .git/hooks
$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$source = Join-Path $repoRoot ".githooks"
$dest = Join-Path $repoRoot ".git\hooks"
if (!(Test-Path $dest)) {
    Write-Error ".git/hooks not found. Are you running this from the repo root?"
    exit 1
}
Get-ChildItem -Path $source -File | ForEach-Object {
    $target = Join-Path $dest $_.Name
    Copy-Item -Path $_.FullName -Destination $target -Force
    icacls $target /grant "$(whoami):(RX)" | Out-Null
}
Write-Output "Installed git hooks from .githooks to .git/hooks"
Write-Output "Hooks enforce plan single-source-of-truth (see plan/PLAN_WORKFLOW.md)."
Write-Output "Use scripts\update-plan.ps1 (or scripts\update-plan.sh) to regenerate and stage plan files."
