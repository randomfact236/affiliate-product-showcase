# scripts/port-check.ps1 - Check port availability and locks

$LockDir = ".port-locks"
$Ports = @(3002, 3003, 5433, 6380, 9200)

Write-Host "Checking port status for Affiliate Website..." -ForegroundColor Cyan
Write-Host ""

foreach ($Port in $Ports) {
    $Status = "Available"
    $Color = "Green"
    
    # Check if port is in use
    try {
        $Connection = Test-NetConnection -ComputerName localhost -Port $Port -WarningAction SilentlyContinue
        if ($Connection.TcpTestSucceeded) {
            $TcpConnection = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | Select-Object -First 1
            if ($TcpConnection) {
                $Process = Get-Process -Id $TcpConnection.OwningProcess -ErrorAction SilentlyContinue
                $Status = "IN USE by $($Process.ProcessName) (PID: $($Process.Id))"
                $Color = "Yellow"
            }
        }
    } catch {}
    
    Write-Host "Port $Port : $Status" -ForegroundColor $Color
}

Write-Host ""
Write-Host "Lock files in $LockDir :" -ForegroundColor Cyan
$LockFiles = Get-ChildItem "$LockDir\*.pid" -ErrorAction SilentlyContinue
if ($LockFiles) {
    $LockFiles | ForEach-Object { Write-Host "  - $($_.Name)" }
} else {
    Write-Host "  (none)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "Default Ports for this project:" -ForegroundColor Cyan
Write-Host "  Web (Next.js):  3002" -ForegroundColor Green
Write-Host "  API (NestJS):   3003" -ForegroundColor Green
Write-Host "  PostgreSQL:     5433" -ForegroundColor Green
Write-Host "  Redis:          6380" -ForegroundColor Green
