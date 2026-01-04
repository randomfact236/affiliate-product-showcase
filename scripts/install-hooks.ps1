<#
.SYNOPSIS
  Install git hooks from `plan/git-hooks` into `.git/hooks`.

.DESCRIPTION
  Copies files from `plan/git-hooks` into the repository's local `.git/hooks`
  directory and attempts to make them executable for Unix-like environments.

.USAGE
  From repository root:
    .\scripts\install-hooks.ps1
#>

Set-StrictMode -Version Latest

try { $repo = Get-Location } catch { Write-Error "Unable to get current directory"; exit 1 }

$src = Join-Path $repo 'plan\git-hooks'
$dst = Join-Path $repo '.git\hooks'

if (-not (Test-Path $src)) { Write-Error "Source hooks folder not found: $src"; exit 1 }
if (-not (Test-Path $dst)) { New-Item -ItemType Directory -Path $dst | Out-Null }

Write-Host "Installing hooks from $src to $dst"

Get-ChildItem -Path $src -File | ForEach-Object {
  $target = Join-Path $dst $_.Name
  Copy-Item -Path $_.FullName -Destination $target -Force
  Write-Host "Installed: $($_.Name)"
}

# Try to set executable bit using Git Bash/MingW chmod if available
if (Get-Command bash -ErrorAction SilentlyContinue) {
  try {
    & bash -lc "chmod +x '.git/hooks/*'" 2>$null
    Write-Host "Set executable bit via bash chmod"
  } catch { }
}

Write-Host "Hooks installed. You can test by running a commit or push." 
exit 0
