Param(
    [Parameter(Mandatory=$true, ValueFromRemainingArguments=$true)]
    [string[]]$Args
)

$ScriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$RootDir = Resolve-Path (Join-Path $ScriptRoot '..')

if (-not $Args) {
    Write-Host "Usage: .\scripts\wpcli.ps1 <wp-cli-args>"
    exit 2
}

docker run --rm --network container:aps_wordpress -v "${RootDir}:/var/www/html" wordpress:cli wp $Args
