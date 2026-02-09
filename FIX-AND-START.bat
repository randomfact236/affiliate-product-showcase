@echo off
title Affiliate Website - Fix and Start
color 0A
cls

echo.
echo  ========================================
echo    FIX AND START WEB SERVER
echo  ========================================
echo.
echo  This will:
echo   1. Stop any stuck processes
echo   2. Fix any issues automatically
echo   3. Start the web server
echo   4. Open your browser
echo.
echo  ----------------------------------------
echo.

:: Step 1: Kill any existing node processes
echo [1/4] Stopping any existing processes...
taskkill /f /im node.exe 2>nul
timeout /t 2 /nobreak >nul
echo      Done.
echo.

:: Step 2: Fix issues
echo [2/4] Running auto-fix...
powershell -ExecutionPolicy Bypass -Command "& {.\scripts\auto-fix-all.ps1}"
echo.

:: Step 3: Start server in new window
echo [3/4] Starting web server...
echo      A new window will open with the server.
echo      Look for 'Ready in X.Xs' message...
echo.
start "Web Server - localhost:3000" cmd /k "cd apps\web && echo Starting Next.js dev server... && echo Wait for 'Ready' message then refresh browser && echo. && npm run dev"

:: Step 4: Wait and open browser
echo [4/4] Waiting for server to start...
echo      This may take 10-15 seconds...
timeout /t 12 /nobreak >nul

echo.
echo      Opening browser...
start http://localhost:3000

echo.
echo  ========================================
echo    ✅ SERVER STARTED!
echo  ========================================
echo.
echo    URL: http://localhost:3000
echo.
echo    If page doesn't load immediately,
echo    wait 5 more seconds and refresh.
echo.
echo    The server is running in another window.
echo    You can close this window now.
echo.
echo  ========================================
echo.
pause
