#!/usr/bin/env pwsh
#Requires -Version 5.1
<#
.SYNOPSIS
    Fixes localhost connection refused errors for Affiliate Product Showcase
.DESCRIPTION
    Automates the fix for ERR_CONNECTION_REFUSED by:
    1. Checking and starting Docker containers
    2. Killing stuck Node.js processes
    3. Starting API server on correct port
    4. Starting frontend development server
    5. Verifying all connections
#>

param(
    [switch]$SkipDocker,
    [switch]$SkipFrontend
)

$ErrorActionPreference = "Stop"
$ProgressPreference = "Continue"

# Colors
$Green = "`e[32m"
$Red = "`e[31m"
$Yellow = "`e[33m"
$Cyan = "`e[36m"
$Reset = "`e[0m"

function Write-Status($Message, $Type = "Info") {
    $prefix = switch ($Type) {
        "Success" { "${Green}✅${Reset}" }
        "Error" { "${Red}❌${Reset}" }
        "Warning" { "${Yellow}⚠️${Reset}" }
        "Info" { "${Cyan}ℹ️${Reset}" }
    }
    Write-Host "$prefix $Message"
}

function Test-Port($Port, $TimeoutMs = 1000) {
    try {
        $client = New-Object System.Net.Sockets.TcpClient
        $connection = $client.BeginConnect("localhost", $Port, $null, $null)
        $success = $connection.AsyncWaitHandle.WaitOne($TimeoutMs, $false)
        if ($success) {
            $client.EndConnect($connection)
            $client.Close()
            return $true
        }
        $client.Close()
        return $false
    }
    catch {
        return $false
    }
}

function Get-ProcessUsingPort($Port) {
    $connection = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($connection) {
        return Get-Process -Id $connection.OwningProcess -ErrorAction SilentlyContinue
    }
    return $null
}

function Stop-ProcessOnPort($Port) {
    $process = Get-ProcessUsingPort -Port $Port
    if ($process) {
        Write-Status "Stopping process '$($process.ProcessName)' (PID: $($process.Id)) on port $Port" "Warning"
        Stop-Process -Id $process.Id -Force -ErrorAction SilentlyContinue
        Start-Sleep -Seconds 2
    }
}

# ============================================
# STEP 1: Check Docker Containers
# ============================================
Write-Host "`n${Cyan}========================================${Reset}"
Write-Host "${Cyan}  STEP 1: Docker Container Check${Reset}"
Write-Host "${Cyan}========================================${Reset}"

if (-not $SkipDocker) {
    try {
        $dockerPs = docker ps --format "{{.Names}}|{{.Status}}" 2>&1
        
        $requiredContainers = @(
            @{ Name = "postgres_affiliate"; Port = 5433 },
            @{ Name = "aps_redis"; Port = 6379 }
        )
        
        foreach ($container in $requiredContainers) {
            $containerLine = $dockerPs | Where-Object { $_ -match $container.Name }
            if ($containerLine -and $containerLine -match "Up") {
                Write-Status "Container '$($container.Name)' is running" "Success"
            }
            else {
                Write-Status "Starting container '$($container.Name)'..." "Warning"
                docker start $container.Name 2>&1 | Out-Null
                Start-Sleep -Seconds 3
                
                # Wait for port to be ready
                $retries = 0
                while (-not (Test-Port -Port $container.Port) -and $retries -lt 10) {
                    Write-Host "  Waiting for port $($container.Port)..."
                    Start-Sleep -Seconds 2
                    $retries++
                }
                
                if (Test-Port -Port $container.Port) {
                    Write-Status "Container '$($container.Name)' is ready" "Success"
                }
                else {
                    Write-Status "Container '$($container.Name)' failed to start" "Error"
                }
            }
        }
    }
    catch {
        Write-Status "Docker check failed: $_" "Error"
    }
}
else {
    Write-Status "Skipping Docker check" "Info"
}

# ============================================
# STEP 2: Stop Conflicting Processes
# ============================================
Write-Host "`n${Cyan}========================================${Reset}"
Write-Host "${Cyan}  STEP 2: Port Cleanup${Reset}"
Write-Host "${Cyan}========================================${Reset}"

$ports = @(3000, 3003, 3001, 3002)
foreach ($port in $ports) {
    if (Test-Port -Port $port) {
        Stop-ProcessOnPort -Port $port
    }
}

# Kill any orphaned Node processes
$nodeProcesses = Get-Process -Name "node" -ErrorAction SilentlyContinue | Where-Object {
    $_.Path -like "*affiliate-product-showcase*"
}
if ($nodeProcesses) {
    Write-Status "Found $($nodeProcesses.Count) orphaned Node process(es), stopping..." "Warning"
    $nodeProcesses | Stop-Process -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 2
}

Write-Status "Port cleanup complete" "Success"

