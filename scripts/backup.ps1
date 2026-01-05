Param()

# PowerShell backup script for Windows
Set-StrictMode -Version Latest

$ts = (Get-Date).ToUniversalTime().ToString('yyyyMMddTHHmmssZ')
$out = Join-Path -Path . -ChildPath "backups\$ts"
New-Item -ItemType Directory -Force -Path $out | Out-Null

if (Test-Path .env) { Get-Content .env | ForEach-Object { if ($_ -match '=') { $p = $_ -split '='; Set-Item -Path "env:$($p[0])" -Value $p[1] } } }

$db = $env:MYSQL_DATABASE -or $env:WORDPRESS_DB_NAME -or 'wordpress'
$user = $env:MYSQL_USER -or $env:WORDPRESS_DB_USER -or 'root'
$pass = $env:MYSQL_PASSWORD -or $env:WORDPRESS_DB_PASSWORD -or ''

Write-Host "Dumping DB $db to $out\db.sql"
docker exec aps_db sh -c "exec mysqldump --single-transaction -u\"$user\" -p\"$pass\" \"$db\"" > (Join-Path $out 'db.sql')

Write-Host "Archiving wp-content to $out\wp-content.tar.gz"
tar -C . -czf (Join-Path $out 'wp-content.tar.gz') wp-content

Write-Host "Backup complete: $out"
