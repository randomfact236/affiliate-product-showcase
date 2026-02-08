@echo off
echo =========================================
echo  AFFILIATE WEBSITE - AUTO START
echo =========================================
echo.

:: Check if Docker is running
docker ps >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker is not running!
    echo Please start Docker Desktop first.
    pause
    exit /b 1
)

:: Check if containers are running
docker ps --filter "name=affiliate-website" --format "{{.Names}}" | findstr "affiliate-website" >nul
if errorlevel 1 (
    echo 🐳 Starting Docker containers...
    docker-compose -p affiliate-website -f docker\docker-compose.yml up -d
    timeout /t 5 /nobreak >nul
) else (
    echo ✅ Docker containers already running
)

echo.
echo 📡 Starting API Server (Port 3003)...
start "🔵 API Server - http://localhost:3003" cmd /k "cd /d %~dp0apps/api && title API Server && echo. && echo 🔵 API Server Starting... && echo URL: http://localhost:3003/api/v1/health && echo. && node simple-server.js"

echo 📡 Starting Web Server (Port 3002)...
start "🟣 Web Server - http://localhost:3002" cmd /k "cd /d %~dp0apps/web && title Web Server && echo. && echo 🟣 Web Server Starting... && echo URL: http://localhost:3002 && echo. && node simple-server.js"

echo.
echo [3/3] Waiting 5 seconds for servers to start...
timeout /t 5 /nobreak >nul

echo.
echo 🌐 Opening website in browser...
start http://localhost:3002

echo.
echo =========================================
echo  ✅ WEBSITE IS RUNNING!
echo =========================================
echo.
echo 📝 Access your website at:
echo    http://localhost:3002
echo.
echo ℹ️  Keep the two windows open!
echo    Closing them will stop the website.
echo.
pause
