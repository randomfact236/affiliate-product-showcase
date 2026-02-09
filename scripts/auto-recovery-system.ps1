#!/usr/bin/env pwsh
# Auto-Recovery System for Affiliate Website
# Automatically detects and recovers from network/server failures

param(
    [switch]$Monitor = $true,
    [int]$CheckInterval = 10,
    [string]$LogFile = "Scan-report/recovery-log.md"
)

$ErrorActionPreference = "Continue"
$script:recoveryCount = 0
$script:startTime = Get-Date
$script:webProcess = $null
$script:apiProcess = $null

# Ensure log directory exists
if (-not (Test-Path "Scan-report")) {
    New-Item -ItemType Directory -Path "Scan-report" | Out-Null
}

function Write-RecoveryLog($Message, $Level = "Info") {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $color = switch ($Level) {
        "Error" { "Red" }
        "Warning" { "Yellow" }
        "Success" { "Green" }
        "Recovery" { "Cyan" }
        default { "White" }
    }
    Write-Host "[$timestamp] [$Level] $Message" -ForegroundColor $color
    
    # Log to file
    "| $timestamp | $Level | $Message |" | Out-File -FilePath $LogFile -Append -Encoding UTF8
}

# Initialize log
@"
# Auto-Recovery System Log

**Started:** $(Get-Date)
**Mode:** $(if ($Monitor) { "Continuous Monitoring" } else { "Single Check" })

---

| Timestamp | Level | Message |
|-----------|-------|---------|

"@ | Out-File -FilePath $LogFile -Encoding UTF8

function Test-ServerHealth() {
    param($Url = "http://localhost:3000", $Name = "Web")
    
    try {
        $response = Invoke-WebRequest -Uri $Url -Method HEAD -TimeoutSec 5 -ErrorAction Stop
        return @{ Healthy = $true; StatusCode = $response.StatusCode }
    } catch {
        return @{ Healthy = $false; Error = $_.Exception.Message }
    }
}

function Get-NodeProcesses() {
    $processes = @()
    
    Get-Process node -ErrorAction SilentlyContinue | ForEach-Object {
        $cmdLine = (Get-WmiObject Win32_Process -Filter "ProcessId=$($_.Id)").CommandLine
        $processes += @{
            Id = $_.Id
            Path = $_.Path
            CommandLine = $cmdLine
            IsWeb = $cmdLine -match "next"
            IsApi = $cmdLine -match "nest"
        }
    }
    
    return $processes
}

function Stop-AllNodeProcesses() {
    Write-RecoveryLog "Stopping all Node.js processes..." "Warning"
    Get-Process node -ErrorAction SilentlyContinue | Stop-Process -Force
    Start-Sleep -Seconds 3
    Write-RecoveryLog "All Node.js processes stopped" "Success"
}

function Start-WebServer() {
    Write-RecoveryLog "Starting Web server..." "Recovery"
    
    # Start web server in a new window
    $webJob = Start-Job -ScriptBlock {
        Set-Location "$using:PWD/apps/web"
        npm run dev 2>&1
    }
    
    $script:webProcess = $webJob
    
    # Wait for ready
    $ready = $false
    $timeout = 60
    $elapsed = 0
    
    while (-not $ready -and $elapsed -lt $timeout) {
        Start-Sleep -Seconds 1
        $elapsed++
        
        $output = Receive-Job $webJob
        if ($output -match "Ready in" -or $output -match "✓ Ready" -or $output -match "http://localhost:3000") {
            $ready = $true
        }
        
        if ($elapsed % 10 -eq 0) {
            Write-RecoveryLog "Waiting for Web server... ($elapsed seconds)" "Info"
        }
    }
    
    if ($ready) {
        Write-RecoveryLog "Web server is ready" "Success"
        return $true
    } else {
        Write-RecoveryLog "Web server failed to start within timeout" "Error"
        return $false
    }
}

function Start-ApiServer() {
    Write-RecoveryLog "Starting API server..." "Recovery"
    
    $apiJob = Start-Job -ScriptBlock {
        Set-Location "$using:PWD/apps/api"
        npm run dev 2>&1
    }
    
    $script:apiProcess = $apiJob
    
    $ready = $false
    $timeout = 45
    $elapsed = 0
    
    while (-not $ready -and $elapsed -lt $timeout) {
        Start-Sleep -Seconds 1
        $elapsed++
        
        $output = Receive-Job $apiJob
        if ($output -match "Application is running" -or $output -match "3001") {
            $ready = $true
        }
        
        if ($elapsed % 10 -eq 0) {
            Write-RecoveryLog "Waiting for API server... ($elapsed seconds)" "Info"
        }
    }
    
    if ($ready) {
        Write-RecoveryLog "API server is ready" "Success"
        return $true
    } else {
        Write-RecoveryLog "API server failed to start within timeout" "Error"
        return $false
    }
}

