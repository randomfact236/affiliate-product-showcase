# Script to create a backup branch with date and timestamp
# Usage: .\scripts\create-backup-branch.ps1 [-BranchPrefix "prefix"]

param(
    [string]$BranchPrefix = "backup"
)

# Color output functions
function Write-Info {
    param([string]$Message)
    Write-Host "`ℹ " -ForegroundColor Cyan -NoNewline
    Write-Host $Message
}

function Write-Success {
    param([string]$Message)
    Write-Host "`✓ " -ForegroundColor Green -NoNewline
    Write-Host $Message
}

function Write-Warning {
    param([string]$Message)
    Write-Host "`⚠ " -ForegroundColor Yellow -NoNewline
    Write-Host $Message
}

function Write-Error {
    param([string]$Message)
    Write-Host "`✗ " -ForegroundColor Red -NoNewline
    Write-Host $Message
}

# Get current date and time in format YYYY-MM-DD-HHMMSS
$DateTime = Get-Date -Format "yyyy-MM-dd-HHmmss"

# Create branch name with date and time
$BranchName = "${BranchPrefix}-${DateTime}"

# Check if we're in a git repository
try {
    $gitDir = git rev-parse --git-dir 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Not a git repository. Please run this script from within a git repository."
        exit 1
    }
} catch {
    Write-Error "Not a git repository. Please run this script from within a git repository."
    exit 1
}

# Get current branch
$CurrentBranch = git rev-parse --abbrev-ref HEAD

Write-Info "Current branch: $CurrentBranch"
Write-Info "Creating backup branch: $BranchName"

# Check if branch already exists locally
$branchExists = git show-ref --verify --quiet refs/heads/$BranchName
if ($LASTEXITCODE -eq 0) {
    Write-Warning "Branch $BranchName already exists locally."
    $response = Read-Host "Do you want to continue? (y/N)"
    if ($response -notmatch '^[Yy]$') {
        Write-Error "Operation cancelled."
        exit 1
    }
}

# Create new branch
Write-Info "Creating new branch from current state..."
git checkout -b $BranchName
if ($LASTEXITCODE -ne 0) {
    Write-Error "Failed to create branch."
    exit 1
}

# Push to remote with upstream tracking
Write-Info "Pushing branch to remote..."
git push -u origin $BranchName --no-verify
if ($LASTEXITCODE -ne 0) {
    Write-Error "Failed to push branch to remote."
    Write-Warning "Branch was created locally but not pushed to remote."
    exit 1
}

# Switch back to original branch
Write-Info "Switching back to $CurrentBranch..."
git checkout $CurrentBranch
if ($LASTEXITCODE -ne 0) {
    Write-Warning "Failed to switch back to $CurrentBranch. You're currently on $BranchName."
}

Write-Success "Backup branch created successfully!"
Write-Host ""
Write-Host "Backup Details:" -ForegroundColor Green
Write-Host "  Branch Name: " -NoNewline
Write-Host $BranchName -ForegroundColor Cyan
Write-Host "  Remote: " -NoNewline
Write-Host "origin/$BranchName" -ForegroundColor Cyan
Write-Host "  Date/Time: " -NoNewline
Write-Host $DateTime -ForegroundColor Cyan
Write-Host "  Based on: " -NoNewline
Write-Host $CurrentBranch -ForegroundColor Cyan
Write-Host ""
Write-Info "To view all backup branches: git branch -a | Select-String backup"
Write-Info "To delete this backup: git branch -D $BranchName && git push origin --delete $BranchName"
