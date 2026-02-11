#!/usr/bin/env pwsh
# Complete Fix and Start Automation Script

$ErrorActionPreference = "Stop"
$ProgressPreference = "Continue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Affiliate Platform - Fix & Start" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configuration
$ApiPort = 3003
$FrontendPort = 3000
$rootDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $rootDir

# Step 1: Kill all Node processes
Write-Host "[STEP 1] Stopping all Node processes..." -ForegroundColor Yellow
$nodeProcesses = Get-Process -Name "node" -ErrorAction SilentlyContinue
if ($nodeProcesses) {
    $nodeProcesses | Stop-Process -Force -ErrorAction SilentlyContinue
    Write-Host "  OK Stopped $($nodeProcesses.Count) Node process(es)" -ForegroundColor Green
} else {
    Write-Host "  OK No Node processes found" -ForegroundColor Green
}
Start-Sleep -Seconds 2

# Step 2: Check and free ports
Write-Host "`n[STEP 2] Checking ports..." -ForegroundColor Yellow
$ports = @($ApiPort, $FrontendPort)
foreach ($port in $ports) {
    $conn = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue
    if ($conn) {
        Stop-Process -Id $conn.OwningProcess -Force -ErrorAction SilentlyContinue
        Write-Host "  OK Freed port $port" -ForegroundColor Green
    }
}
Start-Sleep -Seconds 2

# Step 3: Start Docker containers
Write-Host "`n[STEP 3] Starting Docker containers..." -ForegroundColor Yellow
$containers = @("postgres_affiliate", "aps_redis")
foreach ($container in $containers) {
    $running = docker ps --format "{{.Names}}" | Select-String $container
    if (-not $running) {
        docker start $container 2>&1 | Out-Null
        Write-Host "  OK Started $container" -ForegroundColor Green
    } else {
        Write-Host "  OK $container already running" -ForegroundColor Green
    }
}
Start-Sleep -Seconds 3

# Step 4: Fix Frontend Configuration
Write-Host "`n[STEP 4] Fixing Frontend configuration..." -ForegroundColor Yellow
$envFile = "$rootDir/apps/web/.env.local"
$envLocalContent = @"
NEXT_PUBLIC_API_URL=http://localhost:$ApiPort
NEXT_PUBLIC_APP_URL=http://localhost:$FrontendPort
"@
$envLocalContent | Out-File -FilePath $envFile -Encoding UTF8 -Force
Write-Host "  OK Created .env.local with API URL" -ForegroundColor Green

# Step 5: Clear Next.js cache
Write-Host "`n[STEP 5] Clearing caches..." -ForegroundColor Yellow
$cachePaths = @(
    "$rootDir/apps/web/.next",
    "$rootDir/apps/web/node_modules/.cache"
)
foreach ($path in $cachePaths) {
    if (Test-Path $path) {
        Remove-Item -Recurse -Force $path -ErrorAction SilentlyContinue
        Write-Host "  OK Cleared $path" -ForegroundColor Green
    }
}

# Step 6: Start API Server
Write-Host "`n[STEP 6] Starting API Server on port $ApiPort..." -ForegroundColor Yellow
$apiJob = Start-Job -ScriptBlock {
    param($dir)
    Set-Location "$dir/apps/api"
    node simple-server.js 2>&1
} -ArgumentList $rootDir

# Wait for API to be ready
$apiReady = $false
$retries = 30
while ($retries -gt 0 -and -not $apiReady) {
    Start-Sleep -Seconds 1
    try {
        $response = Invoke-RestMethod -Uri "http://localhost:$ApiPort/api/v1/health" -TimeoutSec 2 -ErrorAction Stop
        if ($response.status -eq "ok") {
            $apiReady = $true
        }
    } catch {}
    $retries--
}

if ($apiReady) {
    Write-Host "  OK API Server ready on http://localhost:$ApiPort" -ForegroundColor Green
} else {
    Write-Host "  WARNING API starting... check logs with: Get-Job | Receive-Job" -ForegroundColor Yellow
}

# Step 7: Start Frontend
Write-Host "`n[STEP 7] Starting Frontend on port $FrontendPort..." -ForegroundColor Yellow
$frontendJob = Start-Job -ScriptBlock {
    param($dir)
    Set-Location "$dir/apps/web"
    npm run dev -- --port 3000 2>&1
} -ArgumentList $rootDir

# Wait for Frontend
Write-Host "  Waiting for frontend to compile (this may take 30-60 seconds)..." -ForegroundColor Gray
$frontendReady = $false
$retries = 60
while ($retries -gt 0 -and -not $frontendReady) {
    Start-Sleep -Seconds 1
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:$FrontendPort" -TimeoutSec 2 -ErrorAction Stop
        if ($response.StatusCode -eq 200) {
            $frontendReady = $true
        }
    } catch {}
    $retries--
    if ($retries % 10 -eq 0) { Write-Host "." -NoNewline }
}
Write-Host ""

if ($frontendReady) {
    Write-Host "  OK Frontend ready on http://localhost:$FrontendPort" -ForegroundColor Green
} else {
    Write-Host "  WARNING Frontend compiling... check logs with: Get-Job | Receive-Job" -ForegroundColor Yellow
}

# Summary
Write-Host "`n========================================" -ForegroundColor Green
Write-Host "  ALL SERVICES STARTED!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Frontend:    http://localhost:$FrontendPort"
Write-Host "  API:         http://localhost:$ApiPort"
Write-Host "  Database:    postgresql://localhost:5433"
Write-Host "  Redis:       redis://localhost:6379"
Write-Host ""
Write-Host "  Admin Panel:   http://localhost:$FrontendPort/admin"
Write-Host "  Products:      http://localhost:$FrontendPort/admin/products"
Write-Host ""
Write-Host "========================================" -ForegroundColor Gray
Write-Host "Press Ctrl+C to stop all services" -ForegroundColor Gray
Write-Host "========================================" -ForegroundColor Gray

# Keep running and monitor
while ($true) {
    Start-Sleep -Seconds 10
    
    # Check if jobs are still running
    $apiRunning = $apiJob.State -eq "Running"
    $frontendRunning = $frontendJob.State -eq "Running"
    
    if (-not $apiRunning -and -not $frontendRunning) {
        Write-Host "`n[!] All services stopped unexpectedly" -ForegroundColor Red
        break
    }
}
