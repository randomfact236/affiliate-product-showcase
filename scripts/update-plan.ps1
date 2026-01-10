Param()
Set-StrictMode -Version Latest
$Root = Join-Path $PSScriptRoot '..'
Set-Location $Root

if (-not (Get-Command node -ErrorAction SilentlyContinue)) {
    Write-Error "node is required to run the plan generator."
    exit 2
}

Write-Host "Regenerating plan files (single source of truth)..."
node plan/manage-plan.js regenerate

Write-Host "Done. Review staged changes and commit when ready."

exit 0
