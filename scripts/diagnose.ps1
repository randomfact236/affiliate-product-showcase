# Diagnostic script for connection issues

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "AFFILIATE WEBSITE - CONNECTION DIAGNOSTIC" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# 1. Check Docker
Write-Host "1. Checking Docker Containers..." -ForegroundColor Yellow
$containers = docker ps --filter "name=affiliate-website" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" 2>$null
if ($containers) {
    Write-Host $containers -ForegroundColor Green
} else {
    Write-Host "   ❌ No containers running!" -ForegroundColor Red
    Write-Host "   Run: pnpm infra:up" -ForegroundColor Yellow
}
Write-Host ""

# 2. Check if ports are listening
Write-Host "2. Checking Port Status..." -ForegroundColor Yellow
$ports = @(3002, 3003, 5433, 6380)
foreach ($port in $ports) {
    try {
        $conn = Test-NetConnection -ComputerName localhost -Port $port -WarningAction SilentlyContinue -ErrorAction SilentlyContinue
        if ($conn.TcpTestSucceeded) {
            Write-Host "   ✅ Port $port - LISTENING" -ForegroundColor Green
        } else {
            Write-Host "   ❌ Port $port - NOT LISTENING" -ForegroundColor Red
        }
    } catch {
        Write-Host "   ❌ Port $port - ERROR" -ForegroundColor Red
    }
}
Write-Host ""

# 3. Check for Node processes
Write-Host "3. Checking Node.js Processes..." -ForegroundColor Yellow
$nodeProcesses = Get-Process -Name "node" -ErrorAction SilentlyContinue
if ($nodeProcesses) {
    Write-Host "   Found $($nodeProcesses.Count) Node process(es):" -ForegroundColor Green
    foreach ($proc in $nodeProcesses) {
        Write-Host "      PID: $($proc.Id) - $($proc.ProcessName)" -ForegroundColor Gray
    }
} else {
    Write-Host "   ❌ No Node.js processes found!" -ForegroundColor Red
    Write-Host "   You need to start the dev servers:" -ForegroundColor Yellow
    Write-Host "      cd apps/api && npm run dev" -ForegroundColor Gray
    Write-Host "      cd apps/web && npm run dev" -ForegroundColor Gray
}
Write-Host ""

# 4. Check package installations
Write-Host "4. Checking Package Installations..." -ForegroundColor Yellow
if (Test-Path "apps/api/node_modules") {
    Write-Host "   ✅ API dependencies installed" -ForegroundColor Green
} else {
    Write-Host "   ❌ API dependencies MISSING" -ForegroundColor Red
    Write-Host "   Run: cd apps/api && npm install" -ForegroundColor Yellow
}

if (Test-Path "apps/web/node_modules") {
    Write-Host "   ✅ Web dependencies installed" -ForegroundColor Green
} else {
    Write-Host "   ❌ Web dependencies MISSING" -ForegroundColor Red
    Write-Host "   Run: cd apps/web && npm install" -ForegroundColor Yellow
}
Write-Host ""

# 5. Summary and next steps
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "DIAGNOSIS COMPLETE" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Determine the issue
$dockerRunning = docker ps --filter "name=affiliate-website" --format "{{.Names}}" 2>$null
$apiListening = $false
$webListening = $false

try {
    $apiTest = Test-NetConnection -ComputerName localhost -Port 3003 -WarningAction SilentlyContinue -ErrorAction SilentlyContinue
    $apiListening = $apiTest.TcpTestSucceeded
} catch {}

try {
    $webTest = Test-NetConnection -ComputerName localhost -Port 3002 -WarningAction SilentlyContinue -ErrorAction SilentlyContinue
    $webListening = $webTest.TcpTestSucceeded
} catch {}

if (-not $dockerRunning) {
    Write-Host "🚨 ISSUE: Docker containers not running!" -ForegroundColor Red
    Write-Host ""
    Write-Host "FIX:" -ForegroundColor Green
    Write-Host "   pnpm infra:up" -ForegroundColor Yellow
} elseif (-not $apiListening -and -not $webListening) {
    Write-Host "🚨 ISSUE: Dev servers not running!" -ForegroundColor Red
    Write-Host ""
    Write-Host "FIX: Open TWO new terminal windows and run:" -ForegroundColor Green
    Write-Host ""
    Write-Host "Terminal 1 (API):" -ForegroundColor Cyan
    Write-Host "   cd apps/api" -ForegroundColor Yellow
    Write-Host "   npm run dev" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Terminal 2 (Web):" -ForegroundColor Cyan
    Write-Host "   cd apps/web" -ForegroundColor Yellow
    Write-Host "   npm run dev" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Wait for both to show 'ready' message, then refresh browser." -ForegroundColor Gray
} elseif (-not $webListening) {
    Write-Host "⚠️  ISSUE: Web server not running!" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "FIX: Run in new terminal:" -ForegroundColor Green
    Write-Host "   cd apps/web" -ForegroundColor Yellow
    Write-Host "   npm run dev" -ForegroundColor Yellow
} elseif (-not $apiListening) {
    Write-Host "⚠️  ISSUE: API server not running!" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "FIX: Run in new terminal:" -ForegroundColor Green
    Write-Host "   cd apps/api" -ForegroundColor Yellow
    Write-Host "   npm run dev" -ForegroundColor Yellow
} else {
    Write-Host "✅ All services are running!" -ForegroundColor Green
    Write-Host "   Try: http://localhost:3002" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "For more help: See TROUBLESHOOTING.md" -ForegroundColor Gray
