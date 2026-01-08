Write-Host "Seeding database (PowerShell)"

$dbHost = $env:DB_HOST -or '127.0.0.1'
$dbPort = $env:DB_PORT -or '3306'
$dbUser = $env:DB_USER -or 'wp'
$dbPass = $env:DB_PASS -or 'wp'
$dbName = $env:DB_NAME -or 'wordpress'

if (Test-Path "tests\db-seed.php") {
  php tests\db-seed.php
  Write-Host "Seeder script executed"
  exit 0
}

if (Get-Command wp -ErrorAction SilentlyContinue) {
  if ((wp option get aps_test_seed --path=.) -ne $null) {
    Write-Host "aps_test_seed already present"
    exit 0
  }
  wp option add aps_test_seed 1 --skip-plugins --skip-themes --path=. || Write-Host "wp option add returned non-zero"
  Write-Host "Seeded via WP-CLI"
  exit 0
}

if (Get-Command mysql -ErrorAction SilentlyContinue) {
  $sql = "INSERT IGNORE INTO wp_options (option_name, option_value, autoload) VALUES ('aps_test_seed', '1', 'no');"
  cmd.exe /c "mysql -h $dbHost -P $dbPort -u $dbUser -p$dbPass $dbName -e \"$sql\""
  Write-Host "Seeded via mysql client"
  exit 0
}

Write-Error "No method available to seed the DB (php, wp, or mysql required)"
exit 2
