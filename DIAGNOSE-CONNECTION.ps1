#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Diagnose connection issues for Affiliate Product Showcase
#>

$ErrorActionPreference = "SilentlyContinue"

# Colors
$Green = "`e[32m"
$Red = "`e[31m"
$Yellow = "`e[33m"
$Cyan = "`e[36m"
$Reset = "`e[0m"

Write-Host "${Cyan}========================================${Reset}"
Write-Host "${Cyan}  CONNECTION DIAGNOSTICS${Reset}"
Write-Host "${Cyan}========================================${Reset}"

# Check Docker
Write-Host "`n${Cyan}DOCKER CONTAINERS:${Reset}"
try {
    $containers = docker ps --format "{{.Names}}|{{.Status}}|{{.Ports}}" 2>$null
    if ($containers) {
        foreach ($line in $containers) {
            $parts = $line -split '\|'
            $name = $parts[0]
            $status = $parts[1]
            $ports = $parts[2]
            
            $statusIcon = if ($status -match "Up") { "${Green}✅${Reset}" } else { "${Red}❌${Reset}" }
            Write-Host "  $statusIcon $name - $status"
            if ($ports) {
                Write-Host "     Ports: $ports"
            }
        }
    }
    else {
        Write-Host "  ${Red}❌ No containers running${Reset}"
    }
}
catch {
    Write-Host "  ${Red}❌ Docker not accessible${Reset}"
}

# Check Ports
Write-Host "`n${Cyan}PORT STATUS:${Reset}"
$ports = @(
    @{ Port = 3000; Service = "Frontend" },
    @{ Port = 3003; Service = "API" },
    @{ Port = 5433; Service = "PostgreSQL" },
    @{ Port = 6379; Service = "Redis" }
)

foreach ($p in $ports) {
    $tcpConnection = Get-NetTCPConnection -LocalPort $p.Port -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($tcpConnection) {
        $process = Get-Process -Id $tcpConnection.OwningProcess -ErrorAction SilentlyContinue
        $procName = if ($process) { $process.ProcessName } else { "Unknown" }
        Write-Host "  ${Green}✅${Reset} Port $($p.Port.ToString().PadRight(6)) - $($p.Service.PadRight(12)) - PID: $($tcpConnection.OwningProcess) ($procName)"
    }
    else {
        Write-Host "  ${Red}❌${Reset} Port $($p.Port.ToString().PadRight(6)) - $($p.Service.PadRight(12)) - Not listening"
    }
}

# Test HTTP endpoints
Write-Host "`n${Cyan}HTTP ENDPOINTS:${Reset}"

$endpoints = @(
    @{ Url = "http://localhost:3003/health"; Name = "API Health" },
    @{ Url = "http://localhost:3000"; Name = "Frontend" }
)

foreach ($ep in $endpoints) {
    try {
        $response = Invoke-WebRequest -Uri $ep.Url -TimeoutSec 5 -UseBasicParsing -ErrorAction Stop
        Write-Host "  ${Green}✅${Reset} $($ep.Name.PadRight(15)) - HTTP $($response.StatusCode)"
    }
    catch {
        if ($_.Exception.Response) {
            $statusCode = [int]$_.Exception.Response.StatusCode
            Write-Host "  ${Yellow}⚠️${Reset} $($ep.Name.PadRight(15)) - HTTP $statusCode"
        }
        else {
            Write-Host "  ${Red}❌${Reset} $($ep.Name.PadRight(15)) - Connection refused"
        }
    }
}

# Check environment files
Write-Host "`n${Cyan}ENVIRONMENT FILES:${Reset}"
$envFiles = @(
    ".env",
    "apps/api/.env",
    "apps/frontend/.env.local"
)

foreach ($file in $envFiles) {
    $path = Join-Path $PSScriptRoot $file
    if (Test-Path $path) {
        Write-Host "  ${Green}✅${Reset} $file"
    }
    else {
        Write-Host "  ${Red}❌${Reset} $file (missing)"
    }
}

# API URL Configuration
Write-Host "`n${Cyan}API URL CONFIGURATION:${Reset}"
$frontendEnv = Join-Path $PSScriptRoot "apps\frontend\.env.local"
if (Test-Path $frontendEnv) {
    $content = Get-Content $frontendEnv -Raw
    if ($content -match "NEXT_PUBLIC_API_URL=(.+)") {
        $apiUrl = $matches[1].Trim()
        Write-Host "  Frontend API_URL: $apiUrl"
        if ($apiUrl -match "3003") {
            Write-Host "  ${Green}✅${Reset} Port matches API (3003)"
        }
        else {
            Write-Host "  ${Red}❌${Reset} Port mismatch! Expected :3003"
        }
    }
}

# Recommendations
Write-Host "`n${Cyan}RECOMMENDATIONS:${Reset}"
$issues = 0

if (-not (Get-NetTCPConnection -LocalPort 3003 -ErrorAction SilentlyContinue)) {
    Write-Host "  ${Red}•${Reset} API server is not running - run: .\FIX-AND-START.bat"
    $issues++
}

if (-not (Get-NetTCPConnection -LocalPort 3000 -ErrorAction SilentlyContinue)) {
    Write-Host "  ${Red}•${Reset} Frontend is not running - run: .\FIX-AND-START.bat"
    $issues++
}

$dockerRunning = $false
try {
    $dockerPs = docker ps --format "{{.Names}}" 2>$null
    if ($dockerPs -match "postgres_affiliate" -and $dockerPs -match "aps_redis") {
        $dockerRunning = $true
    }
}
catch {}

if (-not $dockerRunning) {
    Write-Host "  ${Red}•${Reset} Docker containers not running - run: docker start postgres_affiliate aps_redis"
    $issues++
}

if ($issues -eq 0) {
    Write-Host "  ${Green}✅${Reset} All systems operational!"
}

Write-Host "`n${Cyan}========================================${Reset}"
