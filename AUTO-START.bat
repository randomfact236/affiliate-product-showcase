@echo off
title Affiliate Website - Auto Start (Fixing Connection)
color 0B
cls

echo.
echo  ========================================
echo    AFFILIATE WEBSITE - AUTO START
echo    Fixing: ERR_CONNECTION_REFUSED
echo  ========================================
echo.

powershell -ExecutionPolicy Bypass -File "AUTO-START.ps1"

echo.
echo  ========================================
pause
