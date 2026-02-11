#!/usr/bin/env powershell
#requires -version 5
<#
.SYNOPSIS
    Fixes common null/undefined runtime errors in React components
.DESCRIPTION
    Scans TypeScript/React files for potential null reference errors and applies fixes
#>

param(
    [string]$Path = "apps/web/src",
    [switch]$DryRun
)

$fixedFiles = @()

function Fix-File {
    param([string]$FilePath)
    
    $content = Get-Content $FilePath -Raw
    $original = $content
    $changes = @()
    
    # Fix 1: data?.array.map() -> (data?.array || []).map()
    $pattern1 = 'data\?\.(\w+)\.map\('
    if ($content -match $pattern1) {
        $content = $content -replace $pattern1, '(data?.$1 || []).map('
        $changes += "Fixed: data?.(array).map() -> (data?.(array) || []).map()"
    }
    
    # Fix 2: data?.prop.access -> data?.prop?.access (deep optional chaining)
    $pattern2 = 'data\?\.(\w+)\.(\w+)\.(\w+)'
    if ($content -match $pattern2) {
        $content = $content -replace $pattern2, 'data?.$1?.$2?.$3'
        $changes += "Fixed: data?.a.b.c -> data?.a?.b?.c"
    }
    
    # Fix 3: data.prop?.access without optional on first level
    $pattern3 = '(?<!\?)\bdata\.(\w+)\?\.'
    if ($content -match $pattern3) {
        $content = $content -replace $pattern3, 'data?.$1?.'
        $changes += "Fixed: data.x?.y -> data?.x?.y"
    }
    
    if ($content -ne $original) {
        if (-not $DryRun) {
            Set-Content $FilePath $content -NoNewline
        }
        return @{
            File = $FilePath
            Changes = $changes
        }
    }
    return $null
}

# Find all TypeScript/TSX files
$files = Get-ChildItem -Path $Path -Recurse -Include "*.ts","*.tsx" | 
    Where-Object { $_.FullName -notlike "*node_modules*" }

Write-Host "Scanning $($files.Count) files for null check issues..." -ForegroundColor Cyan

foreach ($file in $files) {
    $result = Fix-File -FilePath $file.FullName
    if ($result) {
        $fixedFiles += $result
        Write-Host "Fixed: $($result.File)" -ForegroundColor Green
        foreach ($change in $result.Changes) {
            Write-Host "  - $change" -ForegroundColor Yellow
        }
    }
}

Write-Host ""
Write-Host "Fixed $($fixedFiles.Count) files." -ForegroundColor Cyan
if ($DryRun) {
    Write-Host "(Dry run - no changes saved)" -ForegroundColor Magenta
}
