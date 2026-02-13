@echo off
title Quick Fix - ERR_CONNECTION_REFUSED
color 0A
cls

echo.
echo  ========================================
echo    QUICK FIX - ERR_CONNECTION_REFUSED
echo  ========================================
echo.
echo  This will restart all servers cleanly.
echo.

powershell -ExecutionPolicy Bypass -File "QUICK-FIX.ps1"
