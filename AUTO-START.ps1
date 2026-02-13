#!/usr/bin/env pwsh
# Auto-start script for Affiliate Website - Fixes ERR_CONNECTION_REFUSED
#Requires -Version 5.1

param(
    [switch]$SkipDocker,
    [switch]$SkipAPI,
    [switch]$SkipBrowser
)

$ErrorActionPreference = "Continue"
$ProgressPreference = "Continue"

function Write-Status($Message, $Type = "Info") {
    $prefix = switch ($Type) {
        "Success" { "[OK]" }
        "Error" { "[ERR]" }
        "Warning" { "[WARN]" }
        "Info" { "[INFO]" }
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

Write-Host ""
Write-Host "========================================"
Write-Host "   AFFILIATE WEBSITE - AUTO START"
Write-Host "   Fixing ERR_CONNECTION_REFUSED"
Write-Host "========================================"
Write-Host ""

# ============================================
# STEP 1: Kill Conflicting Processes
# ============================================
Write-Host ""
Write-Host "STEP 1: Cleanup"
Write-Host "----------------------------------------"

# Kill any node processes
$nodeProcesses = Get-Process -Name "node" -ErrorAction SilentlyContinue
if ($nodeProcesses) {
    Write-Status "Stopping $($nodeProcesses.Count) Node process(es)..." "Warning"
    $nodeProcesses | Stop-Process -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 2
}

# Check for processes on ports
$ports = @(3000, 3003)
foreach ($port in $ports) {
    try {
        $connection = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($connection) {
            $process = Get-Process -Id $connection.OwningProcess -ErrorAction SilentlyContinue
            if ($process) {
                Write-Status "Stopping process on port $port (PID: $($process.Id))..." "Warning"
                Stop-Process -Id $process.Id -Force -ErrorAction SilentlyContinue
            }
        }
    }
    catch {
        # Ignore errors
    }
}

Write-Status "Cleanup complete" "Success"

# ============================================
# STEP 2: Start Docker (if available)
# ============================================
if (-not $SkipDocker) {
    Write-Host ""
    Write-Host "STEP 2: Docker Infrastructure"
    Write-Host "----------------------------------------"
    
    try {
        $dockerInfo = docker info 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Status "Docker is running" "Success"
            
            # Start containers
            $containers = docker ps -a --format "{{.Names}}" 2>&1
            
            if ($containers -match "postgres") {
                docker start postgres_affiliate 2>&1 | Out-Null
                Write-Status "PostgreSQL container started" "Success"
            }
            
            if ($containers -match "redis") {
                docker start aps_redis 2>&1 | Out-Null
                Write-Status "Redis container started" "Success"
            }
            
            # Wait for PostgreSQL
            Write-Status "Waiting for PostgreSQL..." "Info"
            $retries = 0
            while (-not (Test-Port -Port 5433) -and $retries -lt 10) {
                Start-Sleep -Seconds 2
                $retries++
            }
        }
        else {
            Write-Status "Docker is not running. Skipping infrastructure start." "Warning"
            Write-Status "API may fail if database is required." "Warning"
        }
    }
    catch {
        Write-Status "Docker check failed: $_" "Warning"
    }
}

# ============================================
# STEP 3: Start API Server
# ============================================
if (-not $SkipAPI) {
    Write-Host ""
    Write-Host "STEP 3: API Server"
    Write-Host "----------------------------------------"
    
    $apiPath = Join-Path $PSScriptRoot "apps\api"
    
    if (Test-Path $apiPath) {
        Write-Status "Starting API server on port 3003..." "Info"
        
        # Start API in background job
        $apiJob = Start-Job -ScriptBlock {
            param($Path)
            Set-Location $Path
            npm run dev 2>&1
        } -ArgumentList $apiPath
        
        # Wait for API to be ready
        Write-Status "Waiting for API to start..." "Info"
        $retries = 0
        $maxRetries = 30
        $apiReady = $false
        
        while (-not $apiReady -and $retries -lt $maxRetries) {
            Start-Sleep -Seconds 2
            $retries++
            
            # Check job output for errors
            $output = Receive-Job $apiJob
            if ($output) {
                $output | ForEach-Object { 
                    if ($_ -match "error|Error|ERROR") {
                        Write-Host "  ERROR: $_"
                    }
                }
            }
            
            # Test port
            if (Test-Port -Port 3003) {
                $apiReady = $true
            }
            
            if ($retries % 5 -eq 0) {
                Write-Host "  Still waiting... ($retries/$maxRetries)"
            }
        }
        
        if ($apiReady) {
            Write-Status "API server running on http://localhost:3003" "Success"
            Start-Sleep -Seconds 2
        }
        else {
            Write-Status "API server failed to start" "Error"
        }
        
        # Keep job reference for later cleanup
        $global:ApiJob = $apiJob
    }
    else {
        Write-Status "API path not found: $apiPath" "Error"
    }
}

# ============================================
# STEP 4: Start Web Server
# ============================================
Write-Host ""
Write-Host "STEP 4: Web Server"
Write-Host "----------------------------------------"

$webPath = Join-Path $PSScriptRoot "apps\web"

if (Test-Path $webPath) {
    Write-Status "Starting Next.js web server on port 3000..." "Info"
    
    # Start Web in background job
    $webJob = Start-Job -ScriptBlock {
        param($Path)
        Set-Location $Path
        npm run dev 2>&1
    } -ArgumentList $webPath
    
    # Wait for Web to be ready
    Write-Status "Waiting for web server to start..." "Info"
    $retries = 0
    $maxRetries = 45
    $webReady = $false
    
    while (-not $webReady -and $retries -lt $maxRetries) {
        Start-Sleep -Seconds 2
        $retries++
        
        # Check job output
        $output = Receive-Job $webJob
        if ($output) {
            # Check for ready message
            if ($output -match "Ready in" -or $output -match "Local:" -or $output -match "ready") {
                $webReady = $true
                $output | ForEach-Object { Write-Host "  $_" }
            }
            else {
                # Only show relevant lines
                $output | ForEach-Object { 
                    if ($_ -match "error|Error|ERROR|failed|Failed|ready|Ready|Local:|Network:") {
                        Write-Host "  $_"
                    }
                }
            }
        }
        
        # Test port
        if (Test-Port -Port 3000) {
            $webReady = $true
        }
        
        if ($retries % 5 -eq 0 -and -not $webReady) {
            Write-Host "  Still waiting... ($retries/$maxRetries)"
        }
    }
    
    if ($webReady) {
        Write-Status "Web server running on http://localhost:3000" "Success"
        
        # Open browser
        if (-not $SkipBrowser) {
            Write-Status "Opening browser..." "Info"
            Start-Process "http://localhost:3000"
        }
    }
    else {
        Write-Status "Web server may still be starting..." "Warning"
    }
    
    # Keep job reference
    $global:WebJob = $webJob
}
else {
    Write-Status "Web path not found: $webPath" "Error"
}

# ============================================
# STEP 5: Final Status
# ============================================
Write-Host ""
Write-Host "========================================"
Write-Host "   STATUS CHECK"
Write-Host "========================================"

Start-Sleep -Seconds 2

$services = @(
    @{ Name = "Web Server"; Port = 3000; Url = "http://localhost:3000" }
    @{ Name = "API Server"; Port = 3003; Url = "http://localhost:3003" }
)

foreach ($service in $services) {
    $isRunning = Test-Port -Port $service.Port
    $status = if ($isRunning) { "[RUNNING]" } else { "[DOWN]" }
    Write-Host "$($service.Name.PadRight(12)) Port $($service.Port.ToString().PadRight(6)) $status"
    if ($isRunning) {
        Write-Host "             URL: $($service.Url)"
    }
}

Write-Host ""
Write-Host "Press Ctrl+C to stop all servers"
Write-Host "========================================"

# Keep showing logs
while ($true) {
    if ($global:WebJob) {
        $output = Receive-Job $global:WebJob
        if ($output) { $output | ForEach-Object { Write-Host "[WEB] $_" } }
    }
    if ($global:ApiJob) {
        $output = Receive-Job $global:ApiJob
        if ($output) { $output | ForEach-Object { Write-Host "[API] $_" } }
    }
    Start-Sleep -Seconds 1
}
