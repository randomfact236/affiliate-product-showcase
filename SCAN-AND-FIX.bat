@echo off
:: Enterprise Code Scanner - Scan, Log, Fix, Re-scan Cycle
:: This script automates the perfection cycle for enterprise-grade quality

title Affiliate Website - Enterprise Scanner
color 0B

echo ========================================
echo   ENTERPRISE CODE SCANNER
echo   Scan ^| Log ^| Fix ^| Re-scan
echo ========================================
echo.

:: Check PowerShell
powershell -Command "Get-Host" >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PowerShell is required but not available
    pause
    exit /b 1
)

:: Mode selection
if "%~1"=="--continuous" (
    echo [MODE] Continuous scanning enabled
    goto :continuous
)

if "%~1"=="--scan-only" (
    echo [MODE] Scan only (no fixes)
    goto :scan-only
)

if "%~1"=="--fix-only" (
    echo [MODE] Fix only (no scan)
    goto :fix-only
)

echo Select mode:
echo   [1] Scan and Fix (single pass)
echo   [2] Continuous scanning
echo   [3] Scan only
echo   [4] Fix only
echo   [5] Full perfection cycle (scan-fix-rescan)
echo.
set /p mode="Enter choice (1-5): "

if "%mode%"=="1" goto :scan-and-fix
if "%mode%"=="2" goto :continuous
if "%mode%"=="3" goto :scan-only
if "%mode%"=="4" goto :fix-only
if "%mode%"=="5" goto :full-cycle

echo Invalid choice
goto :end

:scan-and-fix
echo.
echo [1/2] Running enterprise scanner with auto-fix...
powershell -ExecutionPolicy Bypass -File "scripts\enterprise-scanner.ps1" -Fix -MaxRounds 1
echo.
echo [2/2] Applying additional fixes...
powershell -ExecutionPolicy Bypass -File "scripts\auto-fix-issues.ps1"
goto :summary

:continuous
echo.
echo Starting continuous scanner...
echo Press Ctrl+C to stop
echo.
powershell -ExecutionPolicy Bypass -File "scripts\enterprise-scanner.ps1" -Fix -Continuous -MaxRounds 100
goto :end

:scan-only
echo.
echo Running scanner (no fixes)...
powershell -ExecutionPolicy Bypass -File "scripts\enterprise-scanner.ps1" -MaxRounds 1
goto :end

:fix-only
echo.
echo Running automated fixes...
powershell -ExecutionPolicy Bypass -File "scripts\auto-fix-issues.ps1"
goto :end

:full-cycle
echo.
echo ========================================
echo   FULL PERFECTION CYCLE
echo ========================================
echo.
echo [PHASE 1] Initial Scan...
powershell -ExecutionPolicy Bypass -File "scripts\enterprise-scanner.ps1" -MaxRounds 1
echo.

echo [PHASE 2] Applying Fixes...
powershell -ExecutionPolicy Bypass -File "scripts\auto-fix-issues.ps1"
echo.

echo [PHASE 3] Re-scanning to verify...
powershell -ExecutionPolicy Bypass -File "scripts\enterprise-scanner.ps1" -MaxRounds 1
echo.

echo [PHASE 4] Final Verification...
set /p verify="Run one more verification scan? (y/n): "
if /i "%verify%"=="y" (
    powershell -ExecutionPolicy Bypass -File "scripts\enterprise-scanner.ps1" -MaxRounds 1
)
goto :summary

:summary
echo.
echo ========================================
echo   SCAN COMPLETE
echo ========================================
if exist "Scan-report\auto-scan-log.md" (
    echo.
    echo Latest scan log: Scan-report\auto-scan-log.md
    echo.
    echo Recent entries:
    powershell -Command "Get-Content 'Scan-report\auto-scan-log.md' -Tail 20"
)

echo.
echo Options:
echo   [S] Start web server
echo   [R] Run another scan
echo   [V] View full log
echo   [X] Exit
set /p next="Choice: "

if /i "%next%"=="S" goto :start-server
if /i "%next%"=="R" goto :full-cycle
if /i "%next%"=="V" goto :view-log
if /i "%next%"=="X" goto :end

goto :end

:start-server
echo Starting web server...
start "" "START-WEBSITE.bat"
goto :end

:view-log
if exist "Scan-report\auto-scan-log.md" (
    notepad "Scan-report\auto-scan-log.md"
) else (
    echo No scan log found
)
goto :end

:end
echo.
echo Press any key to exit...
pause >nul
