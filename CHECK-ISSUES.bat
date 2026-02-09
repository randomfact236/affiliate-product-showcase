@echo off
title Issue Checker
color 0B
cls

echo.
echo  ========================================
echo    ISSUE CHECKER and RESOLVER
echo  ========================================
echo.

powershell -ExecutionPolicy Bypass -File "scripts\check-and-resolve-issues.ps1"

echo.
echo  ========================================
pause
