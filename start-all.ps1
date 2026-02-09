# Start all services for Affiliate Website

Write-Host "🚀 Starting Affiliate Website..." -ForegroundColor Green

# Start API in background
Write-Host "📡 Starting API Server on port 3003..." -ForegroundColor Cyan
$apiJob = Start-Job -ScriptBlock {
    Set-Location $using:PWD\apps\api
    npm run dev 2>&1
}

# Start Web in background  
Write-Host "🌐 Starting Web Server on port 3000..." -ForegroundColor Cyan
$webJob = Start-Job -ScriptBlock {
    Set-Location $using:PWD\apps\web
    npm run dev 2>&1
}

Write-Host ""
Write-Host "⏳ Waiting 15 seconds for servers to start..." -ForegroundColor Yellow

# Progress bar
for ($i = 1; $i -le 15; $i++) {
    Write-Progress -Activity "Starting servers" -Status "$i seconds elapsed" -PercentComplete (($i / 15) * 100)
    Start-Sleep -Seconds 1
}
Write-Progress -Activity "Starting servers" -Completed

Write-Host ""
Write-Host "🔍 Checking if servers are running..." -ForegroundColor Cyan

$apiReady = $false
$webReady = $false

# Try to connect to API
try {
    $apiResponse = Invoke-WebRequest -Uri "http://localhost:3003/api/v1/health" -UseBasicParsing -TimeoutSec 3 -ErrorAction SilentlyContinue
    if ($apiResponse.StatusCode -eq 200) {
        Write-Host "✅ API Server is running on http://localhost:3003" -ForegroundColor Green
        $apiReady = $true
    }
} catch {
    Write-Host "⚠️  API Server is starting (may take more time)..." -ForegroundColor Yellow
}

# Try to connect to Web
try {
    $webResponse = Invoke-WebRequest -Uri "http://localhost:3000" -UseBasicParsing -TimeoutSec 3 -ErrorAction SilentlyContinue
    if ($webResponse.StatusCode -eq 200) {
        Write-Host "✅ Web Server is running on http://localhost:3000" -ForegroundColor Green
        $webReady = $true
    }
} catch {
    Write-Host "⚠️  Web Server is starting (may take more time)..." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SERVICES STARTED" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Open browser automatically
Write-Host "🌐 Opening browser..." -ForegroundColor Green
Start-Process "http://localhost:3000"

Write-Host ""
Write-Host "🔗 URLs:" -ForegroundColor White
Write-Host "   Frontend: http://localhost:3000" -ForegroundColor Yellow
Write-Host "   API:      http://localhost:3003/api/v1/health" -ForegroundColor Yellow
Write-Host ""

if (-not $apiReady -or -not $webReady) {
    Write-Host "⏳ Servers are still warming up..." -ForegroundColor Yellow
    Write-Host "   Wait a few more seconds and refresh the browser" -ForegroundColor Gray
    Write-Host ""
}

Write-Host "📋 Server Logs:" -ForegroundColor White
Write-Host "   (Showing live output - press Ctrl+C to stop viewing logs)" -ForegroundColor Gray
Write-Host ""

# Show logs continuously
while ($true) {
    Start-Sleep -Milliseconds 500
    
    # Show any new output
    $apiOutput = Receive-Job -Id $apiJob.Id -ErrorAction SilentlyContinue
    $webOutput = Receive-Job -Id $webJob.Id -ErrorAction SilentlyContinue
    
    if ($apiOutput) {
        foreach ($line in $apiOutput) {
            Write-Host "[API] $line" -ForegroundColor Blue
        }
    }
    if ($webOutput) {
        foreach ($line in $webOutput) {
            Write-Host "[WEB] $line" -ForegroundColor Magenta
        }
    }
}
