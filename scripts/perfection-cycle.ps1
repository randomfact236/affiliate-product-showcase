#!/usr/bin/env pwsh
# Perfection Cycle Automation for Phase 3 Frontend
# Scans code, identifies issues, logs them, fixes, and repeats until 10/10

param(
    [string]$TargetPath = "apps/web",
    [int]$MaxIterations = 10,
    [switch]$AutoFix = $true
)

$ErrorActionPreference = "Stop"
$ProgressPreference = "Continue"

# Colors
$Red = "Red"
$Green = "Green"
$Yellow = "Yellow"
$Cyan = "Cyan"
$White = "White"

# Issue log file
$IssueLog = "Scan-report/phase3-perfection-log.md"
$ScanResults = "Scan-report/phase3-scan-results.json"

function Write-Header($text) {
    Write-Host "`n========================================" -ForegroundColor $Cyan
    Write-Host $text -ForegroundColor $Cyan
    Write-Host "========================================" -ForegroundColor $Cyan
}

function Write-SubHeader($text) {
    Write-Host "`n--- $text ---" -ForegroundColor $Yellow
}

function Initialize-LogFile {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logContent = @"
# Phase 3 Perfection Cycle Log

**Started:** $timestamp
**Target:** $TargetPath
**Goal:** Enterprise Grade 10/10

---

"@
    New-Item -ItemType Directory -Path "Scan-report" -Force | Out-Null
    Set-Content -Path $IssueLog -Value $logContent
}

function Add-ToLog($section, $content) {
    Add-Content -Path $IssueLog -Value "`n## $section - $(Get-Date -Format 'HH:mm:ss')`n"
    Add-Content -Path $IssueLog -Value $content
    Add-Content -Path $IssueLog -Value "`n---`n"
}

# Scan 1: TypeScript Type Checking
function Test-TypeScriptTypes {
    Write-SubHeader "Scan 1: TypeScript Type Checking"
    
    try {
        Push-Location $TargetPath
        $result = npm run type-check 2>&1 | Out-String
        Pop-Location
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ TypeScript: No type errors" -ForegroundColor $Green
            return @{ Score = 10; Issues = @() }
        } else {
            Write-Host "❌ TypeScript: Type errors found" -ForegroundColor $Red
            $issues = @($result -split "`n" | Where-Object { $_ -match "error TS" })
            return @{ Score = 0; Issues = $issues }
        }
    } catch {
        # Try direct tsc
        try {
            Push-Location $TargetPath
            $result = npx tsc --noEmit 2>&1 | Out-String
            Pop-Location
            
            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ TypeScript: No type errors" -ForegroundColor $Green
                return @{ Score = 10; Issues = @() }
            } else {
                $issues = @($result -split "`n" | Where-Object { $_ -match "error TS" })
                return @{ Score = 0; Issues = $issues }
            }
        } catch {
            Write-Host "⚠️ Could not run TypeScript check" -ForegroundColor $Yellow
            return @{ Score = 5; Issues = @("TypeScript check failed to run") }
        }
    }
}

# Scan 2: ESLint
function Test-ESLint {
    Write-SubHeader "Scan 2: ESLint Code Quality"
    
    try {
        Push-Location $TargetPath
        $result = npm run lint 2>&1 | Out-String
        Pop-Location
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ ESLint: No linting errors" -ForegroundColor $Green
            return @{ Score = 10; Issues = @() }
        } else {
            Write-Host "❌ ESLint: Issues found" -ForegroundColor $Red
            $issues = @($result -split "`n" | Where-Object { $_ -match "error|warning" })
            return @{ Score = 0; Issues = $issues }
        }
    } catch {
        Write-Host "⚠️ ESLint check skipped" -ForegroundColor $Yellow
        return @{ Score = 5; Issues = @() }
    }
}

