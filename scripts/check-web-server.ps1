#!/usr/bin/env pwsh
# Quick Web Server Check and Auto-Start Script

param(
    [int]$Port = 3000,
    [switch]$AutoStart
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Web Server Diagnostic" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Check if port is in use
function Test-PortInUse($port) {
    try {
        $listener = [System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Any, $port)
        $listener.Start()
        $listener.Stop()
        return $false  # Port is available
    } catch {
        return $true   # Port is in use
    }
}

# Check if web server is responding
function Test-WebServer($port) {
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:$port" -TimeoutSec 5 -ErrorAction Stop
        return @{ Success = $true; StatusCode = $response.StatusCode }
    } catch {
        return @{ Success = $false; Error = $_.Exception.Message }
    }
}

Write-Host ""
Write-Host "Checking port $Port..." -ForegroundColor Yellow

$portInUse = Test-PortInUse $Port

if ($portInUse) {
    Write-Host "Port $Port is in use ✅" -ForegroundColor Green
    
    Write-Host "Testing web server response..." -ForegroundColor Yellow
    $test = Test-WebServer $Port
    
    if ($test.Success) {
        Write-Host "Web server is running and responding! ✅" -ForegroundColor Green
        Write-Host "Status Code: $($test.StatusCode)" -ForegroundColor Gray
        Write-Host ""
        Write-Host "Access your site at: http://localhost:$Port" -ForegroundColor Cyan
    } else {
        Write-Host "Port is in use but not responding to HTTP requests ❌" -ForegroundColor Red
        Write-Host "Error: $($test.Error)" -ForegroundColor Red
        Write-Host ""
        Write-Host "Another process may be using port $Port." -ForegroundColor Yellow
        Write-Host "To find and kill the process:" -ForegroundColor White
        Write-Host "  netstat -ano | findstr :$Port" -ForegroundColor Gray
    }
} else {
    Write-Host "Port $Port is available (web server not running) ⚠️" -ForegroundColor Yellow
    
    if ($AutoStart) {
        Write-Host ""
        Write-Host "Attempting to start web server..." -ForegroundColor Cyan
        
        # Check if node_modules exists
        if (-not (Test-Path "apps/web/node_modules")) {
            Write-Host "Installing dependencies first..." -ForegroundColor Yellow
            cd "apps/web"
            npm install --legacy-peer-deps
            cd "../.."
        }
        
        # Start the web server in a new window
        Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'apps/web'; npm run dev" -WindowStyle Normal
        
        Write-Host ""
        Write-Host "Web server starting... waiting for it to be ready" -ForegroundColor Yellow
        
        # Wait for server to start
        $attempts = 0
        $maxAttempts = 30
        $started = $false
        
        while ($attempts -lt $maxAttempts -and -not $started) {
            Start-Sleep -Seconds 1
            $attempts++
            
            $test = Test-WebServer $Port
            if ($test.Success) {
                $started = $true
                Write-Host ""
                Write-Host "========================================" -ForegroundColor Green
                Write-Host "  Web server is now running! ✅" -ForegroundColor Green
                Write-Host "========================================" -ForegroundColor Green
                Write-Host ""
                Write-Host "Access your site at: http://localhost:$Port" -ForegroundColor Cyan
            }
        }
        
        if (-not $started) {
            Write-Host ""
            Write-Host "Server didn't start within 30 seconds. Check the window for errors." -ForegroundColor Red
        }
    } else {
        Write-Host ""
        Write-Host "To start the web server, run:" -ForegroundColor Cyan
        Write-Host "  cd apps/web && npm run dev" -ForegroundColor White
        Write-Host ""
        Write-Host "Or use auto-start:" -ForegroundColor Cyan
        Write-Host "  .\scripts\check-web-server.ps1 -AutoStart" -ForegroundColor White
    }
}

Write-Host ""
