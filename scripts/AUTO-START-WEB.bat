@echo off
echo ========================================
echo  Affiliate Website Auto-Start
echo ========================================
echo.
echo This will automatically:
echo  1. Check for errors
echo  2. Fix any issues found
echo  3. Start the web server
echo  4. Open your browser
echo  5. Verify everything works
echo.
pause

powershell -ExecutionPolicy Bypass -File "scripts/auto-start-and-verify.ps1" -MaxRetries 10 -OpenBrowser

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ========================================
    echo  There was an error starting the server.
    echo  Check the messages above.
    echo ========================================
    pause
)
