Param(
    [string]$DbSql,
    [string]$WpTar
)

if (-not $DbSql -or -not $WpTar) {
    Write-Host "Usage: .\scripts\restore.ps1 <db-sql-path> <wp-content-tar.gz>" -ForegroundColor Yellow
    exit 2
}

if (Test-Path .env) { Get-Content .env | ForEach-Object { if ($_ -match '=') { $p = $_ -split '='; Set-Item -Path "env:$($p[0])" -Value $p[1] } } }

$db = $env:MYSQL_DATABASE -or $env:WORDPRESS_DB_NAME -or 'wordpress'
$user = $env:MYSQL_USER -or $env:WORDPRESS_DB_USER -or 'root'
$pass = $env:MYSQL_PASSWORD -or $env:WORDPRESS_DB_PASSWORD -or ''

Write-Host "Restoring DB $db from $DbSql"
Get-Content $DbSql -Raw | docker exec -i aps_db sh -c "mysql -u\"$user\" -p\"$pass\" \"$db\""

Write-Host "Restoring wp-content from $WpTar"
tar -C . -xzf $WpTar

Write-Host "Restore completed"
