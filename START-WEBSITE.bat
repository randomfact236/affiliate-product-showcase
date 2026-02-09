@echo off
title Affiliate Website - Smart Launcher
color 0B
cls

echo.
echo  ========================================
echo    AFFILIATE WEBSITE
necho    Smart Launcher
echo  ========================================
echo.
echo  This will:
echo   1. Start the web server
echo   2. Wait for it to be ready
echo   3. Open your browser automatically
echo.
echo  ----------------------------------------
echo.

powershell -ExecutionPolicy Bypass -File "scripts\smart-launcher.ps1"

echo.
echo  ========================================
pause
