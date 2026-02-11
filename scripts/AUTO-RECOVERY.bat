@echo off
:: Auto-Recovery System - Fixes ERR_NETWORK_IO_SUSPENDED and other connection issues
:: Automatically restarts server and repairs network when connection is lost

title Affiliate Website - Auto Recovery System
color 0A

echo ========================================
echo   AUTO-RECOVERY SYSTEM
echo   Fixes: ERR_NETWORK_IO_SUSPENDED
echo ========================================
echo.
echo This will:
echo   1. Check if server is running
echo   2. Restart if needed
echo   3. Monitor continuously
echo   4. Auto-repair on failures
echo.
echo Press any key to start...
pause >nul
cls

:: Check PowerShell
powershell -Command "Get-Host" >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PowerShell is required
    pause
    exit /b 1
)

:: Menu
echo.
echo Select mode:
echo.
echo   [1] FULL RECOVERY NOW ^(restart + monitor^)
echo   [2] QUICK FIX ^(just restart server^)
echo   [3] NETWORK REPAIR ONLY
echo   [4] BACKGROUND MONITOR ^(hidden window^)
echo   [5] STOP MONITORING
echo.
set /p mode="Choice (1-5): "

if "%mode%"=="1" goto :full-recovery
if "%mode%"=="2" goto :quick-fix
if "%mode%"=="3" goto :network-repair
if "%mode%"=="4" goto :background-monitor
if "%mode%"=="5" goto :stop-monitor
goto :end

:full-recovery
echo.
echo [MODE] Full Recovery + Continuous Monitoring
echo.
powershell -ExecutionPolicy Bypass -File "scripts\auto-recovery-system.ps1" -Monitor -CheckInterval 10
goto :end

:quick-fix
echo.
echo [MODE] Quick Fix - Restarting server...
echo.
echo Step 1: Stopping existing processes...
taskkill /f /im node.exe 2>nul
timeout /t 2 /nobreak >nul
echo.
echo Step 2: Clearing cache...
if exist "apps\web\.next" rmdir /s /q "apps\web\.next"
echo.
echo Step 3: Starting server...
start "Web Server" cmd /k "cd apps\web && npm run dev"
timeout /t 5 /nobreak >nul
echo.
echo Step 4: Opening browser...
start http://localhost:3000
echo.
echo ========================================
echo   ✅ SERVER RESTARTED
echo   http://localhost:3000
echo ========================================
echo.
echo The server is running in a new window.
echo.
pause
goto :end

:network-repair
echo.
echo [MODE] Network Repair Only
echo.
echo Repairing network stack...
echo.
ipconfig /release
echo   - IP released
timeout /t 1 /nobreak >nul
ipconfig /renew
echo   - IP renewed
timeout /t 1 /nobreak >nul
ipconfig /flushdns
echo   - DNS flushed
echo.
echo ========================================
echo   ✅ NETWORK REPAIRED
echo ========================================
echo.
pause
goto :end

:background-monitor
echo.
echo [MODE] Background Monitor
echo.
echo Starting hidden monitor...
echo Check logs at: Scan-report\recovery-log.md
echo.
start /min "" powershell -WindowStyle Hidden -ExecutionPolicy Bypass -File "scripts\auto-recovery-system.ps1" -Monitor -CheckInterval 15
echo Monitor started in background.
echo.
pause
goto :end

:stop-monitor
echo.
echo [MODE] Stop All Monitoring
echo.
echo Stopping all Node processes...
taskkill /f /im node.exe 2>nul
echo Stopped.
echo.
pause
goto :end

:end
