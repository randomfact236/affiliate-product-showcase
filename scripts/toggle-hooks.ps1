<#
.SYNOPSIS
  Enable or disable the local git hooks (`pre-commit` and `pre-push`).

.DESCRIPTION
  This script renames `.git/hooks/pre-commit` and `.git/hooks/pre-push` to
  `.git/hooks/pre-commit.disabled` / `.git/hooks/pre-push.disabled` to disable
  them, and restores the original names to enable them again.

.USAGE
  From repository root:
    # disable hooks
    .\scripts\toggle-hooks.ps1 -Action disable

    # enable hooks
    .\scripts\toggle-hooks.ps1 -Action enable
#>

param(
  [Parameter(Mandatory=$true)]
  [ValidateSet('disable','enable')]
  [string]$Action
)

Set-StrictMode -Version Latest

try { $repo = Get-Location } catch { Write-Error 'Unable to determine current directory'; exit 2 }

$hooksDir = Join-Path $repo '.git\hooks'
if (-not (Test-Path $hooksDir)) { Write-Error "Hooks folder not found: $hooksDir"; exit 3 }

$hooks = @('pre-commit','pre-push')

function Disable-Hook($name) {
  $src = Join-Path $hooksDir $name
  $dst = Join-Path $hooksDir ($name + '.disabled')
  if (Test-Path $dst) {
    Write-Host "$name already disabled (found $($name).disabled)"
    return
  }
  if (Test-Path $src) {
    Rename-Item -Path $src -NewName ($name + '.disabled') -Force
    Write-Host "Disabled: $name"
  } else {
    Write-Host "No $name hook found to disable"
  }
}

function Enable-Hook($name) {
  $src = Join-Path $hooksDir ($name + '.disabled')
  $dst = Join-Path $hooksDir $name
  if (Test-Path $dst) {
    Write-Host "$name already enabled"
    return
  }
  if (Test-Path $src) {
    Rename-Item -Path $src -NewName $name -Force
    Write-Host "Enabled: $name"
  } else {
    Write-Host "No disabled $name hook found to enable"
  }
}

if ($Action -eq 'disable') {
  foreach ($h in $hooks) { Disable-Hook $h }
  Write-Host 'Hooks disabled.'
  exit 0
} else {
  foreach ($h in $hooks) { Enable-Hook $h }
  Write-Host 'Hooks enabled.'
  exit 0
}
