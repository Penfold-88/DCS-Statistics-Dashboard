@echo off
:: DCS Statistics Docker Manager - Windows Batch Wrapper
:: This file manages Docker containers for DCS Statistics
:: Supports: pre-flight, start, stop, restart, status, logs, destroy

:: Check if no arguments provided
if "%~1"=="" goto :ShowHelp

:: Check for help command
if /I "%~1"=="help" goto :ShowHelp
if /I "%~1"=="--help" goto :ShowHelp
if /I "%~1"=="-h" goto :ShowHelp
if /I "%~1"=="/?" goto :ShowHelp

echo ========================================
echo DCS Statistics Docker Manager
echo ========================================
echo.

:: Check if PowerShell is available
where powershell >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PowerShell is not installed or not in PATH
    echo Please install PowerShell to continue
    exit /b 1
)

:: Run the PowerShell script with bypassed execution policy
:: Pass all command line arguments to the PowerShell script
echo Starting Docker containers...
echo.

:: Pass arguments to PowerShell, handling optional second parameter
if "%~2"=="" (
    powershell.exe -ExecutionPolicy Bypass -NoProfile -Command "& '%~dp0_internal_docker_ops.ps1' -Action '%~1'"
) else (
    powershell.exe -ExecutionPolicy Bypass -NoProfile -Command "& '%~dp0_internal_docker_ops.ps1' -Action '%~1' -Flag '%~2'"
)
goto :End

:ShowHelp
echo ========================================
echo DCS Statistics Docker Manager
echo ========================================
echo.
echo Usage: dcs-docker-manager.bat [COMMAND]
echo.
echo Available Commands:
echo.
echo   pre-flight  - Run pre-flight checks and auto-install Docker if needed
echo   start       - Start DCS Statistics container (builds only if needed)
echo   stop        - Stop DCS Statistics container
echo   restart     - Restart DCS Statistics container
echo   rebuild     - Force rebuild of Docker image
echo   status      - Check if container is running
echo   logs        - Show container logs (last 100 lines)
echo   destroy     - Remove everything except your data (add -f to skip confirmation)
echo   sanitize    - Remove EVERYTHING including all data (add -f to skip confirmation)
echo   help        - Show this help menu
echo.
echo Quick Start:
echo   1. Run 'dcs-docker-manager.bat pre-flight' to set up everything
echo   2. Run 'dcs-docker-manager.bat start' to launch the application
echo.
echo First Time Users:
echo   Start with 'pre-flight' - it will:
echo   - Check and install Docker Desktop if needed
echo   - Set up required directories
echo   - Configure environment files
echo   - Fix any line ending issues
echo.
echo ========================================
exit /b 0

:End

:: Check if the script ran successfully
if %errorlevel% neq 0 (
    echo.
    echo ERROR: Docker startup failed. See error messages above.
    echo.
    echo Common solutions:
    echo - Make sure Docker Desktop is running
    echo - Run 'dcs-docker-manager.bat pre-flight' first
    echo - Check if port 9080 is available
    exit /b %errorlevel%
)

:: Success message is handled by the PowerShell script