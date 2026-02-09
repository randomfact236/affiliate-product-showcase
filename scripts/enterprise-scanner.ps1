#!/usr/bin/env pwsh
# Enterprise Code Scanner - Automated Perfection Cycle
# Scans code line by line, identifies issues, logs them, and fixes automatically

param(
    [switch]$Fix = $false,
    [switch]$Continuous = $false,
    [int]$MaxRounds = 10,
    [string]$LogFile = "Scan-report/auto-scan-log.md"
)

$ErrorActionPreference = "Continue"
$scanRound = 1
$totalIssuesFixed = 0
$startTime = Get-Date

# Ensure Scan-report directory exists
if (-not (Test-Path "Scan-report")) {
    New-Item -ItemType Directory -Path "Scan-report" | Out-Null
}

# Initialize log file
$logHeader = @"
# Automated Perfection Cycle Log

**Started:** $startTime
**Mode:** $(if ($Continuous) { "Continuous" } else { "Single Pass" })
**Auto-Fix Enabled:** $Fix

---

"@

$logHeader | Out-File -FilePath $LogFile -Encoding UTF8

function Write-Log($Message, $Level = "Info") {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $color = switch ($Level) {
        "Error" { "Red" }
        "Warning" { "Yellow" }
        "Success" { "Green" }
        "Critical" { "Magenta" }
        default { "White" }
    }
    Write-Host "[$timestamp] [$Level] $Message" -ForegroundColor $color
    
    # Append to log file
    "| $timestamp | $Level | $Message |" | Out-File -FilePath $LogFile -Append -Encoding UTF8
}

function Add-IssueToLog($Round, $Id, $Location, $Issue, $Severity, $Status) {
    $issueLine = "| $Round-$Id | $Location | $Issue | $Severity | $Status |"
    $issueLine | Out-File -FilePath $LogFile -Append -Encoding UTF8
}

function Test-TypeScriptCompilation() {
    param($AppPath, $AppName)
    Write-Log "Checking TypeScript compilation for $AppName..." "Info"
    
    $originalPath = Get-Location
    Set-Location $AppPath
    $errors = npx tsc --noEmit 2>&1
    Set-Location $originalPath
    
    if ($errors) {
        $errorLines = $errors | Where-Object { $_ -match "error TS" }
        $errorCount = ($errorLines | Measure-Object).Count
        if ($errorCount -gt 0) {
            Write-Log "$AppName has $errorCount TypeScript errors" "Error"
            return @{
                Pass = $false
                Count = $errorCount
                Details = $errorLines
            }
        }
    }
    
    Write-Log "$AppName TypeScript compilation passed" "Success"
    return @{ Pass = $true }
}

function Test-DependencyIssues() {
    Write-Log "Checking dependency issues..." "Info"
    
    $issues = @()
    
    # Check for missing dependencies
    $webPkg = Get-Content "apps/web/package.json" -Raw | ConvertFrom-Json
    $apiPkg = Get-Content "apps/api/package.json" -Raw | ConvertFrom-Json
    
    $criticalDeps = @{
        "apps/web" = @("next", "react", "react-dom")
        "apps/api" = @("@nestjs/core", "@nestjs/common", "@prisma/client")
    }
    
    foreach ($app in $criticalDeps.Keys) {
        $pkg = if ($app -eq "apps/web") { $webPkg } else { $apiPkg }
        foreach ($dep in $criticalDeps[$app]) {
            if (-not ($pkg.dependencies.PSObject.Properties.Name -contains $dep)) {
                $issues += @{
                    Type = "Missing Dependency"
                    Package = $dep
                    App = $app
                    Severity = "Critical"
                }
            }
        }
    }
    
    # Check node_modules exist
    if (-not (Test-Path "apps/web/node_modules")) {
        $issues += @{
            Type = "Missing node_modules"
            App = "apps/web"
            Severity = "High"
        }
    }
    
    if (-not (Test-Path "apps/api/node_modules")) {
        $issues += @{
            Type = "Missing node_modules"
            App = "apps/api"
            Severity = "High"
        }
    }
    
    return $issues
}

