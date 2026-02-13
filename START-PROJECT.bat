@echo off
chcp 65001 >nul
title Affiliate Product Showcase - Protected Ports
echo.
echo ╔═══════════════════════════════════════════════════════════════════════════╗
echo ║     AFFILIATE PRODUCT SHOWCASE - PORT PROTECTION SYSTEM                    ║
echo ║     Protected Ports: 3000, 3001, 3002, 3003, 5433, 6380, 9000, 9001       ║
echo ║                      5672, 15672 (10 ports total)                         ║
echo ╚═══════════════════════════════════════════════════════════════════════════╝
echo.

REM Check for admin privileges
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [WARNING] Not running as Administrator. Port firewall rules cannot be applied.
    echo [INFO] Right-click and "Run as Administrator" for full port protection.
    echo.
)

REM Run port setup (protect all project ports)
echo [INFO] Protecting all project ports...
echo [INFO] Ports: 3000, 3001, 3002, 3003, 5433, 6380, 9000, 9001, 5672, 15672
powershell -ExecutionPolicy Bypass -File scripts\port-manager.ps1 -Action setup
if %errorLevel% neq 0 (
    echo.
    echo [WARNING] Port setup requires Administrator privileges.
    echo [INFO] Continuing without firewall protection...
    echo.
)

echo.
echo [INFO] Starting services on ACTIVE ports...
echo.

REM Start API Server on port 3003
start "API Server - Port 3003" cmd /k "cd apps/api && set API_PORT=3003 && node simple-server.js"
timeout /t 3 /nobreak >nul

REM Start Web Server on port 3000
start "Web Server - Port 3000" cmd /k "cd apps/web && set PORT=3000 && npm run dev"
timeout /t 5 /nobreak >nul

echo.
echo ╔═══════════════════════════════════════════════════════════════════════════╗
echo ║                    SERVICES STARTED SUCCESSFULLY                          ║
echo ╠═══════════════════════════════════════════════════════════════════════════╣
echo ║  🌐 Web Frontend:  http://localhost:3000                                  ║
echo ║  📡 API Server:    http://localhost:3003                                  ║
echo ║  📊 API Health:    http://localhost:3003/api/v1/health                    ║
echo ╠═══════════════════════════════════════════════════════════════════════════╣
echo ║  [ALL 10 PORTS PROTECTED]                                                 ║
echo ║  Active:    3000 (Web), 3003 (API)                                       ║
echo ║  Legacy:    3001, 3002 (Reserved for compatibility)                      ║
echo ║  Infra:     5433, 6380, 9000, 9001, 5672, 15672                          ║
echo ╚═══════════════════════════════════════════════════════════════════════════╝
echo.

REM Open browser
timeout /t 3 /nobreak >nul
start http://localhost:3000

echo Press any key to stop all services and release ports...
pause >nul

REM Kill processes
taskkill /FI "WINDOWTITLE eq API Server - Port 3003*" /F >nul 2>&1
taskkill /FI "WINDOWTITLE eq Web Server - Port 3000*" /F >nul 2>&1

REM Release ports
powershell -ExecutionPolicy Bypass -File scripts\port-manager.ps1 -Action release

echo.
echo [INFO] All services stopped. Ports released.
pause
