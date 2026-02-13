#!/usr/bin/env pwsh
# Quick fix for ERR_CONNECTION_REFUSED - Restarts servers cleanly
$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   QUICK FIX - ERR_CONNECTION_REFUSED" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Kill all Node processes
Write-Host "[1/5] Stopping all Node processes..." -ForegroundColor Yellow
$nodeProcs = Get-Process -Name "node" -ErrorAction SilentlyContinue
if ($nodeProcs) {
    $nodeProcs | Stop-Process -Force -ErrorAction SilentlyContinue
    Write-Host "      Stopped $($nodeProcs.Count) process(es)" -ForegroundColor Green
}
else {
    Write-Host "      No Node processes found" -ForegroundColor Gray
}
Start-Sleep -Seconds 2

# Step 2: Check ports are free
Write-Host "[2/5] Verifying ports are free..." -ForegroundColor Yellow
$ports = @(3000, 3003)
foreach ($port in $ports) {
    try {
        $conn = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue
        if ($conn) {
            Stop-Process -Id $conn.OwningProcess -Force -ErrorAction SilentlyContinue
            Write-Host "      Freed port $port" -ForegroundColor Green
        }
    }
    catch {
        # Port is free
    }
}
Write-Host "      Ports are ready" -ForegroundColor Green

# Step 3: Start API Server (simple-server.js)
Write-Host "[3/5] Starting API server on port 3003..." -ForegroundColor Yellow
$apiPath = Join-Path $PSScriptRoot "apps\api"
Start-Process powershell -ArgumentList "-WindowStyle Hidden", "-Command", "cd `"$apiPath`"; node simple-server.js" -WorkingDirectory $apiPath

# Wait for API
$retries = 0
$apiReady = $false
while (-not $apiReady -and $retries -lt 15) {
    Start-Sleep -Seconds 1
    $retries++
    try {
        $client = New-Object System.Net.Sockets.TcpClient
        $client.Connect("localhost", 3003)
        $client.Close()
        $apiReady = $true
    }
    catch {
        # Not ready yet
    }
}

if ($apiReady) {
    Write-Host "      API is ready!" -ForegroundColor Green
}
else {
    Write-Host "      API may still be starting..." -ForegroundColor Yellow
}

# Step 4: Start Web Server
Write-Host "[4/5] Starting Web server on port 3000..." -ForegroundColor Yellow
$webPath = Join-Path $PSScriptRoot "apps\web"
Start-Process powershell -ArgumentList "-WindowStyle Hidden", "-Command", "cd `"$webPath`"; npm run dev" -WorkingDirectory $webPath

# Wait for Web
$retries = 0
$webReady = $false
while (-not $webReady -and $retries -lt 30) {
    Start-Sleep -Seconds 1
    $retries++
    try {
        $client = New-Object System.Net.Sockets.TcpClient
        $client.Connect("localhost", 3000)
        $client.Close()
        $webReady = $true
    }
    catch {
        # Not ready yet
    }
    
    if ($retries % 5 -eq 0 -and -not $webReady) {
        Write-Host "      Still waiting... ($retries seconds)" -ForegroundColor Gray
    }
}

if ($webReady) {
    Write-Host "      Web server is ready!" -ForegroundColor Green
}
else {
    Write-Host "      Web server may still be starting..." -ForegroundColor Yellow
}

# Step 5: Verify and open browser
Write-Host "[5/5] Final verification..." -ForegroundColor Yellow
Start-Sleep -Seconds 3

$services = @(
    @{ Name = "API"; Port = 3003; Url = "http://localhost:3003/api/v1/health" }
    @{ Name = "Web"; Port = 3000; Url = "http://localhost:3000" }
)

Write-Host ""
Write-Host "Service Status:" -ForegroundColor Cyan
Write-Host "---------------"
$allReady = $true
foreach ($svc in $services) {
    try {
        $client = New-Object System.Net.Sockets.TcpClient
        $client.Connect("localhost", $svc.Port)
        $client.Close()
        Write-Host "$($svc.Name.PadRight(6)) Port $($svc.Port.ToString().PadRight(6)) [RUNNING]" -ForegroundColor Green
        Write-Host "       URL: $($svc.Url)" -ForegroundColor Gray
    }
    catch {
        Write-Host "$($svc.Name.PadRight(6)) Port $($svc.Port.ToString().PadRight(6)) [FAILED]" -ForegroundColor Red
        $allReady = $false
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
if ($allReady) {
    Write-Host "   ALL SERVICES ARE RUNNING!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Opening browser..." -ForegroundColor Yellow
    Start-Process "http://localhost:3000"
}
else {
    Write-Host "   SOME SERVICES FAILED TO START" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Check the server windows for errors." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