# ============================================
# STEP 3: Start API Server
# ============================================
Write-Host "`n${Cyan}========================================${Reset}"
Write-Host "${Cyan}  STEP 3: Starting API Server${Reset}"
Write-Host "${Cyan}========================================${Reset}"

$apiPath = Join-Path $PSScriptRoot "apps\api"
$envFile = Join-Path $apiPath ".env"

# Ensure .env file exists
if (-not (Test-Path $envFile)) {
    Write-Status "Creating .env file for API..." "Warning"
    @"
NODE_ENV=development
PORT=3003
DATABASE_URL="postgresql://postgres:postgres@localhost:5433/affiliate_db?schema=public"
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=
JWT_SECRET=your-super-secret-jwt-key-change-in-production
JWT_EXPIRATION=24h
"@ | Out-File -FilePath $envFile -Encoding UTF8
    Write-Status ".env file created" "Success"
}

Write-Status "Starting API server on port 3003..." "Info"

# Start API in new window
$apiCommand = "cd `"$apiPath`"; npm run start:dev"
Start-Process powershell -ArgumentList "-NoExit", "-Command", $apiCommand -WindowStyle Minimized

# Wait for API to be ready
Write-Status "Waiting for API to start..." "Info"
$retries = 0
$maxRetries = 30
while (-not (Test-Port -Port 3003) -and $retries -lt $maxRetries) {
    Write-Host "  Checking API port 3003... ($retries/$maxRetries)"
    Start-Sleep -Seconds 2
    $retries++
}

if (Test-Port -Port 3003) {
    Start-Sleep -Seconds 3  # Give extra time for NestJS to initialize
    Write-Status "API server is running on port 3003" "Success"
    
    # Test health endpoint
    try {
        $response = Invoke-RestMethod -Uri "http://localhost:3003/health" -TimeoutSec 5 -ErrorAction Stop
        Write-Status "Health check passed: $($response.status)" "Success"
    }
    catch {
        Write-Status "Health check warning: API may still be initializing" "Warning"
    }
}
else {
    Write-Status "API server failed to start on port 3003" "Error"
}

# ============================================
# STEP 4: Start Frontend
# ============================================
if (-not $SkipFrontend) {
    Write-Host "`n${Cyan}========================================${Reset}"
    Write-Host "${Cyan}  STEP 4: Starting Frontend${Reset}"
    Write-Host "${Cyan}========================================${Reset}"
    
    $frontendPath = Join-Path $PSScriptRoot "apps\frontend"
    
    Write-Status "Starting frontend development server on port 3000..." "Info"
    
    # Start frontend in new window
    $frontendCommand = "cd `"$frontendPath`"; npm run dev"
    Start-Process powershell -ArgumentList "-NoExit", "-Command", $frontendCommand -WindowStyle Minimized
    
    # Wait for frontend
    Write-Status "Waiting for frontend to start..." "Info"
    $retries = 0
    $maxRetries = 30
    while (-not (Test-Port -Port 3000) -and $retries -lt $maxRetries) {
        Write-Host "  Checking frontend port 3000... ($retries/$maxRetries)"
        Start-Sleep -Seconds 2
        $retries++
    }
    
    if (Test-Port -Port 3000) {
        Write-Status "Frontend is running on port 3000" "Success"
    }
    else {
        Write-Status "Frontend may still be starting..." "Warning"
    }
}
else {
    Write-Status "Skipping frontend startup" "Info"
}

# ============================================
# STEP 5: Final Verification
# ============================================
Write-Host "`n${Cyan}========================================${Reset}"
Write-Host "${Cyan}  STEP 5: Verification${Reset}"
Write-Host "${Cyan}========================================${Reset}"

$services = @(
    @{ Name = "PostgreSQL"; Port = 5433 },
    @{ Name = "Redis"; Port = 6379 },
    @{ Name = "API Server"; Port = 3003 }
)

if (-not $SkipFrontend) {
    $services += @{ Name = "Frontend"; Port = 3000 }
}

Write-Host "`nService Status:"
Write-Host "---------------"
foreach ($service in $services) {
    $status = if (Test-Port -Port $service.Port) { "${Green}✅ Running${Reset}" } else { "${Red}❌ Down${Reset}" }
    Write-Host "$($service.Name.PadRight(15)) Port $($service.Port.ToString().PadRight(6)) $status"
}

# ============================================
# Summary
# ============================================
Write-Host "`n${Cyan}========================================${Reset}"
Write-Host "${Cyan}  SUMMARY${Reset}"
Write-Host "${Cyan}========================================${Reset}"

if (Test-Port -Port 3003) {
    Write-Status "API: http://localhost:3003" "Success"
    Write-Status "Health: http://localhost:3003/health" "Info"
}

if (-not $SkipFrontend -and (Test-Port -Port 3000)) {
    Write-Status "Frontend: http://localhost:3000" "Success"
}

Write-Host "`n${Yellow}Press any key to close this window...${Reset}"
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
