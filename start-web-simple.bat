@echo off
echo Starting Affiliate Website Web Server...
echo.

:: Kill any existing node processes (clean slate)
taskkill /f /im node.exe 2>nul

:: Wait a moment
timeout /t 2 /nobreak >nul

:: Start the web server in a new window
echo Starting Next.js dev server on port 3000...
start "Web Server" cmd /k "cd apps/web && npm run dev"

:: Wait for server to start
echo.
echo Waiting for server to start...
timeout /t 10 /nobreak >nul

:: Try to open browser
echo Opening browser...
start http://localhost:3000

echo.
echo ========================================
echo  Web server should be starting...
echo  Browser will open automatically
echo  If page doesn't load, wait 10s and refresh
echo ========================================
echo.

:: Keep checking if server is up
:check_loop
timeout /t 3 /nobreak >nul
powershell -Command "try { $r = Invoke-WebRequest 'http://localhost:3000' -TimeoutSec 2; Write-Host 'SUCCESS: Server is running! (HTTP '$r.StatusCode')' -ForegroundColor Green; exit } catch { Write-Host -NoNewline '.' }"
if errorlevel 1 goto check_loop
