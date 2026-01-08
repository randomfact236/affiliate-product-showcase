Param(
  [string]$File
)

$BackupDir = $env:BACKUP_DIR -or "${PWD}\backups"
$dbHost = $env:DB_HOST -or '127.0.0.1'
$dbPort = $env:DB_PORT -or '3306'
$dbUser = $env:DB_USER -or 'wp'
$dbPass = $env:DB_PASS -or 'wp'
$dbName = $env:DB_NAME -or 'wordpress'

if (-not $File) {
  $File = Get-ChildItem -Path $BackupDir -Filter *.sql.gz | Sort-Object LastWriteTime -Descending | Select-Object -First 1 | ForEach-Object { $_.FullName }
}

if (-not $File -or -not (Test-Path $File)) {
  Write-Error "No backup file found to restore: $File"
  exit 2
}

Write-Host "Restoring database '$dbName' from $File"

if (-not (Get-Command gunzip -ErrorAction SilentlyContinue) -and -not (Get-Command gzip -ErrorAction SilentlyContinue)) {
  Write-Error "Error: gunzip/gzip is required to restore"
  exit 2
}

if (-not (Get-Command mysql -ErrorAction SilentlyContinue)) {
  Write-Error "Error: mysql client not available"
  exit 2
}

cmd.exe /c "gzip -dc \"$File\" | mysql -h $dbHost -P $dbPort -u $dbUser -p$dbPass $dbName"
Write-Host "Restore complete"

if (Get-Command wp -ErrorAction SilentlyContinue) { & wp cache flush --path=. }
