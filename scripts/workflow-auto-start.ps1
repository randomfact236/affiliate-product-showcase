#!/usr/bin/env pwsh
# Workflow Automation: Fix and Start Web Server
# This script runs in a loop until localhost:3000 works

param(
    [int]$MaxAttempts = 20,
    [switch]$OpenBrowser = $true
)

$ErrorActionPreference = "Continue"
$host.UI.RawUI.WindowTitle = "Affiliate Website - Auto Workflow"

function Write-Status($message, $type = "Info") {
    $color = switch ($type) {
        "Success" { "Green" }
        "Error" { "Red" }
        "Warning" { "Yellow" }
        "Action" { "Cyan" }
        default { "White" }
    }
    $time = Get-Date -Format "HH:mm:ss"
    Write-Host "[$time] $message" -ForegroundColor $color
}

function Test-Server {
    try {
        $resp = Invoke-WebRequest "http://localhost:3000" -TimeoutSec 3 -ErrorAction Stop
        return @{ Success = $true; StatusCode = $resp.StatusCode }
    } catch {
        return @{ Success = $false; Error = $_.Exception.Message }
    }
}

function Get-NodeProcesses {
    return Get-Process -Name "node" -ErrorAction SilentlyContinue | 
        Select-Object Id, ProcessName, @{N="Port"; E={
            (Get-NetTCPConnection -OwningProcess $_.Id -ErrorAction SilentlyContinue | 
                Select-Object -First 1).LocalPort
        }}
}

function Kill-Port($port) {
    Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue | ForEach-Object {
        Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue
        Write-Status "Killed process on port $port" "Warning"
    }
}

function Install-Dependencies {
    Write-Status "Installing dependencies..." "Action"
    
    # Check if node_modules exists
    if (-not (Test-Path "apps/web/node_modules")) {
        Write-Status "Installing web dependencies (this may take a minute)..." "Warning"
        Set-Location "apps/web"
        npm install --legacy-peer-deps 2>&1 | Out-Null
        Set-Location "../.."
    }
    
    # Check specific dependencies
    $pkg = Get-Content "apps/web/package.json" | ConvertFrom-Json
    $deps = $pkg.dependencies.PSObject.Properties.Name
    
    $required = @("@tanstack/react-query", "clsx", "tailwind-merge")
    foreach ($dep in $required) {
        if ($deps -notcontains $dep) {
            Write-Status "Installing $dep..." "Action"
            Set-Location "apps/web"
            npm install $dep --save --legacy-peer-deps 2>&1 | Out-Null
            Set-Location "../.."
        }
    }
    
    Write-Status "Dependencies OK" "Success"
}

function Start-WebServer {
    Write-Status "Starting Next.js dev server..." "Action"
    
    # Start in a new window so we can see the output
    Start-Process powershell -ArgumentList "-NoExit", "-Command", @"
        cd 'apps/web'
        Write-Host 'Starting Next.js dev server on http://localhost:3000...' -ForegroundColor Green
        npm run dev
"@ -WindowStyle Normal
    
    return $true
}

# =====================
# MAIN WORKFLOW
# =====================

Clear-Host
Write-Status "========================================" "Info"
Write-Status "  WORKFLOW: Auto-Fix & Start Web Server" "Info"
Write-Status "========================================" "Info"
Write-Status ""
Write-Status "This script will:" "Info"
Write-Status "  1. Check current state" "Info"
Write-Status "  2. Fix any issues found" "Info"
Write-Status "  3. Start the web server" "Info"
Write-Status "  4. Verify it works" "Info"
Write-Status "  5. Open your browser" "Info"
Write-Status ""
Write-Status "Press Ctrl+C to stop at any time" "Warning"
Write-Status ""
Start-Sleep -Seconds 2

$attempt = 0
$success = $false

while ($attempt -lt $MaxAttempts -and -not $success) {
    $attempt++
    Write-Status ""
    Write-Status "=== ATTEMPT $attempt of $MaxAttempts ===" "Warning"
    Write-Status ""
    
    # STEP 1: Test if server is already working
    Write-Status "STEP 1: Testing http://localhost:3000..." "Action"
    $test = Test-Server
    
    if ($test.Success) {
        Write-Status "✅ Server is already running! (HTTP $($test.StatusCode))" "Success"
        $success = $true
        break
    }
    
    Write-Status "Server not responding" "Error"
    Write-Status "Error: $($test.Error)" "Error"
    
    # STEP 2: Check and kill existing processes
    Write-Status ""
    Write-Status "STEP 2: Cleaning up port 3000..." "Action"
    Kill-Port 3000
    Start-Sleep -Seconds 2
    
    # STEP 3: Install dependencies
    Write-Status ""
    Write-Status "STEP 3: Checking dependencies..." "Action"
    Install-Dependencies
    
    # STEP 4: Verify TypeScript compiles
    Write-Status ""
    Write-Status "STEP 4: Checking TypeScript..." "Action"
    Set-Location "apps/web"
    $tsErrors = npx tsc --noEmit 2>&1
    Set-Location "../.."
    
    if ($tsErrors) {
        Write-Status "TypeScript errors found:" "Error"
        Write-Status $tsErrors "Error"
        Write-Status "Attempting to fix..." "Warning"
        # The auto-fix script should handle this
        .\scripts\auto-fix-all.ps1 2>&1 | Out-Null
    } else {
        Write-Status "✅ TypeScript compiles OK" "Success"
    }
    
    # STEP 5: Start the server
    Write-Status ""
    Write-Status "STEP 5: Starting web server..." "Action"
    Start-WebServer
    
    # STEP 6: Wait and verify
    Write-Status ""
    Write-Status "STEP 6: Waiting for server (max 45s)..." "Action"
    $started = $false
    for ($i = 1; $i -le 45; $i++) {
        Start-Sleep -Seconds 1
        Write-Host -NoNewline "."
        
        $test2 = Test-Server
        if ($test2.Success) {
            Write-Host ""
            Write-Status "✅ Server is responding!" "Success"
            $started = $true
            break
        }
    }
    Write-Host ""
    
    if ($started) {
        $success = $true
    } else {
        Write-Status "Server didn't start in time" "Error"
        Write-Status "Will retry..." "Warning"
        Start-Sleep -Seconds 3
    }
}

# =====================
# RESULT
# =====================

Write-Status ""
Write-Status "========================================" "Info"

if ($success) {
    Write-Status "  ✅ SUCCESS!" "Success"
    Write-Status "========================================" "Success"
    Write-Status ""
    Write-Status "  Web server is running at:" "Success"
    Write-Status "  http://localhost:3000" "Success"
    Write-Status ""
    
    if ($OpenBrowser) {
        Write-Status "Opening browser..." "Action"
        Start-Process "http://localhost:3000"
    }
    
    Write-Status "========================================" "Success"
    Write-Status ""
    Write-Status "The server is now running in a separate window." "Info"
    Write-Status "You can close this window or keep it open." "Info"
    Write-Status ""
    Read-Host "Press Enter to close this launcher"
    exit 0
} else {
    Write-Status "  ❌ FAILED AFTER $MaxAttempts ATTEMPTS" "Error"
    Write-Status "========================================" "Error"
    Write-Status ""
    Write-Status "Troubleshooting:" "Warning"
    Write-Status "  1. Check if Node.js is installed: node --version" "Info"
    Write-Status "  2. Try manual start: cd apps/web && npm run dev" "Info"
    Write-Status "  3. Check for errors in the server window" "Info"
    Write-Status ""
    Read-Host "Press Enter to exit"
    exit 1
}
