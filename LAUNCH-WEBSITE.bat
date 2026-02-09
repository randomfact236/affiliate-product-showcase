@echo off
title Affiliate Website Launcher
color 0A
cls

echo.
echo  ========================================
echo   AFFILIATE WEBSITE LAUNCHER
echo  ========================================
echo.
echo  This will automatically:
echo   - Fix any issues
echo   - Start the web server
echo   - Open your browser
echo.
echo  Press any key to start...
pause >nul

:: Run the PowerShell script
powershell -ExecutionPolicy Bypass -File "scripts\workflow-auto-start.ps1" -MaxAttempts 10

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo  ========================================
    echo   Launch failed. Check errors above.
    echo  ========================================
    pause
)
