#!/usr/bin/env pwsh
# Quick Web Server Start with Auto-Fix

param(
    [switch]$SkipBrowser
)

$ErrorActionPreference = "Continue"

function Log($msg, $color = "White") {
    Write-Host "[$(Get-Date -Format 'HH:mm:ss')] $msg" -ForegroundColor $color
}

Log "========================================" "Cyan"
Log "  Quick Web Server Starter" "Cyan"
Log "========================================" "Cyan"

# 1. Check/install dependencies
Log "Checking dependencies..." "Yellow"
if (-not (Test-Path "apps/web/node_modules")) {
    Log "Installing web dependencies..." "Yellow"
    cd "apps/web"
    npm install --legacy-peer-deps
    cd "../.."
}

# Check for react-query
$pkg = Get-Content "apps/web/package.json" | ConvertFrom-Json
if ($pkg.dependencies.'@tanstack/react-query' -eq $null) {
    Log "Installing @tanstack/react-query..." "Yellow"
    cd "apps/web"
    npm install @tanstack/react-query --save --legacy-peer-deps
    cd "../.."
}

# 2. Kill existing processes on port 3000
Log "Checking port 3000..." "Yellow"
Get-NetTCPConnection -LocalPort 3000 -ErrorAction SilentlyContinue | ForEach-Object {
    Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue
    Log "Stopped process on port 3000" "Green"
}

# 3. Start web server in new window
Log "Starting web server..." "Green"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'apps/web'; npm run dev" -WindowStyle Normal

# 4. Wait for server
Log "Waiting for server (max 30s)..." "Yellow"
for ($i = 1; $i -le 30; $i++) {
    Start-Sleep -Seconds 1
    try {
        $resp = Invoke-WebRequest "http://localhost:3000" -TimeoutSec 2 -ErrorAction Stop
        Log "Server is up! (HTTP $($resp.StatusCode))" "Green"
        
        # Open browser
        if (-not $SkipBrowser) {
            Log "Opening browser..." "Green"
            Start-Process "http://localhost:3000"
        }
        
        Log "========================================" "Green"
        Log "  ✅ Web server ready!" "Green"
        Log "  http://localhost:3000" "Green"
        Log "========================================" "Green"
        exit 0
    } catch {
        Write-Host -NoNewline "."
    }
}

Log ""
Log "Server didn't respond in 30s. Check the window for errors." "Red"
exit 1
