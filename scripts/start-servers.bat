@echo off
echo =========================================
echo  STARTING AFFILIATE WEBSITE SERVERS
echo =========================================
echo.
echo This will open TWO new windows:
echo   - API Server (port 3003)
echo   - Web Server (port 3002)
echo.
pause

echo.
echo [1/3] Starting API Server...
start "API Server - Port 3003" cmd /k "cd /d %~dp0apps\api && echo Starting API Server... && npm run dev"

echo [2/3] Starting Web Server...
start "Web Server - Port 3000" cmd /k "cd /d %~dp0apps\web && echo Starting Next.js App... && npm run dev"

echo.
echo [3/3] Waiting for servers to start...
echo =========================================
timeout /t 10 /nobreak >nul

echo.
echo ✅ Servers should be ready now!
echo.
echo 🌐 Opening browser...
start http://localhost:3000

echo.
echo =========================================
echo  ALL DONE!
echo =========================================
echo.
echo 📝 URLs:
echo    Website: http://localhost:3000
echo    API:     http://localhost:3003/api/v1
echo.
echo ℹ️  Two windows are running in the background.
echo    DO NOT CLOSE them or the website will stop!
echo.
pause
