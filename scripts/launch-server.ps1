#!/usr/bin/env pwsh
# Direct Server Launcher - Simple and Reliable

param([switch]$OpenBrowser)

$ErrorActionPreference = "Continue"

Write-Host @"
========================================
  DIRECT WEB SERVER LAUNCHER
========================================
"@ -ForegroundColor Cyan

# Step 1: Kill any existing node processes
Write-Host "[1/4] Stopping any existing processes..." -ForegroundColor Yellow
Get-Process node -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

# Step 2: Verify dependencies
Write-Host "[2/4] Checking dependencies..." -ForegroundColor Yellow
if (-not (Test-Path "apps/web/node_modules")) {
    Write-Host "Installing dependencies..." -ForegroundColor Yellow
    cd "apps/web"
    npm install --legacy-peer-deps
    cd "../.."
}

# Step 3: Start the dev server (foreground so we see errors)
Write-Host "[3/4] Starting web server..." -ForegroundColor Green
Write-Host ""
Write-Host "The server will start below. Look for 'Ready in' message." -ForegroundColor Cyan
Write-Host "Press Ctrl+C to stop the server." -ForegroundColor Yellow
Write-Host ""
Write-Host "========================================" -ForegroundColor Green

if ($OpenBrowser) {
    # Start browser in background after delay
    Start-Job -ScriptBlock {
        Start-Sleep -Seconds 15
        Start-Process "http://localhost:3000"
    } | Out-Null
}

# Run the dev server directly
cd "apps/web"
npm run dev
