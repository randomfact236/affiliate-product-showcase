@echo off
title Affiliate Website Launcher
color 0B
echo.
echo  ========================================
echo   AFFILIATE WEBSITE LAUNCHER
echo  ========================================
echo.
echo  Starting automatic setup...
echo.

powershell -ExecutionPolicy Bypass -File "START-HERE.ps1"

if errorlevel 1 (
    echo.
    echo  ========================================
    echo   There was an error. 
    echo   Check the messages above.
    echo  ========================================
    echo.
    pause
)
