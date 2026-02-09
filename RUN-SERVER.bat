@echo off
title Web Server Launcher
cls

echo ========================================
echo   WEB SERVER LAUNCHER
echo ========================================
echo.
echo This window will show the server output.
echo Look for "Ready in X.Xs" message.
echo.
echo Starting in 3 seconds...
timeout /t 3 /nobreak >nul

powershell -ExecutionPolicy Bypass -File "scripts\launch-server.ps1" -OpenBrowser

echo.
echo ========================================
echo Server stopped. Press any key to exit.
echo ========================================
pause >nul
