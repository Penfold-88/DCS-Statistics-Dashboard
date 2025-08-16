@echo off
:: DCS Statistics Docker Manager - Windows Batch Wrapper
:: This file manages Docker containers for DCS Statistics
:: Supports: pre-flight, start, stop, restart, status, logs, destroy

echo ========================================
echo DCS Statistics Docker Manager
echo ========================================
echo.

:: Check if PowerShell is available
where powershell >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PowerShell is not installed or not in PATH
    echo Please install PowerShell to continue
    pause
    exit /b 1
)

:: Run the PowerShell script with bypassed execution policy
:: Pass all command line arguments to the PowerShell script
echo Starting Docker containers...
echo.

powershell.exe -ExecutionPolicy Bypass -NoProfile -File "%~dp0_internal_docker_ops.ps1" %*

:: Check if the script ran successfully
if %errorlevel% neq 0 (
    echo.
    echo ERROR: Docker startup failed. See error messages above.
    echo.
    echo Common solutions:
    echo - Make sure Docker Desktop is running
    echo - Run 'dcs-docker-manager.bat pre-flight' first
    echo - Check if port 9080 is available
    pause
    exit /b %errorlevel%
)

:: Success message is handled by the PowerShell script
pause