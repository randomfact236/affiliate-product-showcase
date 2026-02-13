#requires -RunAsAdministrator
<#
.SYNOPSIS
    Port Manager for Affiliate Product Showcase
    Reserves ports: 3000, 3001, 3002, 3003, 5433, 6380, 9000, 9001, 5672, 15672
    exclusively for this project and blocks other projects from using them.

.DESCRIPTION
    - Protects ports: 3000, 3001, 3002, 3003, 5433, 6380, 9000, 9001, 5672, 15672
    - Creates Windows Firewall rules to block external access
    - Prevents other projects from using these ports
    - Validates no port conflicts exist

.PARAMETER Action
    setup, check, reserve, release, or firewall

.EXAMPLE
    .\scripts\port-manager.ps1 -Action setup
#>

param(
    [Parameter(Mandatory = $true)]
    [ValidateSet("setup", "check", "reserve", "release", "firewall", "verify")]
    [string]$Action,
    
    [switch]$Force
)

# Configuration
$ProjectName = "affiliate-product-showcase"
$ProjectId = "aps-2026"
$ConfigFile = ".project-ports.config.json"
$LockDir = ".port-locks"

# All Ports Reserved by This Project (including legacy)
$StandardPorts = @(
    @{ Key = "web"; Port = 3000; Name = "Next.js Web (Active)"; Protocol = "TCP"; Required = $true },
    @{ Key = "api_legacy"; Port = 3001; Name = "API Server (Legacy)"; Protocol = "TCP"; Required = $false },
    @{ Key = "web_legacy"; Port = 3002; Name = "Web Server (Legacy)"; Protocol = "TCP"; Required = $false },
    @{ Key = "api"; Port = 3003; Name = "API Server (Active)"; Protocol = "TCP"; Required = $true },
    @{ Key = "postgres"; Port = 5433; Name = "PostgreSQL"; Protocol = "TCP"; Required = $true },
    @{ Key = "redis"; Port = 6380; Name = "Redis Cache"; Protocol = "TCP"; Required = $true },
    @{ Key = "minio_api"; Port = 9000; Name = "MinIO API"; Protocol = "TCP"; Required = $false },
    @{ Key = "minio_console"; Port = 9001; Name = "MinIO Console"; Protocol = "TCP"; Required = $false },
    @{ Key = "rabbitmq_amqp"; Port = 5672; Name = "RabbitMQ AMQP"; Protocol = "TCP"; Required = $false },
    @{ Key = "rabbitmq_mgmt"; Port = 15672; Name = "RabbitMQ Mgmt"; Protocol = "TCP"; Required = $false }
)

# Colors
$Red = "Red"
$Green = "Green"
$Yellow = "Yellow"
$Cyan = "Cyan"

function Write-Status($Message, $Type = "Info") {
    $color = switch ($Type) {
        "Success" { $Green }
        "Error" { $Red }
        "Warning" { $Yellow }
        default { $Cyan }
    }
    Write-Host "[$Type] $Message" -ForegroundColor $color
}

function Test-PortInUse($Port) {
    try {
        $connection = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($connection) {
            $process = Get-Process -Id $connection.OwningProcess -ErrorAction SilentlyContinue
            return @{
                InUse = $true
                Process = $process.ProcessName
                PID = $process.Id
                Path = $process.Path
                IsProjectProcess = ($process.Path -like "*$ProjectName*")
            }
        }
    } catch {}
    return @{ InUse = $false }
}

