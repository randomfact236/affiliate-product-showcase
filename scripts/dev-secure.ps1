# scripts/dev-secure.ps1 - Windows version of secure development startup
param(
    [Parameter(Mandatory=$true)]
    [ValidateSet('api', 'web')]
    [string]$App,
    
    [Parameter(Mandatory=$false)]
    [int]$Port = 0
)

# Set default ports if not provided
if ($Port -eq 0) {
    $Port = if ($App -eq 'api') { 3003 } else { 3002 }
}

$LockDir = ".port-locks"
$LockFile = "$LockDir\$App.pid"

Write-Host "Starting $App on port $Port with port protection..."

# Create lock directory
New-Item -ItemType Directory -Path $LockDir -Force | Out-Null

# Check for existing lock
if (Test-Path $LockFile) {
    $OldPid = Get-Content $LockFile | Select-Object -First 1
    if (Get-Process -Id $OldPid -ErrorAction SilentlyContinue) {
        Write-Host "Port $Port is locked by process $OldPid"
        exit 1
    } else {
        Remove-Item $LockFile -Force
    }
}

# Check if port is in use
try {
    $Connection = Test-NetConnection -ComputerName localhost -Port $Port -WarningAction SilentlyContinue
    if ($Connection.TcpTestSucceeded) {
        Write-Host "Port $Port is already in use"
        netstat -ano | findstr ":$Port"
        exit 1
    }
} catch {}

# Create lock file
$PID | Set-Content $LockFile
"$(Get-Date): Started by $env:USERNAME" | Add-Content $LockFile

Write-Host "Port $Port locked. Starting dev server..."

# Start dev server
if ($App -eq 'api') {
    Set-Location apps/api
    npm run dev
} else {
    Set-Location apps/web
    npm run dev
}

# Cleanup on exit
Remove-Item $LockFile -Force -ErrorAction SilentlyContinue
Write-Host "Port $Port lock released."
