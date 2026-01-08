Param(
  [string]$OutFile
)

$BackupDir = $env:BACKUP_DIR -or "${PWD}\backups"
If (-not (Test-Path $BackupDir)) { New-Item -ItemType Directory -Path $BackupDir | Out-Null }

$dbHost = $env:DB_HOST -or '127.0.0.1'
$dbPort = $env:DB_PORT -or '3306'
$dbUser = $env:DB_USER -or 'wp'
$dbPass = $env:DB_PASS -or 'wp'
$dbName = $env:DB_NAME -or 'wordpress'

$timestamp = Get-Date -Format yyyyMMdd_HHmmss
$default = Join-Path $BackupDir "$($dbName)_$timestamp.sql.gz"
$out = if ($OutFile) { $OutFile } else { $default }

Write-Host "Backing up database '$dbName' to $out"

if (Get-Command wp -ErrorAction SilentlyContinue) {
  try {
    & wp db export - --add-drop-table --path=. | gzip > $out
    Write-Host "Backup written using WP-CLI"
    exit 0
  } catch { }
}

if (-not (Get-Command mysqldump -ErrorAction SilentlyContinue)) {
  Write-Error "Error: neither wp nor mysqldump available"
  exit 2
}

$dumpCmd = "mysqldump -h $dbHost -P $dbPort -u $dbUser -p$dbPass --single-transaction --routines --triggers --events --add-drop-table $dbName"
cmd.exe /c "$dumpCmd | gzip > $out"
Write-Host "Backup written using mysqldump"