function Add-FirewallRule($Port, $Name, $Protocol = "TCP") {
    $ruleName = "$ProjectId-$Name-$Port"
    
    # Remove existing rule if exists
    Remove-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue
    Remove-NetFirewallRule -DisplayName "$ruleName-BLOCK-EXTERNAL" -ErrorAction SilentlyContinue
    
    # Create inbound rule - ALLOW local only
    New-NetFirewallRule `
        -DisplayName $ruleName `
        -Direction Inbound `
        -LocalPort $Port `
        -Protocol $Protocol `
        -Action Allow `
        -Profile Private `
        -LocalAddress @("127.0.0.1", "::1") `
        -Description "Port $Port reserved for $ProjectName - $Name (LOCAL ONLY)"
    
    # Create rule to block all other incoming connections to this port
    New-NetFirewallRule `
        -DisplayName "$ruleName-BLOCK-EXTERNAL" `
        -Direction Inbound `
        -LocalPort $Port `
        -Protocol $Protocol `
        -Action Block `
        -Profile @("Domain", "Public") `
        -Description "Block external access to port $Port for $ProjectName"
    
    Write-Status "Firewall rule created for port $Port ($Name)" "Success"
}

function Remove-FirewallRules($Port) {
    Get-NetFirewallRule | Where-Object { 
        $_.DisplayName -like "$ProjectId*" -and 
        $_.DisplayName -like "*$Port*"
    } | Remove-NetFirewallRule -ErrorAction SilentlyContinue
}

function Invoke-PortSetup {
    Write-Status "Setting up port protection for $ProjectName..." "Info"
    Write-Status "Protecting ALL project ports: 3000, 3001, 3002, 3003, 5433, 6380, 9000, 9001, 5672, 15672" "Info"
    
    # Create lock directory
    if (-not (Test-Path $LockDir)) {
        New-Item -ItemType Directory -Path $LockDir -Force | Out-Null
    }
    
    Write-Status "Checking for port conflicts..." "Info"
    $conflicts = @()
    
    foreach ($p in $StandardPorts) {
        $status = Test-PortInUse $p.Port
        if ($status.InUse -and -not $status.IsProjectProcess) {
            $conflicts += [PSCustomObject]@{
                Port = $p.Port
                Name = $p.Name
                Process = $status.Process
                PID = $status.PID
                Path = $status.Path
            }
        }
    }
    
    if ($conflicts.Count -gt 0) {
        Write-Status "PORT CONFLICTS DETECTED:" "Error"
        Write-Host ""
        $conflicts | Format-Table -AutoSize | Out-Host
        Write-Host ""
        Write-Status "These ports are RESERVED for $ProjectName only!" "Warning"
        
        if (-not $Force) {
            Write-Status "Use -Force to kill conflicting processes" "Warning"
            Write-Status "Or close other applications using these ports manually" "Info"
            return
        }
        
        Write-Status "Force mode enabled - stopping conflicting processes..." "Warning"
        foreach ($c in $conflicts) {
            try {
                Stop-Process -Id $c.PID -Force -ErrorAction Stop
                Write-Status "Stopped process $($c.Process) (PID: $($c.PID))" "Success"
            }
            catch {
                Write-Status "Failed to stop process $($c.Process): $_" "Error"
            }
        }
    }
    
    Write-Status "Creating Windows Firewall rules..." "Info"
    foreach ($p in $StandardPorts) {
        Add-FirewallRule $p.Port $p.Key $p.Protocol
    }
    
    Write-Status "Creating port reservation locks..." "Info"
    foreach ($p in $StandardPorts) {
        $lockFile = "$LockDir\$($p.Key).$($p.Port).pid"
        @{
            project = $ProjectName
            projectId = $ProjectId
            port = $p.Port
            service = $p.Key
            reservedAt = (Get-Date).ToString("o")
            standardPort = $true
            legacy = ($p.Port -eq 3001 -or $p.Port -eq 3002)
        } | ConvertTo-Json | Out-File $lockFile -Force
    }
    
    Write-Status "Port protection complete!" "Success"
    Write-Status "PORTS RESERVED exclusively for this project:" "Success"
    Write-Host ""
    Write-Host "  ACTIVE ports:" -ForegroundColor $Green
    Write-Host "    3000 (Web), 3003 (API)" -ForegroundColor $Green
    Write-Host ""
    Write-Host "  LEGACY ports (reserved to prevent conflicts):" -ForegroundColor $Yellow
    Write-Host "    3001 (API Legacy), 3002 (Web Legacy)" -ForegroundColor $Yellow
    Write-Host ""
    Write-Host "  INFRASTRUCTURE ports:" -ForegroundColor $Cyan
    Write-Host "    5433 (PostgreSQL), 6380 (Redis)" -ForegroundColor $Cyan
    Write-Host "    9000 (MinIO API), 9001 (MinIO Console)" -ForegroundColor $Cyan
    Write-Host "    5672 (RabbitMQ), 15672 (RabbitMQ Mgmt)" -ForegroundColor $Cyan
}

function Invoke-PortCheck {
    Write-Status "Checking port status for $ProjectName..." "Info"
    Write-Status "Protected Ports: 3000, 3001, 3002, 3003, 5433, 6380, 9000, 9001, 5672, 15672" "Info"
    Write-Status ""
    
    foreach ($p in $StandardPorts) {
        $status = Test-PortInUse $p.Port
        $color = if ($status.InUse) { 
            if ($status.IsProjectProcess) { $Green } else { $Red }
        } else { $Yellow }
        
        $legacyFlag = if ($p.Port -eq 3001 -or $p.Port -eq 3002) { " [LEGACY]" } else { "" }
        $statusText = if ($status.InUse) {
            if ($status.IsProjectProcess) { 
                "✓ RESERVED by Project (PID: $($status.PID))" 
            } else { 
                "✗ CONFLICT - $($status.Process) (PID: $($status.PID))" 
            }
        } else { 
            "○ Available" 
        }
        
        Write-Host "$($p.Port.ToString().PadRight(6)) | $($p.Key.PadRight(15))$legacyFlag | $statusText" -ForegroundColor $color
    }
}

function Invoke-PortReserve {
    Write-Status "Reserving all project ports..." "Info"
    
    $reservedCount = 0
    
    foreach ($p in $StandardPorts | Where-Object { $_.Required -or $_.Port -eq 3001 -or $_.Port -eq 3002 }) {
        $lockFile = "$LockDir\$($p.Key).$($p.Port).pid"
        
        @{
            project = $ProjectName
            projectId = $ProjectId
            port = $p.Port
            service = $p.Key
            reservedAt = (Get-Date).ToString("o")
            pid = $PID
            standardPort = $true
            legacy = ($p.Port -eq 3001 -or $p.Port -eq 3002)
        } | ConvertTo-Json | Out-File $lockFile -Force
        
        $reservedCount++
        Write-Status "Reserved port $($p.Port) for $($p.Key)" "Success"
    }
    
    Write-Status "Reserved $reservedCount ports" "Success"
}

function Invoke-PortRelease {
    Write-Status "Releasing port reservations..." "Info"
    
    if (Test-Path $LockDir) {
        Remove-Item "$LockDir\*.pid" -Force -ErrorAction SilentlyContinue
        Write-Status "Removed port lock files" "Success"
    }
    
    # Remove firewall rules
    Get-NetFirewallRule | Where-Object { $_.DisplayName -like "$ProjectId*" } | Remove-NetFirewallRule -ErrorAction SilentlyContinue
    Write-Status "Removed firewall rules" "Success"
    
    Write-Status "Ports released - other projects can now use them" "Success"
}

function Invoke-FirewallSetup {
    Write-Status "Configuring Windows Firewall for port protection..." "Info"
    
    # Remove old rules
    Get-NetFirewallRule | Where-Object { 
        $_.DisplayName -like "$ProjectId*" 
    } | Remove-NetFirewallRule -ErrorAction SilentlyContinue
    
    Write-Status "Creating firewall rules..." "Info"
    foreach ($p in $StandardPorts) {
        Add-FirewallRule $p.Port $p.Key $p.Protocol
    }
    
    Write-Status "Firewall configured - all ports isolated" "Success"
}

function Invoke-PortVerify {
    Write-Status "Verifying port configuration..." "Info"
    
    $issues = @()
    
    # Check for duplicate port definitions
    $portGroups = $StandardPorts | Group-Object Port
    $duplicates = $portGroups | Where-Object { $_.Count -gt 1 }
    if ($duplicates) {
        $issues += "Duplicate port assignments found!"
    }
    
    # Verify all expected ports are present
    $expectedPorts = @(3000, 3001, 3002, 3003, 5433, 6380, 9000, 9001, 5672, 15672)
    $actualPorts = $StandardPorts | ForEach-Object { $_.Port }
    $missing = $expectedPorts | Where-Object { $_ -notin $actualPorts }
    if ($missing) {
        $issues += "Missing ports: $($missing -join ', ')"
    }
    
    if ($issues.Count -eq 0) {
        Write-Status "All port configurations are valid!" "Success"
        Write-Status "Protected ports:" "Info"
        Write-Host "  • 3000 (Web), 3001 (API Legacy), 3002 (Web Legacy), 3003 (API)" -ForegroundColor $Green
        Write-Host "  • 5433 (PostgreSQL), 6380 (Redis)" -ForegroundColor $Green
        Write-Host "  • 9000/9001 (MinIO), 5672/15672 (RabbitMQ)" -ForegroundColor $Green
        return $true
    } else {
        Write-Status "Configuration issues found:" "Error"
        $issues | ForEach-Object { Write-Status $_ "Error" }
        return $false
    }
}

# Main execution
switch ($Action) {
    "setup" { Invoke-PortSetup }
    "check" { Invoke-PortCheck }
    "reserve" { Invoke-PortReserve }
    "release" { Invoke-PortRelease }
    "firewall" { Invoke-FirewallSetup }
    "verify" { Invoke-PortVerify }
}

Write-Status "Port manager operation completed: $Action" "Success"
