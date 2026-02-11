#!/usr/bin/env pwsh
# Affiliate Product Showcase - Complete Workflow Automation

param(
    [switch]$SkipDocker,
    [switch]$SkipBackend,
    [switch]$SkipFrontend,
    [int]$ApiPort = 3003,
    [int]$FrontendPort = 3000
)

$ErrorActionPreference = "Stop"
$rootDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $rootDir

Write-Host "========================================" -ForegroundColor Blue
Write-Host "  Affiliate Product Showcase" -ForegroundColor Blue
Write-Host "  Workflow Automation" -ForegroundColor Blue
Write-Host "========================================" -ForegroundColor Blue
Write-Host ""

# ============================================
# STEP 1: Check Docker Services
# ============================================
if (-not $SkipDocker) {
    Write-Host "[1/4] Checking Docker Services..." -ForegroundColor Yellow
    
    $requiredContainers = @(
        @{ Name = "postgres_affiliate"; Port = 5433 },
        @{ Name = "aps_redis"; Port = 6379 }
    )
    
    foreach ($container in $requiredContainers) {
        $running = docker ps --format "{{.Names}}" | Select-String $container.Name
        if (-not $running) {
            Write-Host "  Starting $($container.Name)..." -ForegroundColor Yellow
            docker start $container.Name 2>&1 | Out-Null
            Start-Sleep -Seconds 3
        }
        Write-Host "  [OK] $($container.Name) on port $($container.Port)" -ForegroundColor Green
    }
} else {
    Write-Host "[INFO] Skipped Docker checks" -ForegroundColor Gray
}

# ============================================
# STEP 2: Start Backend API
# ============================================
$apiJob = $null
if (-not $SkipBackend) {
    Write-Host "`n[2/4] Starting Backend API on port $ApiPort..." -ForegroundColor Yellow
    
    Set-Location "$rootDir/apps/api"
    
    # Check if port is already in use
    $portInUse = Get-NetTCPConnection -LocalPort $ApiPort -ErrorAction SilentlyContinue
    if ($portInUse) {
        Write-Host "  Port $ApiPort in use. Stopping existing process..." -ForegroundColor Yellow
        Stop-Process -Id $portInUse.OwningProcess -Force -ErrorAction SilentlyContinue
        Start-Sleep -Seconds 2
    }
    
    # Generate Prisma client
    Write-Host "  Generating Prisma client..." -ForegroundColor Gray
    npx prisma generate | Out-Null
    
    # Start API in background
    Write-Host "  Starting NestJS API server..." -ForegroundColor Gray
    $apiJob = Start-Job -ScriptBlock {
        param($dir, $port)
        Set-Location $dir
        $env:API_PORT = $port
        npm run start:dev 2>&1
    } -ArgumentList (Get-Location).Path, $ApiPort
    
    # Wait for API to be ready
    Write-Host "  Waiting for API to start..." -ForegroundColor Gray
    $retries = 30
    $apiReady = $false
    while ($retries -gt 0 -and -not $apiReady) {
        Start-Sleep -Seconds 1
        try {
            $response = Invoke-RestMethod -Uri "http://localhost:$ApiPort/health" -TimeoutSec 2 -ErrorAction SilentlyContinue
            if ($response.status -eq "ok") { $apiReady = $true }
        } catch {}
        $retries--
    }
    
    if ($apiReady) {
        Write-Host "  [OK] API running on http://localhost:$ApiPort" -ForegroundColor Green
    } else {
        Write-Host "  [WARNING] API starting... check logs with: Get-Job | Receive-Job" -ForegroundColor Yellow
    }
    
    Set-Location $rootDir
}

# ============================================
# STEP 3: Update Frontend API URL
# ============================================
Write-Host "`n[3/4] Configuring Frontend..." -ForegroundColor Yellow
$envFile = "$rootDir/apps/web/.env.local"
$apiUrl = "http://localhost:$ApiPort"
"NEXT_PUBLIC_API_URL=$apiUrl" | Out-File -FilePath $envFile -Encoding UTF8 -Force
Write-Host "  [OK] Frontend API URL: $apiUrl" -ForegroundColor Green

# ============================================
# STEP 4: Start Frontend
# ============================================
$frontendJob = $null
if (-not $SkipFrontend) {
    Write-Host "`n[4/4] Starting Frontend on port $FrontendPort..." -ForegroundColor Yellow
    
    Set-Location "$rootDir/apps/web"
    
    # Check if port is already in use
    $portInUse = Get-NetTCPConnection -LocalPort $FrontendPort -ErrorAction SilentlyContinue
    if ($portInUse) {
        Write-Host "  Port $FrontendPort in use. Stopping existing process..." -ForegroundColor Yellow
        Stop-Process -Id $portInUse.OwningProcess -Force -ErrorAction SilentlyContinue
        Start-Sleep -Seconds 2
    }
    
    # Clear cache
    $nextCache = "$rootDir/apps/web/.next"
    if (Test-Path $nextCache) {
        Remove-Item -Recurse -Force $nextCache -ErrorAction SilentlyContinue
    }
    
    # Start Frontend
    Write-Host "  Starting Next.js..." -ForegroundColor Gray
    $frontendJob = Start-Job -ScriptBlock {
        param($dir, $port)
        Set-Location $dir
        npm run dev -- --port $port 2>&1
    } -ArgumentList (Get-Location).Path, $FrontendPort
    
    # Wait for Frontend
    Write-Host "  Waiting for Frontend..." -ForegroundColor Gray
    $retries = 60
    $frontendReady = $false
    while ($retries -gt 0 -and -not $frontendReady) {
        Start-Sleep -Seconds 1
        try {
            $response = Invoke-WebRequest -Uri "http://localhost:$FrontendPort" -TimeoutSec 2 -ErrorAction SilentlyContinue
            if ($response.StatusCode -eq 200) { $frontendReady = $true }
        } catch {}
        $retries--
    }
    
    if ($frontendReady) {
        Write-Host "  [OK] Frontend running on http://localhost:$FrontendPort" -ForegroundColor Green
    } else {
        Write-Host "  [WARNING] Frontend starting..." -ForegroundColor Yellow
    }
    
    Set-Location $rootDir
}

# ============================================
# SUMMARY
# ============================================
Write-Host "`n========================================" -ForegroundColor Green
Write-Host "  WORKFLOW STARTED SUCCESSFULLY" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Database:   postgresql://localhost:5433"
Write-Host "  Redis:      redis://localhost:6379"
Write-Host "  API:        http://localhost:$ApiPort"
Write-Host "  Frontend:   http://localhost:$FrontendPort"
Write-Host ""
Write-Host "========================================" -ForegroundColor Gray

if ($apiJob) {
    Write-Host "API Logs:     Get-Job -Id $($apiJob.Id) | Receive-Job -Keep"
}
if ($frontendJob) {
    Write-Host "Frontend Logs: Get-Job -Id $($frontendJob.Id) | Receive-Job -Keep"
}
Write-Host "Stop All:     Stop-Job *; Remove-Job *"
Write-Host "========================================" -ForegroundColor Gray

# Keep script running
while ($true) {
    Start-Sleep -Seconds 10
    $runningJobs = Get-Job | Where-Object { $_.State -eq "Running" }
    if (-not $runningJobs -and ($apiJob -or $frontendJob)) {
        Write-Host "`n[INFO] All services stopped." -ForegroundColor Yellow
        break
    }
}
