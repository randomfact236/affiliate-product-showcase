#!/usr/bin/env pwsh
# Smart Launcher - Starts server and opens browser when ready

param([switch]$NoBrowser)

$ErrorActionPreference = "Continue"

Write-Host @"
========================================
   SMART WEB SERVER LAUNCHER
========================================
"@ -ForegroundColor Cyan

# Kill any existing node processes
Write-Host "[1/3] Cleaning up..." -ForegroundColor Yellow
Get-Process node -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

# Start the server in a job so we can monitor it
Write-Host "[2/3] Starting Next.js dev server..." -ForegroundColor Yellow
Write-Host ""

$job = Start-Job -ScriptBlock {
    Set-Location "apps/web"
    npm run dev 2>&1
}

# Monitor the output for "Ready" message
Write-Host "Waiting for server to be ready..." -ForegroundColor Cyan
$ready = $false
$timeout = 60
$elapsed = 0

while (-not $ready -and $elapsed -lt $timeout) {
    Start-Sleep -Seconds 1
    $elapsed++
    
    # Check job output
    $output = Receive-Job $job
    if ($output) {
        # Show the output
        $output | ForEach-Object { Write-Host $_ }
        
        # Check for ready message
        if ($output -match "Ready in" -or $output -match "✓ Ready") {
            $ready = $true
            Write-Host ""
            Write-Host "========================================" -ForegroundColor Green
            Write-Host "   ✅ SERVER IS READY!" -ForegroundColor Green
            Write-Host "   http://localhost:3000" -ForegroundColor Green
            Write-Host "========================================" -ForegroundColor Green
            
            if (-not $NoBrowser) {
                Write-Host ""
                Write-Host "Opening browser..." -ForegroundColor Cyan
                Start-Process "http://localhost:3000"
            }
            
            Write-Host ""
            Write-Host "The server is running in the background." -ForegroundColor White
            Write-Host "You can close this window or keep it open to see logs." -ForegroundColor Gray
            Write-Host "Press Ctrl+C to stop the server." -ForegroundColor Yellow
        }
    }
    
    # Show progress
    if ($elapsed % 5 -eq 0 -and -not $ready) {
        Write-Host "  Still waiting... ($elapsed seconds)" -ForegroundColor Gray
    }
}

if (-not $ready) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "   ⚠️ TIMEOUT - Server didn't start" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Check the error messages above." -ForegroundColor Yellow
    Stop-Job $job
    exit 1
}

# Keep showing output
Write-Host ""
Write-Host "--- Server Logs ---" -ForegroundColor Gray
while ($job.State -eq "Running") {
    $output = Receive-Job $job
    if ($output) {
        $output | ForEach-Object { Write-Host $_ }
    }
    Start-Sleep -Seconds 1
}

# Job ended
Write-Host ""
Write-Host "Server stopped." -ForegroundColor Yellow