function Repair-NetworkConnection() {
    Write-RecoveryLog "Attempting network repair..." "Recovery"
    
    # Reset Windows network stack
    try {
        # Release and renew IP
        ipconfig /release 2>&1 | Out-Null
        Start-Sleep -Seconds 1
        ipconfig /renew 2>&1 | Out-Null
        
        # Flush DNS
        ipconfig /flushdns 2>&1 | Out-Null
        
        Write-RecoveryLog "Network stack reset complete" "Success"
        return $true
    } catch {
        Write-RecoveryLog "Network repair failed: $($_.Exception.Message)" "Error"
        return $false
    }
}

function Invoke-FullRecovery() {
    $script:recoveryCount++
    
    Write-RecoveryLog "========================================" "Recovery"
    Write-RecoveryLog "  RECOVERY ATTEMPT #$($script:recoveryCount)" "Recovery"
    Write-RecoveryLog "========================================" "Recovery"
    
    # Step 1: Stop all node processes
    Stop-AllNodeProcesses
    
    # Step 2: Repair network (for ERR_NETWORK_IO_SUSPENDED)
    Repair-NetworkConnection
    
    # Step 3: Clear Next.js cache
    if (Test-Path "apps/web/.next") {
        Write-RecoveryLog "Clearing Next.js cache..." "Info"
        Remove-Item -Path "apps/web/.next" -Recurse -Force -ErrorAction SilentlyContinue
    }
    
    # Step 4: Check dependencies
    if (-not (Test-Path "apps/web/node_modules")) {
        Write-RecoveryLog "Web dependencies missing - installing..." "Warning"
        Set-Location "apps/web"
        npm install --legacy-peer-deps 2>&1 | Out-Null
        Set-Location "../.."
    }
    
    # Step 5: Start servers
    $apiStarted = Start-ApiServer
    Start-Sleep -Seconds 2
    $webStarted = Start-WebServer
    
    # Step 6: Verify
    Start-Sleep -Seconds 5
    $webHealth = Test-ServerHealth -Url "http://localhost:3000" -Name "Web"
    $apiHealth = Test-ServerHealth -Url "http://localhost:3001/api/v1/health" -Name "API"
    
    if ($webHealth.Healthy) {
        Write-RecoveryLog "✅ Recovery successful! Server is online" "Success"
        
        # Open browser
        Start-Process "http://localhost:3000"
        
        return $true
    } else {
        Write-RecoveryLog "❌ Recovery failed - server still not responding" "Error"
        return $false
    }
}

# Main execution
Write-RecoveryLog "========================================" "Info"
Write-RecoveryLog "  AUTO-RECOVERY SYSTEM STARTED" "Info"
Write-RecoveryLog "========================================" "Info"
Write-RecoveryLog "Monitoring: http://localhost:3000" "Info"
Write-RecoveryLog "Check interval: $CheckInterval seconds" "Info"
Write-RecoveryLog "Press Ctrl+C to stop" "Warning"
Write-RecoveryLog "========================================" "Info"

# Initial check
$webHealth = Test-ServerHealth -Url "http://localhost:3000" -Name "Web"

if (-not $webHealth.Healthy) {
    Write-RecoveryLog "Server not running on startup - initiating recovery..." "Warning"
    Invoke-FullRecovery | Out-Null
} else {
    Write-RecoveryLog "Server is healthy on startup" "Success"
}

# Continuous monitoring
if ($Monitor) {
    Write-RecoveryLog "Starting continuous monitoring..." "Info"
    
    $consecutiveFailures = 0
    $maxConsecutiveFailures = 3
    
    while ($true) {
        Start-Sleep -Seconds $CheckInterval
        
        $webHealth = Test-ServerHealth -Url "http://localhost:3000" -Name "Web"
        
        if (-not $webHealth.Healthy) {
            $consecutiveFailures++
            Write-RecoveryLog "Health check failed ($consecutiveFailures/$maxConsecutiveFailures): $($webHealth.Error)" "Warning"
            
            if ($consecutiveFailures -ge $maxConsecutiveFailures) {
                Write-RecoveryLog "Maximum consecutive failures reached - initiating recovery" "Error"
                $recovered = Invoke-FullRecovery
                
                if ($recovered) {
                    $consecutiveFailures = 0
                }
            }
        } else {
            if ($consecutiveFailures -gt 0) {
                Write-RecoveryLog "Server recovered - health check passed" "Success"
                $consecutiveFailures = 0
            }
        }
    }
}

# Final summary
Write-RecoveryLog "========================================" "Info"
Write-RecoveryLog "  MONITORING STOPPED" "Info"
Write-RecoveryLog "  Total recoveries: $($script:recoveryCount)" "Info"
Write-RecoveryLog "  Runtime: $((Get-Date) - $script:startTime)" "Info"
Write-RecoveryLog "========================================" "Info"
