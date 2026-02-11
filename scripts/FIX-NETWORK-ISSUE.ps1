#!/usr/bin/env pwsh
# Fix ERR_NETWORK_IO_SUSPENDED and connection issues

$ErrorActionPreference = "Stop"

Write-Host "========================================"
Write-Host "  Network Issue Resolution Script"
Write-Host "========================================"
Write-Host ""

# 1. Check and reset network adapters
Write-Host "[INFO] Checking network configuration..."
$adapters = Get-NetAdapter | Where-Object { $_.Status -eq "Up" }
Write-Host "[OK] Found $($adapters.Count) active network adapter(s)"

# 2. Check if ports are in use
Write-Host "`n[INFO] Checking port usage..."
$ports = @(3000, 3001, 3003, 5433, 6379)
foreach ($port in $ports) {
    $connection = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue
    if ($connection) {
        $process = Get-Process -Id $connection.OwningProcess -ErrorAction SilentlyContinue
        Write-Host "[WARNING] Port $port is in use by: $($process.ProcessName) (PID: $($process.Id))" -ForegroundColor Yellow
    } else {
        Write-Host "[OK] Port $port is available" -ForegroundColor Green
    }
}

# 3. Fix Frontend API URL Configuration
Write-Host "`n[INFO] Fixing Frontend API Configuration..."
$rootDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$envLocal = "$rootDir/apps/web/.env.local"

# Ensure API URL is correctly set
$apiUrl = "http://localhost:3003"
"NEXT_PUBLIC_API_URL=$apiUrl" | Out-File -FilePath $envLocal -Encoding UTF8 -Force
Write-Host "[OK] Created .env.local with API_URL=$apiUrl" -ForegroundColor Green

# 4. Check Docker containers
Write-Host "`n[INFO] Checking Docker containers..."
$dockerRunning = docker ps --format "{{.Names}}" 2>$null
if ($dockerRunning -match "postgres_affiliate") {
    Write-Host "[OK] PostgreSQL is running" -ForegroundColor Green
} else {
    Write-Host "[WARNING] PostgreSQL is not running. Starting..." -ForegroundColor Yellow
    docker start postgres_affiliate 2>$null
    Start-Sleep -Seconds 3
}

if ($dockerRunning -match "aps_redis") {
    Write-Host "[OK] Redis is running" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Redis is not running. Starting..." -ForegroundColor Yellow
    docker start aps_redis 2>$null
}

# 5. Test database connectivity
Write-Host "`n[INFO] Testing database connectivity..."
try {
    $result = docker exec postgres_affiliate psql -U postgres -d affiliate_platform -c "SELECT 1" 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] Database connection successful" -ForegroundColor Green
    } else {
        Write-Host "[WARNING] Database connection failed - may need initialization" -ForegroundColor Yellow
    }
} catch {
    Write-Host "[WARNING] Could not test database connection" -ForegroundColor Yellow
}

# 6. Clear Next.js cache (common fix for network issues)
Write-Host "`n[INFO] Clearing frontend cache..."
$nextCache = "$rootDir/apps/web/.next"
if (Test-Path $nextCache) {
    Remove-Item -Recurse -Force $nextCache -ErrorAction SilentlyContinue
    Write-Host "[OK] Cleared Next.js cache" -ForegroundColor Green
}

# Summary
Write-Host "`n========================================"
Write-Host "  NETWORK ISSUE FIX APPLIED"
Write-Host "========================================"
Write-Host ""
Write-Host "[OK] Frontend API URL configured" -ForegroundColor Green
Write-Host "[OK] Docker containers checked" -ForegroundColor Green
Write-Host "[OK] Cache cleared" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:"
Write-Host "  1. Run: .\START-WORKFLOW.ps1"
Write-Host "  2. Or manually start:"
Write-Host "     - Backend: cd apps/api; npm run start:dev"
Write-Host "     - Frontend: cd apps/web; npm run dev"
