Param(
    [string]$Branch = 'backup',
    [string]$Remote = 'origin',
    [string]$ReturnBranch = 'main',
    [switch]$DryRun,
    [switch]$Commit,
    [string]$Message = 'Save changes before pushing'
)

function Exec-Git {
    param([string[]]$Args)
    if ($DryRun) {
        Write-Output "[DRY-RUN] git $($Args -join ' ')"
        return 0
    }
    & git @Args
    return $LASTEXITCODE
}

# ensure we're in a git repository
$repoRoot = git rev-parse --show-toplevel 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Error "Not inside a Git repository. Run this script from inside a repo."
    exit 2
}

$current = git rev-parse --abbrev-ref HEAD

Write-Output "Current branch: $current"
Write-Output "Target push branch: $Branch (remote: $Remote), will return to: $ReturnBranch"

if ($Branch -ne $current) {
    # checkout target branch (create from remote if needed)
    if (git show-ref --verify --quiet "refs/heads/$Branch") {
        Exec-Git @('checkout', $Branch) | Out-Null
    } else {
        Exec-Git @('fetch', $Remote, $Branch) | Out-Null
        # if remote branch exists, create from remote, otherwise create new local
        $ls = git ls-remote --exit-code $Remote $Branch 2>$null
        if ($LASTEXITCODE -eq 0) {
            Exec-Git @('checkout','-b',$Branch,"$Remote/$Branch") | Out-Null
        } else {
            Exec-Git @('checkout','-b',$Branch) | Out-Null
        }
    }
}

if ($Commit) {
    Exec-Git @('add','-A') | Out-Null
    Exec-Git @('commit','-m',$Message,'--allow-empty') | Out-Null
}

# push and set upstream
Exec-Git @('push',$Remote,$Branch,'--set-upstream') | Out-Null

# switch back to return branch
Exec-Git @('checkout',$ReturnBranch) | Out-Null

Write-Output "Done."
