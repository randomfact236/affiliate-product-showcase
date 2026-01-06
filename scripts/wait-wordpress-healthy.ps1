$max = 30
$i = 0
while ($i -lt $max) {
    $s = docker inspect --format='{{json .State.Health.Status}}' aps_wordpress 2>$null
    Write-Host "attempt $i $s"
    if ($s -eq '"healthy"') { exit 0 }
    Start-Sleep -Seconds 5
    $i = $i + 1
}
Write-Host 'timeout waiting for healthy'
exit 2
