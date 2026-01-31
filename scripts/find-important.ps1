###############################################################################
# Script to find and count !important declarations in CSS files
#
# This script recursively searches through the project directory,
# finds all CSS files, and counts occurrences of !important declarations.
#
# Usage:
#   .\scripts\find-important.ps1 [directory]
#
# Arguments:
#   directory: Optional directory to search (default: current directory)
#
# Output:
#   - Total count of !important declarations
#   - Count per file
#   - Line numbers where !important is found
###############################################################################

param(
    [string]$Directory = "."
)

# Check if directory exists
if (-not (Test-Path $Directory)) {
    Write-Host "Error: Directory '$Directory' does not exist." -ForegroundColor Red
    exit 1
}

Write-Host "Searching for CSS files in: $Directory" -ForegroundColor Cyan
Write-Host ""

# Define directories to exclude
$excludeDirs = @('node_modules', '.git', 'vendor', 'build', 'dist', '.cache')

# Find all CSS files (including SCSS, SASS, LESS)
$cssFiles = Get-ChildItem -Path $Directory -Recurse -File `
    | Where-Object { 
        $_.Extension -in @('.css', '.scss', '.sass', '.less') -and
        $excludeDirs -notcontains $_.Directory.Name -and
        $excludeDirs -notcontains $_.Directory.Parent.Name
    } `
    | Sort-Object FullName

$fileCount = $cssFiles.Count

if ($fileCount -eq 0) {
    Write-Host "No CSS files found."
    exit 0
}

Write-Host "Found $fileCount CSS files to analyze."
Write-Host ""

# Initialize counters
$totalCount = 0
$filesWithImportant = @()

# Analyze each file
foreach ($file in $cssFiles) {
    $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
    
    if ($null -eq $content) {
        continue
    }
    
    # Find all !important occurrences (case-insensitive, with optional whitespace)
    $matches = [regex]::Matches($content, '!\s*important', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    $count = $matches.Count
    
    if ($count -gt 0) {
        # Get line numbers
        $lines = @()
        $lineNum = 0
        $lineMatches = Get-Content $file.FullName -ErrorAction SilentlyContinue | ForEach-Object {
            $lineNum++
            if ($_ -match '!\s*important') {
                $lines += $lineNum
            }
        }
        
        # Get relative path
        $relPath = $file.FullName.Substring((Get-Location).Path.Length + 1)
        
        $filesWithImportant += [PSCustomObject]@{
            Path = $relPath
            Count = $count
            Lines = $lines
        }
        
        $totalCount += $count
    }
}

# Print header
Write-Host "================================================================================" -ForegroundColor Cyan
Write-Host "!IMPORTANT DECLARATION ANALYSIS REPORT" -ForegroundColor Cyan
Write-Host "================================================================================" -ForegroundColor Cyan
Write-Host ""

if ($filesWithImportant.Count -eq 0) {
    Write-Host "OK: No !important declarations found!" -ForegroundColor Green
    exit 0
}

Write-Host "Total files with !important: $($filesWithImportant.Count)"
Write-Host ""

Write-Host "--------------------------------------------------------------------------------" -ForegroundColor Cyan
Write-Host "DETAILS BY FILE:" -ForegroundColor Cyan
Write-Host "--------------------------------------------------------------------------------" -ForegroundColor Cyan
Write-Host ""

# Sort by count (descending) and display
$filesWithImportant | Sort-Object Count -Descending | ForEach-Object {
    Write-Host "[FILE] $($_.Path)" -ForegroundColor Cyan
    Write-Host "   Count: $($_.Count)"
    
    if ($_.Lines.Count -gt 0) {
        $linesStr = $_.Lines -join ', '
        if ($_.Lines.Count -gt 10) {
            $firstTen = $_.Lines[0..9] -join ', '
            $remaining = $_.Lines.Count - 10
            $linesStr = "$firstTen ... ($remaining more)"
        }
        Write-Host "   Lines: $linesStr"
    }
    Write-Host ""
}

Write-Host "--------------------------------------------------------------------------------" -ForegroundColor Cyan
Write-Host "SUMMARY:" -ForegroundColor Cyan
Write-Host "--------------------------------------------------------------------------------" -ForegroundColor Cyan
Write-Host "Files analyzed: $fileCount"
Write-Host "Files with !important: $($filesWithImportant.Count)"
Write-Host "Total !important declarations: $totalCount"
Write-Host ""

# Severity assessment
if ($totalCount -eq 0) {
    Write-Host "EXCELLENT: No !important declarations found!" -ForegroundColor Green
}
elseif ($totalCount -lt 10) {
    Write-Host "LOW: Minimal use of !important declarations." -ForegroundColor Yellow
}
elseif ($totalCount -lt 50) {
    Write-Host "MODERATE: Consider reducing !important usage." -ForegroundColor Yellow
}
else {
    Write-Host "HIGH: Excessive use of !important! This can lead to CSS specificity issues." -ForegroundColor Red
}
Write-Host ""
