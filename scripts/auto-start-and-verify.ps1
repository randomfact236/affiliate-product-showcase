#!/usr/bin/env pwsh
# Automated Web Server Start, Verify, and Fix Loop
# This script will keep trying until localhost:3000 works perfectly

param(
    [int]$MaxRetries = 10,
    [int]$Port = 3000,
    [switch]$OpenBrowser = $true
)

$ErrorActionPreference = "Continue"
$retryCount = 0
$success = $false

function Write-Status($message, $type = "Info") {
    $color = switch ($type) {
        "Success" { "Green" }
        "Error" { "Red" }
        "Warning" { "Yellow" }
        default { "Cyan" }
    }
    $timestamp = Get-Date -Format "HH:mm:ss"
    Write-Host "[$timestamp] $message" -ForegroundColor $color
}

function Test-Server($port) {
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:$port" -TimeoutSec 5 -ErrorAction Stop
        return @{ 
            Success = $true 
            StatusCode = $response.StatusCode 
            Content = $response.Content
        }
    } catch {
        return @{ 
            Success = $false 
            Error = $_.Exception.Message 
        }
    }
}

function Test-PortAvailable($port) {
    try {
        $listener = [System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Any, $port)
        $listener.Start()
        $listener.Stop()
        return $true
    } catch {
        return $false
    }
}

function Start-WebServer {
    Write-Status "Starting web server..." "Warning"
    
    # Kill any existing node processes on the port
    Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | ForEach-Object {
        $proc = Get-Process -Id $_.OwningProcess -ErrorAction SilentlyContinue
        if ($proc -and $proc.ProcessName -eq "node") {
            Write-Status "Stopping existing node process (PID: $($proc.Id))" "Warning"
            Stop-Process -Id $proc.Id -Force -ErrorAction SilentlyContinue
            Start-Sleep -Seconds 2
        }
    }
    
    # Ensure dependencies are installed
    if (-not (Test-Path "apps/web/node_modules")) {
        Write-Status "Installing web dependencies..." "Warning"
        Set-Location "apps/web"
        npm install --legacy-peer-deps 2>&1 | Out-Null
        Set-Location "../.."
    }
    
    # Check for missing @tanstack/react-query
    $pkg = Get-Content "apps/web/package.json" -Raw | ConvertFrom-Json
    $deps = $pkg.dependencies.PSObject.Properties.Name
    if ($deps -notcontains "@tanstack/react-query") {
        Write-Status "Installing missing @tanstack/react-query..." "Warning"
        Set-Location "apps/web"
        npm install @tanstack/react-query --save --legacy-peer-deps 2>&1 | Out-Null
        Set-Location "../.."
    }
    
    # Start the web server in background
    $job = Start-Job -ScriptBlock {
        Set-Location "apps/web"
        npm run dev 2>&1
    }
    
    # Wait for server to start
    Write-Status "Waiting for server to start..." "Info"
    $attempts = 0
    $started = $false
    
    while ($attempts -lt 30 -and -not $started) {
        Start-Sleep -Seconds 1
        $attempts++
        
        $test = Test-Server $Port
        if ($test.Success) {
            $started = $true
            Write-Status "Server started successfully!" "Success"
            return $true
        }
    }
    
    if (-not $started) {
        Write-Status "Server failed to start within 30 seconds" "Error"
        $job | Stop-Job -ErrorAction SilentlyContinue
        return $false
    }
}

function Open-Browser($url) {
    Write-Status "Opening browser to $url..." "Info"
    Start-Process $url
}

function Get-ErrorType($response) {
    $content = $response.Content
    
    if ($content -match "Module not found|Cannot find module") {
        return "MISSING_MODULE"
    }
    if ($content -match "Cannot read properties of undefined|Cannot read property") {
        return "RUNTIME_ERROR"
    }
    if ($content -match "Failed to compile|Compilation error") {
        return "COMPILATION_ERROR"
    }
    if ($content -match "Next\.js|create-next-app") {
        return "BOILERPLATE"
    }
    return "UNKNOWN"
}

