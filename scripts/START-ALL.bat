@echo off
echo ============================================
echo  Affiliate Platform - Start All Services
echo ============================================
echo.
echo Starting services in separate windows...
echo.

:: Start API Server
echo [1/2] Starting API Server on port 3003...
start "API Server" cmd /c "cd /d "%~dp0apps/api" && node simple-server.js"
timeout /t 3 /nobreak >nul

:: Start Frontend
echo [2/2] Starting Frontend on port 3000...
start "Frontend" cmd /c "cd /d "%~dp0apps/web" && npm run dev -- --port 3000"
timeout /t 5 /nobreak >nul

echo.
echo ============================================
echo  Services Starting...
echo ============================================
echo.
echo  Frontend:    http://localhost:3000
echo  API:         http://localhost:3003
echo  Admin:       http://localhost:3000/admin
echo.
echo  Press any key to view status...
pause >nul

:: Check status
curl -s http://localhost:3003/api/v1/health >nul 2>&1
if %errorlevel% == 0 (
    echo [OK] API Server is running
) else (
    echo [WAIT] API Server is still starting...
)

curl -s http://localhost:3000 >nul 2>&1
if %errorlevel% == 0 (
    echo [OK] Frontend is running
) else (
    echo [WAIT] Frontend is still compiling (may take 1-2 minutes)...
)

echo.
echo Close the service windows to stop.
pause