# Scan 3: Component Structure & Naming
function Test-ComponentStructure {
    Write-SubHeader "Scan 3: Component Structure & Naming"
    
    $issues = @()
    $score = 10
    
    # Check for proper component file naming
    $components = Get-ChildItem -Path "$TargetPath/src/components" -Recurse -Filter "*.tsx" -ErrorAction SilentlyContinue
    
    foreach ($component in $components) {
        $content = Get-Content $component.FullName -Raw
        
        # Check for 'use client' in client components
        if ($content -match "useState|useEffect|useCallback|useMemo|onClick|onChange" -and $content -notmatch "`"use client`"") {
            $issues += "Missing 'use client' in: $($component.FullName.Replace($PWD.Path, ''))"
            $score -= 2
        }
        
        # Check for proper export
        if ($content -notmatch "export (default )?function|export const") {
            $issues += "Missing export in: $($component.FullName.Replace($PWD.Path, ''))"
            $score -= 1
        }
        
        # Check for proper typing
        if ($content -match "function.*\(.*\) {" -and $content -notmatch "function.*\(.*\):") {
            # Function without return type - check if it has JSX
            if ($content -match "return.*\(<") {
                $issues += "Component missing explicit return type: $($component.FullName.Replace($PWD.Path, ''))"
                $score -= 1
            }
        }
    }
    
    if ($issues.Count -eq 0) {
        Write-Host "✅ Component Structure: All good" -ForegroundColor $Green
    } else {
        Write-Host "❌ Component Structure: $($issues.Count) issues" -ForegroundColor $Red
    }
    
    return @{ Score = [Math]::Max(0, $score); Issues = $issues }
}

# Scan 4: Accessibility (a11y)
function Test-Accessibility {
    Write-SubHeader "Scan 4: Accessibility (a11y)"
    
    $issues = @()
    $score = 10
    
    $tsxFiles = Get-ChildItem -Path "$TargetPath/src" -Recurse -Filter "*.tsx" -ErrorAction SilentlyContinue
    
    foreach ($file in $tsxFiles) {
        $content = Get-Content $file.FullName -Raw
        
        # Check for images without alt
        $imgMatches = [regex]::Matches($content, '<img[^>]*>')
        foreach ($match in $imgMatches) {
            if ($match.Value -notmatch 'alt=') {
                $issues += "Image without alt attribute in: $($file.FullName.Replace($PWD.Path, ''))"
                $score -= 1
            }
        }
        
        # Check for buttons without type
        $buttonMatches = [regex]::Matches($content, '<button[^>]*>')
        foreach ($match in $buttonMatches) {
            if ($match.Value -notmatch 'type=') {
                $issues += "Button without type attribute in: $($file.FullName.Replace($PWD.Path, ''))"
                $score -= 1
            }
        }
        
        # Check for interactive elements without aria-label
        $interactiveMatches = [regex]::Matches($content, '<(button|a|div)[^>]*(onClick|onKeyDown)')
        foreach ($match in $interactiveMatches) {
            if ($match.Value -notmatch 'aria-label|aria-labelledby|title=') {
                $issues += "Interactive element without accessibility label in: $($file.FullName.Replace($PWD.Path, ''))"
                $score -= 1
            }
        }
    }
    
    if ($issues.Count -eq 0) {
        Write-Host "✅ Accessibility: All good" -ForegroundColor $Green
    } else {
        Write-Host "❌ Accessibility: $($issues.Count) issues" -ForegroundColor $Red
    }
    
    return @{ Score = [Math]::Max(0, $score); Issues = $issues }
}

# Scan 5: Performance & Best Practices
function Test-Performance {
    Write-SubHeader "Scan 5: Performance & Best Practices"
    
    $issues = @()
    $score = 10
    
    $tsxFiles = Get-ChildItem -Path "$TargetPath/src" -Recurse -Filter "*.tsx" -ErrorAction SilentlyContinue
    
    foreach ($file in $tsxFiles) {
        $content = Get-Content $file.FullName -Raw
        
        # Check for inline styles (bad practice)
        if ($content -match 'style=\{\{[^}]+\}\}') {
            $issues += "Inline styles found (use Tailwind classes) in: $($file.FullName.Replace($PWD.Path, ''))"
            $score -= 1
        }
        
        # Check for console.log
        if ($content -match 'console\.(log|warn|error|debug)\(') {
            $issues += "Console statement found in: $($file.FullName.Replace($PWD.Path, ''))"
            $score -= 0.5
        }
        
        # Check for any type usage
        if ($content -match ': any[;\s]') {
            $issues += "'any' type usage found in: $($file.FullName.Replace($PWD.Path, ''))"
            $score -= 1
        }
    }
    
    if ($issues.Count -eq 0) {
        Write-Host "✅ Performance: All good" -ForegroundColor $Green
    } else {
        Write-Host "❌ Performance: $($issues.Count) issues" -ForegroundColor $Red
    }
    
    return @{ Score = [Math]::Max(0, $score); Issues = $issues }
}

# Scan 6: Import Organization
function Test-Imports {
    Write-SubHeader "Scan 6: Import Organization"
    
    $issues = @()
    $score = 10
    
    $tsFiles = Get-ChildItem -Path "$TargetPath/src" -Recurse -Include "*.ts", "*.tsx" -ErrorAction SilentlyContinue
    
    foreach ($file in $tsFiles) {
        $content = Get-Content $file.FullName -Raw
        
        # Check for relative imports that should be absolute
        if ($content -match "from '\.\./(components|lib|hooks|types)/'") {
            $issues += "Use @/ alias instead of relative path in: $($file.FullName.Replace($PWD.Path, ''))"
            $score -= 0.5
        }
    }
    
    if ($issues.Count -eq 0) {
        Write-Host "✅ Imports: All good" -ForegroundColor $Green
    } else {
        Write-Host "❌ Imports: $($issues.Count) issues" -ForegroundColor $Red
    }
    
    return @{ Score = [Math]::Max(0, $score); Issues = $issues }
}

# Main perfection cycle
function Start-PerfectionCycle {
    Write-Header "PHASE 3 PERFECTION CYCLE"
    Write-Host "Target: $TargetPath" -ForegroundColor $White
    Write-Host "Max Iterations: $MaxIterations" -ForegroundColor $White
    Write-Host "Auto-Fix: $AutoFix" -ForegroundColor $White
    
    Initialize-LogFile
    
    $iteration = 0
    $overallScore = 0
    
    while ($iteration -lt $MaxIterations -and $overallScore -lt 10) {
        $iteration++
        Write-Header "ITERATION $iteration"
        
        $allIssues = @()
        $totalScore = 0
        $scanCount = 0
        
        # Run all scans
        $tsResult = Test-TypeScriptTypes
        $totalScore += $tsResult.Score
        $scanCount++
        $allIssues += $tsResult.Issues
        
        $eslintResult = Test-ESLint
        $totalScore += $eslintResult.Score
        $scanCount++
        $allIssues += $eslintResult.Issues
        
        $structureResult = Test-ComponentStructure
        $totalScore += $structureResult.Score
        $scanCount++
        $allIssues += $structureResult.Issues
        
        $a11yResult = Test-Accessibility
        $totalScore += $a11yResult.Score
        $scanCount++
        $allIssues += $a11yResult.Issues
        
        $perfResult = Test-Performance
        $totalScore += $perfResult.Score
        $scanCount++
        $allIssues += $perfResult.Issues
        
        $importResult = Test-Imports
        $totalScore += $importResult.Score
        $scanCount++
        $allIssues += $importResult.Issues
        
        # Calculate overall score
        $overallScore = $totalScore / $scanCount
        
        Write-Host "`n========================================" -ForegroundColor $Cyan
        Write-Host "OVERALL SCORE: $([Math]::Round($overallScore, 1))/10" -ForegroundColor $(if ($overallScore -ge 9) { $Green } elseif ($overallScore -ge 6) { $Yellow } else { $Red })
        Write-Host "========================================" -ForegroundColor $Cyan
        
        # Log results
        $logContent = @"
### Iteration $iteration

**Score:** $([Math]::Round($overallScore, 1))/10

**Breakdown:**
- TypeScript: $($tsResult.Score)/10
- ESLint: $($eslintResult.Score)/10
- Component Structure: $($structureResult.Score)/10
- Accessibility: $($a11yResult.Score)/10
- Performance: $($perfResult.Score)/10
- Imports: $($importResult.Score)/10

**Issues Found:** $($allIssues.Count)

`````
$($allIssues -join "`n")
`````
"@
        Add-ToLog "Iteration $iteration Results" $logContent
        
        # Check if perfect
        if ($overallScore -ge 9.9) {
            Write-Header "🎉 PERFECTION ACHIEVED!"
            Write-Host "Enterprise Grade 10/10 Reached!" -ForegroundColor $Green
            
            Add-ToLog "FINAL RESULT" "✅ ENTERPRISE GRADE 10/10 ACHIEVED"
            
            return @{ Success = $true; Score = $overallScore; Iterations = $iteration }
        }
        
        # Auto-fix if enabled
        if ($AutoFix -and $allIssues.Count -gt 0) {
            Write-SubHeader "Auto-Fixing Issues"
            Write-Host "Applying automatic fixes..." -ForegroundColor $Yellow
            
            # Try to fix with ESLint --fix
            try {
                Push-Location $TargetPath
                npx eslint . --fix 2>&1 | Out-Null
                Pop-Location
                Write-Host "✅ ESLint --fix applied" -ForegroundColor $Green
            } catch {
                Write-Host "⚠️ ESLint --fix failed" -ForegroundColor $Yellow
            }
            
            Add-ToLog "Auto-Fix Iteration $iteration" "ESLint --fix applied"
        }
        
        Write-Host "`nContinuing to next iteration..." -ForegroundColor $Yellow
        Start-Sleep -Seconds 2
    }
    
    # Max iterations reached
    Write-Header "⚠️ MAX ITERATIONS REACHED"
    Write-Host "Final Score: $([Math]::Round($overallScore, 1))/10" -ForegroundColor $Red
    Write-Host "Review $IssueLog for details" -ForegroundColor $Yellow
    
    Add-ToLog "FINAL RESULT" "⚠️ Max iterations reached. Score: $([Math]::Round($overallScore, 1))/10"
    
    return @{ Success = $false; Score = $overallScore; Iterations = $iteration }
}

# Run the cycle
$result = Start-PerfectionCycle

# Exit with appropriate code
if ($result.Success) {
    exit 0
} else {
    exit 1
}
