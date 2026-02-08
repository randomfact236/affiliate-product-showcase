# scripts/start-dev.ps1 - Start all development services

Write-Host "🚀 Starting Affiliate Website Development Environment..." -ForegroundColor Green
Write-Host ""

# Step 1: Check Docker
Write-Host "Step 1: Checking Docker containers..." -ForegroundColor Cyan
docker ps --filter "name=affiliate-website" | findstr "affiliate-website" > $null
if ($LASTEXITCODE -ne 0) {
    Write-Host "  Containers not running. Starting infrastructure..." -ForegroundColor Yellow
    docker-compose -p affiliate-website -f docker/docker-compose.yml up -d
    Start-Sleep -Seconds 5
} else {
    Write-Host "  ✅ Containers already running" -ForegroundColor Green
}

# Step 2: Check port locks
Write-Host ""
Write-Host "Step 2: Checking port locks..." -ForegroundColor Cyan
if (Test-Path ".port-locks/api.pid") {
    Write-Host "  ⚠️  API port (3003) is locked. Is it already running?" -ForegroundColor Yellow
} else {
    Write-Host "  ✅ API port available" -ForegroundColor Green
}

if (Test-Path ".port-locks/web.pid") {
    Write-Host "  ⚠️  Web port (3002) is locked. Is it already running?" -ForegroundColor Yellow
} else {
    Write-Host "  ✅ Web port available" -ForegroundColor Green
}

# Step 3: Display instructions
Write-Host ""
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "Next steps:" -ForegroundColor Green
Write-Host ""
Write-Host "1. Open TWO new terminal windows" -ForegroundColor Yellow
Write-Host ""
Write-Host "2. Terminal 1 - Start API:" -ForegroundColor White
Write-Host "   cd apps/api" -ForegroundColor Gray
Write-Host "   npm run dev" -ForegroundColor Gray
Write-Host ""
Write-Host "3. Terminal 2 - Start Web:" -ForegroundColor White  
Write-Host "   cd apps/web" -ForegroundColor Gray
Write-Host "   npm run dev" -ForegroundColor Gray
Write-Host ""
Write-Host "4. Open in browser:" -ForegroundColor White
Write-Host "   http://localhost:3002" -ForegroundColor Green
Write-Host "   http://localhost:3003/api/v1/health" -ForegroundColor Green
Write-Host "==============================================" -ForegroundColor Cyan