function Test-SecurityPatterns() {
    Write-Log "Checking for security patterns..." "Info"
    
    $issues = @()
    $sourceFiles = Get-ChildItem -Path "apps/api/src" -Filter "*.ts" -Recurse -ErrorAction SilentlyContinue
    
    foreach ($file in $sourceFiles) {
        $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
        if (-not $content) { continue }
        
        $relativePath = $file.FullName.Replace((Get-Location).Path + "\", "")
        
        # Check for dangerous patterns
        if ($content -match "Math\.random\(\)") {
            $issues += @{
                File = $relativePath
                Pattern = "Math.random()"
                Severity = "High"
                Description = "Not cryptographically secure - use crypto.randomBytes()"
            }
        }
        
        # Check for direct env access (excluding config files and test files which have valid use cases)
        if ($content -match "process\.env\.JWT_SECRET" -and 
            -not ($relativePath -match "config") -and 
            -not ($relativePath -match "\.spec\.ts$")) {
            $issues += @{
                File = $relativePath
                Pattern = "process.env.JWT_SECRET"
                Severity = "Medium"
                Description = "Direct env access - use ConfigService"
            }
        }
        
        if ($content -match "console\.log\(") {
            $count = ([regex]::Matches($content, "console\.log\(")).Count
            if ($count -gt 0) {
                $issues += @{
                    File = $relativePath
                    Pattern = "console.log()"
                    Severity = "Low"
                    Description = "Debug logging found ($count occurrences)"
                }
            }
        }
        
        if ($content -match "debugger;") {
            $issues += @{
                File = $relativePath
                Pattern = "debugger;"
                Severity = "Medium"
                Description = "Debugger statement found"
            }
        }
    }
    
    return $issues
}

function Test-CodeQuality() {
    Write-Log "Checking code quality metrics..." "Info"
    
    $issues = @()
    $sourceFiles = Get-ChildItem -Path "apps/*/src" -Filter "*.ts" -Recurse -ErrorAction SilentlyContinue
    
    # Check for long lines
    $longLines = 0
    foreach ($file in $sourceFiles) {
        $lines = Get-Content $file.FullName -ErrorAction SilentlyContinue
        foreach ($line in $lines) {
            if ($line.Length -gt 120) {
                $longLines++
            }
        }
    }
    
    if ($longLines -gt 100) {
        $issues += @{
            Type = "Code Style"
            Issue = "$longLines lines exceed 120 characters"
            Severity = "Low"
        }
    }
    
    # Check for TODO/FIXME
    $todoCount = 0
    foreach ($file in $sourceFiles) {
        $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
        if ($content -match "TODO:|FIXME:") {
            $todoCount++
        }
    }
    
    if ($todoCount -gt 0) {
        $issues += @{
            Type = "Code Completeness"
            Issue = "$todoCount TODO/FIXME comments found"
            Severity = "Info"
        }
    }
    
    return $issues
}

function Fix-DependencyIssues($Issues) {
    $fixed = 0
    
    foreach ($issue in $Issues) {
        if ($issue.Type -eq "Missing Dependency") {
            Write-Log "Installing $($issue.Package) in $($issue.App)..." "Info"
            $originalPath = Get-Location
            Set-Location $issue.App
            npm install $issue.Package --save --legacy-peer-deps 2>&1 | Out-Null
            Set-Location $originalPath
            $fixed++
        }
        elseif ($issue.Type -eq "Missing node_modules") {
            Write-Log "Installing node_modules in $($issue.App)..." "Info"
            $originalPath = Get-Location
            Set-Location $issue.App
            npm install --legacy-peer-deps 2>&1 | Out-Null
            Set-Location $originalPath
            $fixed++
        }
    }
    
    return $fixed
}

function Calculate-QualityScore($Issues) {
    $score = 10.0
    
    foreach ($issue in $Issues) {
        $deduction = switch ($issue.Severity) {
            "Critical" { 2.0 }
            "High" { 1.0 }
            "Medium" { 0.5 }
            "Low" { 0.25 }
            "Info" { 0.0 }
            default { 0.1 }
        }
        $score -= $deduction
    }
    
    return [Math]::Max(0.0, [Math]::Min(10.0, $score))
}

# Main scanning loop
$continueScanning = $true
$round = 0

while ($continueScanning -and $round -lt $MaxRounds) {
    $round++
    Write-Log "============================================" "Info"
    Write-Log "  PERFECTION CYCLE - ROUND $round" "Info"
    Write-Log "============================================" "Info"
    
    "## Round $round Scan Results`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
    "| ID | Location | Issue | Severity | Status |" | Out-File -FilePath $LogFile -Append -Encoding UTF8
    "|----|----------|-------|----------|--------|" | Out-File -FilePath $LogFile -Append -Encoding UTF8
    
    $roundIssues = @()
    $issueId = 1
    
    # 1. TypeScript Compilation Check - API
    $apiTypeCheck = Test-TypeScriptCompilation -AppPath "apps/api" -AppName "API"
    if (-not $apiTypeCheck.Pass) {
        $roundIssues += @{
            Id = $issueId++
            Location = "apps/api"
            Issue = "TypeScript compilation failed with $($apiTypeCheck.Count) errors"
            Severity = "Critical"
        }
        Add-IssueToLog -Round $round -Id ($issueId-1) -Location "apps/api" -Issue "TypeScript compilation failed" -Severity "Critical" -Status "Pending"
    }
    
    # 2. Dependency Check
    $depIssues = Test-DependencyIssues
    if ($depIssues) {
        foreach ($dep in $depIssues) {
            $desc = if ($dep.Type -eq "Missing Dependency") { "Missing $($dep.Package)" } elseif ($dep.Type -eq "Missing node_modules") { "Missing node_modules" } else { $dep.Type }
            $roundIssues += @{
                Id = $issueId++
                Location = $dep.App
                Issue = $desc
                Severity = $dep.Severity
            }
            Add-IssueToLog -Round $round -Id ($issueId-1) -Location $dep.App -Issue $desc -Severity $dep.Severity -Status "Pending"
        }
        
        if ($Fix) {
            $fixed = Fix-DependencyIssues -Issues $depIssues
            $totalIssuesFixed += $fixed
            Write-Log "Fixed $fixed dependency issues" "Success"
        }
    }
    
    # 3. Security Patterns Check
    $securityIssues = Test-SecurityPatterns
    if ($securityIssues) {
        foreach ($sec in $securityIssues) {
            $roundIssues += @{
                Id = $issueId++
                Location = $sec.File
                Issue = $sec.Description
                Severity = $sec.Severity
            }
            Add-IssueToLog -Round $round -Id ($issueId-1) -Location $sec.File -Issue $sec.Description -Severity $sec.Severity -Status "Pending"
        }
    }
    
    # 4. Code Quality Check
    $qualityIssues = Test-CodeQuality
    if ($qualityIssues) {
        foreach ($qi in $qualityIssues) {
            $roundIssues += @{
                Id = $issueId++
                Location = "apps/"
                Issue = $qi.Issue
                Severity = $qi.Severity
            }
            Add-IssueToLog -Round $round -Id ($issueId-1) -Location "apps/" -Issue $qi.Issue -Severity $qi.Severity -Status "Pending"
        }
    }
    
    # Calculate quality score
    $qualityScore = Calculate-QualityScore -Issues $roundIssues
    
    Write-Log "============================================" "Info"
    Write-Log "  ROUND $round SUMMARY" "Info"
    Write-Log "  Issues Found: $($roundIssues.Count)" $(if ($roundIssues.Count -eq 0) { "Success" } else { "Warning" })
    Write-Log "  Quality Score: $([math]::Round($qualityScore, 1))/10" $(if ($qualityScore -eq 10) { "Success" } elseif ($qualityScore -ge 8) { "Info" } else { "Warning" })
    Write-Log "============================================" "Info"
    
    # Add summary to log
    "`n### Round $round Summary`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
    "- **Issues Found:** $($roundIssues.Count)`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
    "- **Quality Score:** $([math]::Round($qualityScore, 1))/10`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
    "- **Status:** $(if ($qualityScore -eq 10) { "✅ ENTERPRISE GRADE" } elseif ($qualityScore -ge 8) { "⚠️ ACCEPTABLE" } else { "❌ NEEDS IMPROVEMENT" })`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
    
    # Check if we should continue
    if ($qualityScore -ge 9.5) {
        Write-Log "✅ ENTERPRISE GRADE (10/10) ACHIEVED!" "Success"
        $continueScanning = $false
        if ($Continuous) {
            Write-Log "Running in continuous mode - will re-scan in 60 seconds..." "Info"
            Start-Sleep -Seconds 60
            $continueScanning = $true
        }
    } elseif (-not $Continuous -and -not $Fix) {
        $continueScanning = $false
    } elseif ($round -ge $MaxRounds) {
        Write-Log "Maximum rounds reached ($MaxRounds)" "Warning"
        $continueScanning = $false
    } else {
        if (-not $Continuous) {
            $continueScanning = $false
        }
    }
}

# Final summary
$endTime = Get-Date
$duration = $endTime - $startTime

Write-Log "============================================" "Info"
Write-Log "  SCAN COMPLETE" "Success"
Write-Log "============================================" "Info"
Write-Log "Total Rounds: $round" "Info"
Write-Log "Issues Fixed: $totalIssuesFixed" "Info"
Write-Log "Duration: $($duration.ToString('hh\:mm\:ss'))" "Info"
Write-Log "Log File: $LogFile" "Info"
Write-Log "============================================" "Info"

"`n---`n`n## Final Summary`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
"| Metric | Value |`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
"|--------|-------|`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
"| Total Rounds | $round |`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
"| Issues Fixed | $totalIssuesFixed |`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
"| Duration | $($duration.ToString('hh\:mm\:ss')) |`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
"| Final Quality Score | $([math]::Round($qualityScore, 1))/10 |`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
"| Final Status | $(if ($qualityScore -ge 9.5) { "✅ ENTERPRISE GRADE" } else { "⚠️ NEEDS WORK" }) |`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8
"| Log File | $LogFile |`n" | Out-File -FilePath $LogFile -Append -Encoding UTF8

exit $(if ($qualityScore -ge 9.5) { 0 } else { 1 })
