@echo off
chcp 65001 >nul
echo ========================================
echo   Affiliate Product Showcase
echo   Connection Fix + Auto-Start
echo ========================================
echo.
echo This script will:
echo  1. Check and start Docker containers
echo  2. Fix any port conflicts
echo  3. Start the API server (port 3003)
echo  4. Start the frontend (port 3000)
echo  5. Verify all connections
echo.
echo Press any key to continue...
pause >nul

powershell -ExecutionPolicy Bypass -File "%~dp0FIX-CONNECTION-ISSUE.ps1"

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Script failed with error code %errorlevel%
    pause
    exit /b %errorlevel%
)
