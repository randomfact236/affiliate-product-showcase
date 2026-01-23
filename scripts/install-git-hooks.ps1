# PowerShell script to install Git hooks for auto-push prevention
# This script sets up the pre-push hook to require explicit confirmation

Write-Host "========================================" -ForegroundColor Red
Write-Host "  Git Hook Installation Script" -ForegroundColor Red
Write-Host "========================================" -ForegroundColor Red
Write-Host ""

# Get the script directory
$ScriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptPath
$HooksDir = Join-Path $ProjectRoot ".git\hooks"

Write-Host "Project Root: $ProjectRoot" -ForegroundColor Cyan
Write-Host "Hooks Directory: $HooksDir" -ForegroundColor Cyan
Write-Host ""

# Create hooks directory if it doesn't exist
if (-not (Test-Path $HooksDir)) {
    Write-Host "Creating hooks directory..." -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $HooksDir -Force | Out-Null
    Write-Host "✓ Hooks directory created" -ForegroundColor Green
} else {
    Write-Host "✓ Hooks directory exists" -ForegroundColor Green
}

# Copy the pre-push hook
$SourceHook = Join-Path $ProjectRoot ".git\hooks\pre-push"
$DestHook = Join-Path $HooksDir "pre-push"

if (Test-Path $SourceHook) {
    Write-Host "Installing pre-push hook..." -ForegroundColor Yellow
    Copy-Item -Path $SourceHook -Destination $DestHook -Force
    
    # On Windows with Git for Windows, the file should be executable
    # Git for Windows handles file permissions automatically
    Write-Host "✓ Pre-push hook installed" -ForegroundColor Green
} else {
    Write-Host "✗ Source hook file not found: $SourceHook" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  Installation Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "The pre-push hook has been installed successfully." -ForegroundColor Cyan
Write-Host ""
Write-Host "What this means:" -ForegroundColor Yellow
Write-Host "  • Every 'git push' will require explicit confirmation" -ForegroundColor White
Write-Host "  • This prevents accidental pushes to remote" -ForegroundColor White
Write-Host "  • You can approve or cancel each push attempt" -ForegroundColor White
Write-Host ""
Write-Host "To test the hook, try:" -ForegroundColor Yellow
Write-Host "  git push" -ForegroundColor Cyan
Write-Host ""
Write-Host "You will be prompted to approve the push." -ForegroundColor Yellow
Write-Host ""
