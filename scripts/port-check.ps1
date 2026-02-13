# scripts/port-check.ps1 - Check port availability for Affiliate Product Showcase
# Protected Ports: 3000, 3001, 3002, 3003, 5433, 6380, 9000, 9001, 5672, 15672

$LockDir = ".port-locks"

# All ports reserved by this project (including legacy)
$Ports = @(
    @{ Port = 3000; Name = "Web (Next.js Active)"; Required = $true },
    @{ Port = 3001; Name = "API (Legacy)"; Required = $false },
    @{ Port = 3002; Name = "Web (Legacy)"; Required = $false },
    @{ Port = 3003; Name = "API Server (Active)"; Required = $true },
    @{ Port = 5433; Name = "PostgreSQL"; Required = $true },
    @{ Port = 6380; Name = "Redis"; Required = $true },
    @{ Port = 9000; Name = "MinIO API"; Required = $false },
    @{ Port = 9001; Name = "MinIO Console"; Required = $false },
    @{ Port = 5672; Name = "RabbitMQ AMQP"; Required = $false },
    @{ Port = 15672; Name = "RabbitMQ Mgmt"; Required = $false }
)

Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  AFFILIATE PRODUCT SHOWCASE - Port Protection Status" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "Protected Ports for This Project:" -ForegroundColor Yellow
Write-Host "  ACTIVE:    3000 (Web), 3003 (API)" -ForegroundColor White
Write-Host "  LEGACY:    3001 (API), 3002 (Web) - Reserved for compatibility" -ForegroundColor Gray
Write-Host "  DATABASE:  5433 (PostgreSQL), 6380 (Redis)" -ForegroundColor White
Write-Host "  STORAGE:   9000/9001 (MinIO)" -ForegroundColor White
Write-Host "  QUEUE:     5672/15672 (RabbitMQ)" -ForegroundColor White
Write-Host ""

foreach ($p in $Ports) {
    $Status = "Available"
    $Color = "Green"
    $Owner = ""
    
    # Check if port is in use
    try {
        $Connection = Test-NetConnection -ComputerName localhost -Port $p.Port -WarningAction SilentlyContinue
        if ($Connection.TcpTestSucceeded) {
            $TcpConnection = Get-NetTCPConnection -LocalPort $p.Port -ErrorAction SilentlyContinue | Select-Object -First 1
            if ($TcpConnection) {
                $Process = Get-Process -Id $TcpConnection.OwningProcess -ErrorAction SilentlyContinue
                $Status = "IN USE by $($Process.ProcessName) (PID: $($Process.Id))"
                $Color = "Yellow"
                
                # Check if it's our project
                if (Test-Path "$LockDir\*.$($p.Port).pid") {
                    $LockContent = Get-Content "$LockDir\*.$($p.Port).pid" -ErrorAction SilentlyContinue | ConvertFrom-Json
                    if ($LockContent.projectId -eq "aps-2026") {
                        $Status = "✓ RESERVED by this project (PID: $($Process.Id))"
                        $Color = "Green"
                    }
                }
            }
        }
    } catch {}
    
    $RequiredFlag = if ($p.Required) { " [REQ]" } else { "" }
    $LegacyFlag = if ($p.Port -eq 3001 -or $p.Port -eq 3002) { " [LEGACY]" } else { "" }
    Write-Host "$($p.Port.ToString().PadRight(6)) | $($p.Name.PadRight(25))$RequiredFlag$LegacyFlag | " -NoNewline
    Write-Host $Status -ForegroundColor $Color
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "Lock files in $LockDir :" -ForegroundColor Cyan
$LockFiles = Get-ChildItem "$LockDir\*.pid" -ErrorAction SilentlyContinue
if ($LockFiles) {
    $LockFiles | ForEach-Object { 
        $content = Get-Content $_.FullName -ErrorAction SilentlyContinue | ConvertFrom-Json
        $legacyFlag = if ($content.legacy) { " [LEGACY]" } else { "" }
        Write-Host "  ✓ $($_.Name) - Reserved by $($content.project)$legacyFlag" -ForegroundColor Green
    }
} else {
    Write-Host "  (none)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "Note: Run .\scripts\port-manager.ps1 -Action setup (as Admin)" -ForegroundColor Yellow
Write-Host "      to protect these ports for this project only" -ForegroundColor Yellow
Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Cyan
