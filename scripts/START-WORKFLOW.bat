@echo off
chcp 65001 >nul
echo ============================================
echo  Affiliate Product Showcase - Start Workflow
echo ============================================
echo.

REM Check if PowerShell is available
where powershell >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: PowerShell is not available
    exit /b 1
)

REM Run the PowerShell script
echo Starting workflow automation...
powershell -ExecutionPolicy Bypass -File "%~dp0START-WORKFLOW.ps1" %*

pause