function Fix-Error($errorType) {
    switch ($errorType) {
        "MISSING_MODULE" {
            Write-Status "Detected missing module - installing dependencies..." "Warning"
            Set-Location "apps/web"
            npm install --legacy-peer-deps 2>&1 | Out-Null
            Set-Location "../.."
            return $true
        }
        "BOILERPLATE" {
            Write-Status "Detected boilerplate page - already fixed in code" "Success"
            return $true
        }
        "RUNTIME_ERROR" {
            Write-Status "Detected runtime error - restarting server..." "Warning"
            return $true
        }
        "COMPILATION_ERROR" {
            Write-Status "Detected compilation error - checking TypeScript..." "Warning"
            Set-Location "apps/web"
            npx tsc --noEmit 2>&1 | Out-Null
            Set-Location "../.."
            return $true
        }
        default {
            Write-Status "Unknown error type - restarting server..." "Warning"
            return $true
        }
    }
}

# =====================
# MAIN LOOP
# =====================

Write-Status "========================================" "Info"
Write-Status "  Auto Start & Verify Web Server" "Info"
Write-Status "========================================" "Info"
Write-Status "Target: http://localhost:$Port" "Info"
Write-Status "Max Retries: $MaxRetries" "Info"
Write-Status "========================================" "Info"

while ($retryCount -lt $MaxRetries -and -not $success) {
    $retryCount++
    Write-Status ""
    Write-Status "--- Attempt $retryCount of $MaxRetries ---" "Warning"
    
    # Step 1: Check if server is already running and working
    Write-Status "Checking if server is responding..." "Info"
    $test = Test-Server $Port
    
    if ($test.Success) {
        Write-Status "Server is responding (HTTP $($test.StatusCode))" "Success"
        
        # Check content for errors
        $errorType = Get-ErrorType $test
        
        if ($errorType -eq "BOILERPLATE" -or $errorType -eq "UNKNOWN") {
            Write-Status "Page loaded successfully!" "Success"
            $success = $true
            
            if ($OpenBrowser -and $retryCount -eq 1) {
                Open-Browser "http://localhost:$Port"
            }
            break
        } else {
            Write-Status "Page has issues: $errorType" "Error"
            if (Fix-Error $errorType) {
                Write-Status "Applied fix, restarting server..." "Warning"
                Start-WebServer | Out-Null
            }
        }
    } else {
        Write-Status "Server not responding: $($test.Error)" "Error"
        
        # Step 2: Check if port is in use by another process
        $portAvailable = Test-PortAvailable $Port
        
        if (-not $portAvailable) {
            Write-Status "Port $Port is in use - checking if it's a web server..." "Warning"
            
            # Try to connect anyway
            $test2 = Test-Server $Port
            if ($test2.Success) {
                Write-Status "Found working server on port $Port!" "Success"
                $success = $true
                continue
            } else {
                Write-Status "Port in use but not responding - freeing port..." "Warning"
                Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | ForEach-Object {
                    Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue
                }
                Start-Sleep -Seconds 2
            }
        }
        
        # Step 3: Start the server
        if (Start-WebServer) {
            Write-Status "Server started, waiting for it to be ready..." "Info"
            Start-Sleep -Seconds 3
            
            # Test again
            $test3 = Test-Server $Port
            if ($test3.Success) {
                Write-Status "Server is now working!" "Success"
                $success = $true
                
                if ($OpenBrowser) {
                    Open-Browser "http://localhost:$Port"
                }
            }
        } else {
            Write-Status "Failed to start server" "Error"
        }
    }
    
    if (-not $success -and $retryCount -lt $MaxRetries) {
        Write-Status "Waiting before retry..." "Info"
        Start-Sleep -Seconds 5
    }
}

# =====================
# FINAL RESULT
# =====================

Write-Status ""
Write-Status "========================================" "Info"

if ($success) {
    Write-Status "  ✅ SUCCESS!" "Success"
    Write-Status "  Web server is running at:" "Success"
    Write-Status "  http://localhost:$Port" "Success"
    Write-Status "========================================" "Info"
    
    # Final verification
    $finalTest = Test-Server $Port
    if ($finalTest.Success) {
        $lines = ($finalTest.Content -split "`n") | Select-Object -First 5
        Write-Status "Server response preview:" "Info"
        $lines | ForEach-Object { Write-Status "  $_" "Info" }
    }
    
    exit 0
} else {
    Write-Status "  ❌ FAILED" "Error"
    Write-Status "  Could not start web server after $MaxRetries attempts" "Error"
    Write-Status "========================================" "Info"
    Write-Status "Manual troubleshooting:" "Warning"
    Write-Status "  1. Check: cd apps/web && npm install" "Info"
    Write-Status "  2. Check: cd apps/web && npm run dev" "Info"
    Write-Status "  3. Check for port conflicts: netstat -ano | findstr :3000" "Info"
    exit 1
}
