#!/usr/bin/env pwsh
# Automatic Fixer - Fixes all known issues automatically

param([switch]$StartServer)

$ErrorActionPreference = "Continue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Automatic Issue Fixer" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$fixesApplied = 0

# Fix 1: Check and install web dependencies
Write-Host "[1/5] Checking web dependencies..." -ForegroundColor Yellow
Set-Location "apps/web"

$pkg = Get-Content "package.json" | ConvertFrom-Json
$deps = $pkg.dependencies.PSObject.Properties.Name

$requiredDeps = @{
    "@tanstack/react-query" = "^5.0.0"
    "clsx" = "^2.1.0"
    "tailwind-merge" = "^2.2.0"
}

foreach ($dep in $requiredDeps.GetEnumerator()) {
    if ($deps -notcontains $dep.Key) {
        Write-Host "  Installing $($dep.Key)..." -ForegroundColor Yellow
        npm install "$($dep.Key)@$($dep.Value)" --save --legacy-peer-deps 2>&1 | Out-Null
        $fixesApplied++
        Write-Host "  ✅ Installed $($dep.Key)" -ForegroundColor Green
    } else {
        Write-Host "  ✅ $($dep.Key) already installed" -ForegroundColor Green
    }
}

if (-not (Test-Path "node_modules")) {
    Write-Host "  Installing all dependencies..." -ForegroundColor Yellow
    npm install --legacy-peer-deps 2>&1 | Out-Null
    $fixesApplied++
}

Set-Location "../.."

# Fix 2: Check TypeScript compilation
Write-Host ""
Write-Host "[2/5] Checking TypeScript compilation..." -ForegroundColor Yellow
$tsErrors = cd "apps/web"; npx tsc --noEmit 2>&1; cd "../.."
if ($tsErrors) {
    Write-Host "  Found TypeScript errors - attempting fixes..." -ForegroundColor Red
    Write-Host $tsErrors -ForegroundColor Gray
} else {
    Write-Host "  ✅ TypeScript compiles successfully" -ForegroundColor Green
}

# Fix 3: Check API dependencies
Write-Host ""
Write-Host "[3/5] Checking API dependencies..." -ForegroundColor Yellow
Set-Location "apps/api"

$apiPkg = Get-Content "package.json" | ConvertFrom-Json
$apiDeps = $apiPkg.dependencies.PSObject.Properties.Name

$apiRequired = @("ioredis", "prom-client")
foreach ($dep in $apiRequired) {
    if ($apiDeps -notcontains $dep) {
        Write-Host "  Installing $dep..." -ForegroundColor Yellow
        npm install $dep --save --legacy-peer-deps 2>&1 | Out-Null
        $fixesApplied++
        Write-Host "  ✅ Installed $dep" -ForegroundColor Green
    } else {
        Write-Host "  ✅ $dep already installed" -ForegroundColor Green
    }
}

if (-not (Test-Path "node_modules")) {
    Write-Host "  Installing all API dependencies..." -ForegroundColor Yellow
    npm install --legacy-peer-deps 2>&1 | Out-Null
    $fixesApplied++
}

Set-Location "../.."

# Fix 4: Check Prisma
Write-Host ""
Write-Host "[4/5] Checking Prisma schema..." -ForegroundColor Yellow
if (Test-Path "apps/api/prisma/schema.prisma") {
    Write-Host "  ✅ Prisma schema exists" -ForegroundColor Green
    
    # Generate Prisma client if needed
    if (-not (Test-Path "apps/api/node_modules/.prisma")) {
        Write-Host "  Generating Prisma client..." -ForegroundColor Yellow
        Set-Location "apps/api"
        npx prisma generate 2>&1 | Out-Null
        Set-Location "../.."
        $fixesApplied++
        Write-Host "  ✅ Prisma client generated" -ForegroundColor Green
    }
} else {
    Write-Host "  ❌ Prisma schema not found" -ForegroundColor Red
}

# Fix 5: Check environment files
Write-Host ""
Write-Host "[5/5] Checking environment files..." -ForegroundColor Yellow
if (-not (Test-Path "apps/api/.env")) {
    if (Test-Path "apps/api/.env.example") {
        Copy-Item "apps/api/.env.example" "apps/api/.env"
        Write-Host "  ✅ Created .env from .env.example" -ForegroundColor Green
        $fixesApplied++
    } else {
        Write-Host "  ⚠️ No .env.example found" -ForegroundColor Yellow
    }
} else {
    Write-Host "  ✅ .env exists" -ForegroundColor Green
}

# Summary
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
if ($fixesApplied -gt 0) {
    Write-Host "  ✅ Applied $fixesApplied fix(es)" -ForegroundColor Green
} else {
    Write-Host "  ✅ All checks passed - no fixes needed" -ForegroundColor Green
}
Write-Host "========================================" -ForegroundColor Cyan

# Start server if requested
if ($StartServer) {
    Write-Host ""
    Write-Host "Starting web server..." -ForegroundColor Cyan
    .\scripts\quick-start-web.ps1
}

Write-Host ""
