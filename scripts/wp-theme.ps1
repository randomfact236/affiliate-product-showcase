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
    (& wp theme is-installed $Slug) 2>$null
    if ($LASTEXITCODE -eq 0) {
      Write-Output "Theme $Slug already installed; ensuring active..."
      & wp theme activate $Slug | Out-Null
    } else {
      Write-Output "Installing and activating $Slug"
      & wp theme install $Slug --activate
    }
  }
  'activate' {
    (& wp theme is-installed $Slug) 2>$null
    if ($LASTEXITCODE -ne 0) { Write-Error "Theme $Slug not installed"; exit 2 }
    $status = & wp theme status $Slug
    if ($status -match 'Active') { Write-Output "Theme $Slug already active" } else { & wp theme activate $Slug }
  }
}
