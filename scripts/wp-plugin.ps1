Param(
  [Parameter(Mandatory=$true)] [ValidateSet('install','activate')] [string]$Action,
  [Parameter(Mandatory=$true)] [string]$Slug
)

if (-not (Get-Command wp -ErrorAction SilentlyContinue)) {
  Write-Error 'wp CLI not found; run inside container or install WP-CLI.'
  exit 1
}

switch ($Action) {
  'install' {
    $installed = (& wp plugin is-installed $Slug) 2>$null
    if ($LASTEXITCODE -eq 0) {
      Write-Output "Plugin $Slug already installed; ensuring active..."
      & wp plugin activate $Slug | Out-Null
    } else {
      Write-Output "Installing and activating $Slug"
      & wp plugin install $Slug --activate
    }
  }
  'activate' {
    (& wp plugin is-installed $Slug) 2>$null
    if ($LASTEXITCODE -ne 0) { Write-Error "Plugin $Slug not installed"; exit 2 }
    (& wp plugin is-active $Slug) 2>$null
    if ($LASTEXITCODE -eq 0) { Write-Output "Plugin $Slug already active" } else { & wp plugin activate $Slug }
  }
}
