Param(
  [Parameter(ValueFromRemainingArguments=$true)] [string[]]$Args
)

if (-not (Get-Command wp -ErrorAction SilentlyContinue)) {
  Write-Error 'wp CLI not found; run inside container or install WP-CLI.'
  exit 1
}

try {
  & wp core is-installed | Out-Null
  Write-Output 'WordPress already installed. Skipping core install.'
} catch {
  Write-Warning 'WordPress is not installed. Please run wp core install manually or set env vars for unattended install.'
}

foreach ($arg in $Args) {
  if ($arg -like 'plugin:*') {
    $slug = $arg -replace '^plugin:',''
    pwsh -NoProfile -Command "& ./scripts/wp-plugin.ps1 install $slug"
  } elseif ($arg -like 'theme:*') {
    $slug = $arg -replace '^theme:',''
    pwsh -NoProfile -Command "& ./scripts/wp-theme.ps1 install $slug"
  } else {
    Write-Warning "Unknown init argument: $arg"
  }
}

Write-Output 'Init complete.'
