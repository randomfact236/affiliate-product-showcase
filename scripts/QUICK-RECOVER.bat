@echo off
:: One-Click Recovery for ERR_NETWORK_IO_SUSPENDED
:: Restarts server and opens browser immediately

title Quick Recovery - Affiliate Website
color 0A

echo ========================================
echo   QUICK RECOVERY
echo ========================================
echo.

:: Step 1: Stop existing processes
echo [1/4] Stopping existing processes...
taskkill /f /im node.exe 2>nul
timeout /t 2 /nobreak >nul
echo     Done.

:: Step 2: Clear cache
echo [2/4] Clearing cache...
if exist "apps\web\.next" rmdir /s /q "apps\web\.next" 2>nul
echo     Done.

:: Step 3: Start server
echo [3/4] Starting server...
start "Web Server" cmd /k "cd /d %~dp0apps\web && echo Starting Next.js server... && npm run dev"
timeout /t 8 /nobreak >nul
echo     Done.

:: Step 4: Open browser
echo [4/4] Opening browser...
start http://localhost:3000
echo     Done.

echo.
echo ========================================
echo   ✅ RECOVERY COMPLETE
echo   http://localhost:3000
echo ========================================
echo.
echo The server is running in a new window.
echo.
echo Options:
echo   [R] Run auto-monitor
echo   [X] Exit
set /p choice="Choice: "

if /i "%choice%"=="R" (
    start "" AUTO-RECOVERY.bat
)

exit
