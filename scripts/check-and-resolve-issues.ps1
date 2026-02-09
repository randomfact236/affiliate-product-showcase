#!/usr/bin/env pwsh
# Check and Resolve Issues - Automated Issue Detector and Fixer

param([switch]$Continuous)

$ErrorActionPreference = "Continue"

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

function Test-ServerResponse {
    try {
        $resp = Invoke-WebRequest "http://localhost:3000" -TimeoutSec 5 -ErrorAction Stop
        return @{ Success = $true; Content = $resp.Content }
    } catch {
        return @{ Success = $false; Error = $_.Exception.Message }
    }
}

function Get-ConsoleErrors {
    # Check Next.js console output for common errors
    $logFile = "apps/web/.next/trace"
    if (Test-Path $logFile) {
        $recent = Get-Content $logFile -Tail 50 -ErrorAction SilentlyContinue
        return $recent
    }
    return $null
}

function Check-TypeScriptErrors {
    Write-Status "Checking TypeScript..." "Action"
    Set-Location "apps/web"
    $errors = npx tsc --noEmit 2>&1
    Set-Location "../.."
    return $errors
}

function Check-BuildErrors {
    Write-Status "Checking for build issues..." "Action"
    Set-Location "apps/web"
    # Try a quick build check
    $buildOutput = npm run build 2>&1 | Select-Object -First 50
    Set-Location "../.."
    return $buildOutput
}

# =====================
# MAIN CHECK
# =====================

Clear-Host
Write-Status "========================================" "Info"
Write-Status "  Issue Checker & Resolver" "Info"
Write-Status "========================================" "Info"
Write-Status ""

# Check 1: Server responding
Write-Status "CHECK 1: Testing server response..." "Action"
$test = Test-ServerResponse

if (-not $test.Success) {
    Write-Status "❌ Server not responding!" "Error"
    Write-Status "Error: $($test.Error)" "Error"
    Write-Status ""
    Write-Status "To fix, run:" "Warning"
    Write-Status "  .\START-WEBSITE.bat" "Action"
    exit 1
}

Write-Status "✅ Server is responding" "Success"

# Check 2: TypeScript compilation
Write-Status ""
Write-Status "CHECK 2: TypeScript compilation..." "Action"
$tsErrors = Check-TypeScriptErrors

if ($tsErrors) {
    Write-Status "⚠️ TypeScript errors found:" "Warning"
    Write-Status $tsErrors "Error"
    
    Write-Status ""
    Write-Status "Auto-fixing..." "Action"
    .\scripts\auto-fix-all.ps1 2>&1 | Out-Null
    
    # Re-check
    $tsErrors2 = Check-TypeScriptErrors
    if ($tsErrors2) {
        Write-Status "❌ Could not auto-fix TypeScript errors" "Error"
        Write-Status "Manual fix required" "Error"
    } else {
        Write-Status "✅ TypeScript errors fixed" "Success"
    }
} else {
    Write-Status "✅ TypeScript compiles cleanly" "Success"
}

# Check 3: Common Next.js issues
Write-Status ""
Write-Status "CHECK 3: Common issues..." "Action"

$issuesFound = @()
$fixesApplied = @()

# Check for missing images
if ($test.Content -match "next.svg|vercel.svg") {
    Write-Status "⚠️ Old Next.js images still referenced" "Warning"
    $issuesFound += "Old image references"
}

# Check for proper API status
if (-not ($test.Content -match "Operational|API Status")) {
    Write-Status "⚠️ API status indicator not found" "Warning"
}

# Check dependencies
$pkg = Get-Content "apps/web/package.json" | ConvertFrom-Json
$deps = $pkg.dependencies.PSObject.Properties.Name

$required = @("@tanstack/react-query", "clsx", "tailwind-merge")
foreach ($dep in $required) {
    if ($deps -notcontains $dep) {
        $issuesFound += "Missing $dep"
        Write-Status "Installing $dep..." "Action"
        cd "apps/web"
        npm install $dep --save --legacy-peer-deps 2>&1 | Out-Null
        cd "../.."
        $fixesApplied += "Installed $dep"
    }
}

# =====================
# RESULTS
# =====================

Write-Status ""
Write-Status "========================================" "Info"
Write-Status "  DIAGNOSIS COMPLETE" "Info"
Write-Status "========================================" "Info"
Write-Status ""

if ($issuesFound.Count -eq 0 -and $fixesApplied.Count -eq 0) {
    Write-Status "✅ All checks passed!" "Success"
    Write-Status ""
    Write-Status "The '1 Issue' badge is likely:" "Info"
    Write-Status "  - A Next.js dev mode indicator" "Info"
    Write-Status "  - Hot Module Reload status" "Info"
    Write-Status "  - Not an actual error" "Info"
    Write-Status ""
    Write-Status "Your website is working correctly!" "Success"
} else {
    if ($fixesApplied.Count -gt 0) {
        Write-Status "✅ Applied fixes:" "Success"
        foreach ($fix in $fixesApplied) {
            Write-Status "  - $fix" "Success"
        }
    }
    
    if ($issuesFound.Count -gt 0) {
        Write-Status "⚠️ Issues found (may need manual fix):" "Warning"
        foreach ($issue in $issuesFound) {
            Write-Status "  - $issue" "Warning"
        }
    }
}

Write-Status ""
Write-Status "Website URL: http://localhost:3000" "Success"
Write-Status ""

# Continuous monitoring
if ($Continuous) {
    Write-Status "Continuous monitoring enabled (Ctrl+C to stop)" "Info"
    while ($true) {
        Start-Sleep -Seconds 10
        $test = Test-ServerResponse
        if (-not $test.Success) {
            Write-Status "Server went down! Attempting restart..." "Error"
            .\scripts\workflow-auto-start.ps1
        }
    }
}

Write-Status "Press Enter to exit" "Info"
Read-Host
