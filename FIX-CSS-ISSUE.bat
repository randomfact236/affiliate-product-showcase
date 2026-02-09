@echo off
title Fix CSS Issue
color 0B
cls

echo.
echo  ========================================
echo    FIX CSS STYLING ISSUE
echo  ========================================
echo.
echo  This will:
echo   1. Clear all caches
echo   2. Rebuild the CSS
echo   3. Start the dev server
echo.
echo  ----------------------------------------
echo.
pause

echo.
echo [1/4] Stopping any running Node processes...
taskkill /F /IM node.exe 2>nul
timeout /t 2 /nobreak >nul
echo Done.

echo.
echo [2/4] Clearing Next.js cache...
cd apps/web
if exist .next rmdir /S /Q .next
echo Done.

echo.
echo [3/4] Rebuilding...
call npm run build
echo Done.

echo.
echo [4/4] Starting dev server...
echo.
echo ========================================
echo The server will start.
echo Open http://localhost:3000 in your browser
echo Press Ctrl+C to stop
echo ========================================
echo.

npm run dev
