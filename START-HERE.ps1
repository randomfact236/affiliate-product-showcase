#!/usr/bin/env pwsh
# START HERE - Automatic Web Server Launcher
# This script will fix any issues and start the web server automatically

param([switch]$NoBrowser)

$host.UI.RawUI.WindowTitle = "Affiliate Website Launcher"

Clear-Host
Write-Host @"
========================================
   AFFILIATE WEBSITE LAUNCHER
========================================

This script will:
  1. Check for any issues
  2. Fix them automatically
  3. Start the web server
  4. Open your browser

Press Ctrl+C to cancel at any time
"@ -ForegroundColor Cyan

Write-Host ""
Read-Host "Press Enter to continue"

# Step 1: Auto-fix all issues
Write-Host ""
Write-Host "STEP 1: Checking and fixing issues..." -ForegroundColor Yellow
& ".\scripts\auto-fix-all.ps1"

# Step 2: Kill any existing processes
Write-Host ""
Write-Host "STEP 2: Preparing environment..." -ForegroundColor Yellow
Get-NetTCPConnection -LocalPort 3000 -ErrorAction SilentlyContinue | ForEach-Object {
    Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue
    Write-Host "  Stopped existing process on port 3000" -ForegroundColor Green
}

# Step 3: Start web server
Write-Host ""
Write-Host "STEP 3: Starting web server..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'apps/web'; npm run dev" -WindowStyle Normal

# Step 4: Wait and check
Write-Host ""
Write-Host "STEP 4: Waiting for server to start..." -ForegroundColor Yellow
$dots = 0
for ($i = 1; $i -le 30; $i++) {
    Start-Sleep -Seconds 1
    Write-Host -NoNewline "."
    $dots++
    if ($dots -gt 60) { Write-Host ""; $dots = 0 }
    
    try {
        $resp = Invoke-WebRequest "http://localhost:3000" -TimeoutSec 2 -ErrorAction Stop
        Write-Host ""
        Write-Host ""
        Write-Host "========================================" -ForegroundColor Green
        Write-Host "  ✅ SERVER IS RUNNING!" -ForegroundColor Green
        Write-Host "========================================" -ForegroundColor Green
        Write-Host ""
        Write-Host "  URL: http://localhost:3000" -ForegroundColor Cyan
        Write-Host "  Status: HTTP $($resp.StatusCode)" -ForegroundColor Cyan
        Write-Host ""
        
        # Open browser
        if (-not $NoBrowser) {
            Write-Host "  Opening browser..." -ForegroundColor Yellow
            Start-Process "http://localhost:3000"
        }
        
        Write-Host "========================================" -ForegroundColor Green
        Read-Host "Press Enter to exit this window"
        exit 0
    } catch {
        # Keep waiting
    }
}

Write-Host ""
Write-Host ""
Write-Host "========================================" -ForegroundColor Red
Write-Host "  ⚠️ SERVER START TIMEOUT" -ForegroundColor Red
Write-Host "========================================" -ForegroundColor Red
Write-Host ""
Write-Host "The server is starting but taking longer than expected." -ForegroundColor Yellow
Write-Host "Check the web server window for errors." -ForegroundColor Yellow
Write-Host ""
Write-Host "Try refreshing http://localhost:3000 in a few seconds." -ForegroundColor Cyan
Write-Host ""
Read-Host "Press Enter to exit"
