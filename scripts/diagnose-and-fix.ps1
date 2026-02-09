#!/usr/bin/env pwsh
# Diagnostic and Repair Script for Affiliate Website

param(
    [switch]$Fix
)

$ErrorActionPreference = "Continue"
$issuesFound = @()

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Affiliate Website Diagnostic Tool" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

function Log($message, $level = "Info") {
    $color = switch ($level) {
        "Error" { "Red" }
        "Warning" { "Yellow" }
        "Success" { "Green" }
        default { "White" }
    }
    Write-Host "[$level] $message" -ForegroundColor $color
}

# Check Node.js
Log "Checking Node.js..." "Info"
try {
    $nodeVersion = node --version 2>$null
    Log "Node.js version: $nodeVersion" "Success"
} catch {
    $issuesFound += "Node.js not found"
    Log "Node.js not installed" "Error"
}

# Check node_modules
Log "Checking node_modules..." "Info"
if (-not (Test-Path "apps/web/node_modules")) {
    $issuesFound += "Web app node_modules missing"
    Log "Web node_modules missing" "Warning"
} else {
    Log "Web node_modules found" "Success"
}

if (-not (Test-Path "apps/api/node_modules")) {
    $issuesFound += "API node_modules missing"
    Log "API node_modules missing" "Warning"
} else {
    Log "API node_modules found" "Success"
}

# Check critical dependencies
Log "Checking critical dependencies..." "Info"
$webPkg = Get-Content "apps/web/package.json" -Raw | ConvertFrom-Json
$apiPkg = Get-Content "apps/api/package.json" -Raw | ConvertFrom-Json

# Check web dependencies
$webDeps = $webPkg.dependencies.PSObject.Properties.Name
if ($webDeps -contains "@tanstack/react-query") {
    Log "@tanstack/react-query found in web deps" "Success"
} else {
    $issuesFound += "Missing @tanstack/react-query in web"
    Log "Missing @tanstack/react-query" "Warning"
}

# Check API dependencies
$apiDeps = $apiPkg.dependencies.PSObject.Properties.Name
$criticalApiDeps = @("@nestjs/core", "@prisma/client", "ioredis", "prom-client")
foreach ($dep in $criticalApiDeps) {
    if ($apiDeps -contains $dep) {
        Log "$dep found in API deps" "Success"
    } else {
        $issuesFound += "Missing $dep in API"
        Log "Missing $dep" "Warning"
    }
}

# Check Docker
Log "Checking Docker..." "Info"
try {
    $dockerPs = docker ps --format "{{.Names}}" 2>$null
    if ($dockerPs -match "affiliate") {
        Log "Docker containers running" "Success"
    } else {
        $issuesFound += "Docker containers not running"
        Log "Docker containers not running" "Warning"
    }
} catch {
    $issuesFound += "Docker not available"
    Log "Docker not available" "Warning"
}

# Summary
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  DIAGNOSTIC RESULTS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

if ($issuesFound.Count -eq 0) {
    Log "No issues found! Environment is ready" "Success"
} else {
    Log "Found $($issuesFound.Count) issue(s):" "Warning"
    foreach ($issue in $issuesFound) {
        Write-Host "  - $issue" -ForegroundColor Yellow
    }
}

# Auto-fix
if ($Fix -and $issuesFound.Count -gt 0) {
    Write-Host ""
    Log "Applying fixes..." "Info"
    
    # Install web dependency if missing
    if ($issuesFound -contains "Missing @tanstack/react-query in web") {
        Log "Installing @tanstack/react-query..." "Info"
        cd "apps/web"
        npm install @tanstack/react-query --save --legacy-peer-deps
        cd "../.."
        Log "Installed @tanstack/react-query" "Success"
    }
    
    # Install API dependencies if missing
    $apiMissingDeps = $issuesFound | Where-Object { $_ -match "Missing (.+) in API" }
    if ($apiMissingDeps) {
        Log "Installing missing API dependencies..." "Info"
        cd "apps/api"
        npm install ioredis prom-client --save --legacy-peer-deps
        cd "../.."
        Log "Installed API dependencies" "Success"
    }
}

Write-Host ""
Write-Host "NEXT STEPS:" -ForegroundColor Cyan
Write-Host "  Start infrastructure:  npm run infra:up" -ForegroundColor White
Write-Host "  Start API:             npm run dev:api" -ForegroundColor White
Write-Host "  Start Web:             npm run dev:web" -ForegroundColor White
Write-Host ""
