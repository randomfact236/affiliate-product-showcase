Param()
Set-StrictMode -Version Latest
$Root = Join-Path $PSScriptRoot '..'
Set-Location $Root

if (-not (Get-Command node -ErrorAction SilentlyContinue)) {
    Write-Error "node is required to run the plan generator."
    exit 2
}

Write-Host "Regenerating plan files..."
node plan/plan_sync_todos.cjs

& git add plan/plan_sync.md plan/plan_sync_todo.md plan/plan_todos.json plan/plan_state.json | Out-Null

$env:PLAN_GENERATOR = '1'
try {
    git commit -m "[plan-generator] regenerate plan files from plan/plan_source.md" | Out-Null
    Write-Host "Committed regenerated plan files."
} catch {
    Write-Host "No changes to commit."
}

exit 0
