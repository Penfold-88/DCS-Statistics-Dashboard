# Windows Docker Issues Fix Script
# This script resolves common Docker issues on Windows
#
# EXECUTION POLICY ERROR FIX:
# If you get "running scripts is disabled on this system" error, use ONE of these methods:
#
# Method 1 (Recommended - Bypass for this session only):
#   powershell -ExecutionPolicy Bypass -File .\fix-windows-issues.ps1
#
# Method 2 (Set for current user):
#   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
#   Then run: .\fix-windows-issues.ps1
#
# Method 3 (One-time bypass):
#   Right-click this file > Properties > Check "Unblock" > Apply

param(
    [switch]$Force
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Windows Docker Issues Fix Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Function to check if running as administrator
function Test-Administrator {
    $currentUser = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($currentUser)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

# Function to fix line endings
function Fix-LineEndings {
    Write-Host "Fixing line endings for Docker files..." -ForegroundColor Yellow
    
    # Files that need LF endings
    $lfFiles = @(
        "*.sh",
        "Dockerfile*",
        "docker-compose*.yml",
        ".dockerignore",
        ".env*",
        "*.conf",
        "*.ini"
    )
    
    foreach ($pattern in $lfFiles) {
        $files = Get-ChildItem -Path . -Filter $pattern -Recurse -ErrorAction SilentlyContinue
        foreach ($file in $files) {
            if (Test-Path $file.FullName -PathType Leaf) {
                $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
                if ($content) {
                    $content = $content -replace "`r`n", "`n"
                    [System.IO.File]::WriteAllText($file.FullName, $content, [System.Text.Encoding]::UTF8)
                    Write-Host "  Fixed: $($file.Name)" -ForegroundColor Green
                }
            }
        }
    }
}

# Function to check Docker Desktop status
function Test-DockerDesktop {
    Write-Host "Checking Docker Desktop status..." -ForegroundColor Yellow
    
    try {
        $dockerVersion = docker --version 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  Docker is installed: $dockerVersion" -ForegroundColor Green
            
            $dockerInfo = docker info 2>&1
            if ($LASTEXITCODE -eq 0) {
                Write-Host "  Docker daemon is running" -ForegroundColor Green
                return $true
            } else {
                Write-Host "  Docker daemon is not running" -ForegroundColor Red
                Write-Host "  Please start Docker Desktop" -ForegroundColor Yellow
                
                # Try to start Docker Desktop
                $dockerDesktop = Get-Process -Name "Docker Desktop" -ErrorAction SilentlyContinue
                if (-not $dockerDesktop) {
                    Write-Host "  Attempting to start Docker Desktop..." -ForegroundColor Yellow
                    $dockerPath = "$env:ProgramFiles\Docker\Docker\Docker Desktop.exe"
                    if (Test-Path $dockerPath) {
                        Start-Process $dockerPath
                        Write-Host "  Waiting for Docker to start (this may take a minute)..." -ForegroundColor Yellow
                        Start-Sleep -Seconds 30
                    }
                }
                return $false
            }
        } else {
            Write-Host "  Docker is not installed" -ForegroundColor Red
            Write-Host "  Please install Docker Desktop from: https://www.docker.com/products/docker-desktop/" -ForegroundColor Yellow
            return $false
        }
    }
    catch {
        Write-Host "  Error checking Docker: $_" -ForegroundColor Red
        return $false
    }
}

# Function to reset Docker networks
function Reset-DockerNetworks {
    Write-Host "Cleaning up Docker networks..." -ForegroundColor Yellow
    
    # Remove the specific network if it exists
    $networks = docker network ls --format "{{.Name}}" 2>&1
    if ($networks -contains "dcs-statistics_dcs-network") {
        docker network rm dcs-statistics_dcs-network 2>&1 | Out-Null
        Write-Host "  Removed old dcs-statistics network" -ForegroundColor Green
    }
    
    # Prune unused networks
    docker network prune -f 2>&1 | Out-Null
    Write-Host "  Pruned unused networks" -ForegroundColor Green
}

# Function to fix permissions
function Fix-Permissions {
    Write-Host "Checking file permissions..." -ForegroundColor Yellow
    
    # Ensure dcs-stats directory exists
    if (-not (Test-Path ".\dcs-stats")) {
        Write-Host "  Warning: dcs-stats directory not found" -ForegroundColor Red
        return
    }
    
    # Create required subdirectories
    $dirs = @(
        ".\dcs-stats\data",
        ".\dcs-stats\site-config\data",
        ".\dcs-stats\backups"
    )
    
    foreach ($dir in $dirs) {
        if (-not (Test-Path $dir)) {
            New-Item -ItemType Directory -Path $dir -Force | Out-Null
            Write-Host "  Created directory: $dir" -ForegroundColor Green
        }
    }
}

# Function to check WSL2 backend
function Test-WSL2Backend {
    Write-Host "Checking Docker WSL2 backend..." -ForegroundColor Yellow
    
    $dockerSettings = "$env:APPDATA\Docker\settings.json"
    if (Test-Path $dockerSettings) {
        $settings = Get-Content $dockerSettings | ConvertFrom-Json
        if ($settings.wslEngineEnabled) {
            Write-Host "  WSL2 backend is enabled (recommended)" -ForegroundColor Green
            
            # Check WSL2 installation
            $wslVersion = wsl --version 2>&1
            if ($LASTEXITCODE -eq 0) {
                Write-Host "  WSL2 is installed" -ForegroundColor Green
            } else {
                Write-Host "  WSL2 is not installed properly" -ForegroundColor Yellow
                Write-Host "  Run: wsl --install" -ForegroundColor Yellow
            }
        } else {
            Write-Host "  WSL2 backend is disabled" -ForegroundColor Yellow
            Write-Host "  Consider enabling WSL2 backend in Docker Desktop settings for better performance" -ForegroundColor Yellow
        }
    }
}

# Function to create .env file if missing
function Ensure-EnvFile {
    Write-Host "Checking .env file..." -ForegroundColor Yellow
    
    if (-not (Test-Path ".\.env")) {
        if (Test-Path ".\.env.example") {
            Copy-Item ".\.env.example" ".\.env"
            Write-Host "  Created .env file from .env.example" -ForegroundColor Green
            
            # Update default port if 9080 is in use
            $port = 9080
            $tcpListener = $null
            try {
                $tcpListener = New-Object System.Net.Sockets.TcpListener([System.Net.IPAddress]::Any, $port)
                $tcpListener.Start()
            }
            catch {
                # Port is in use, find another
                for ($i = 8081; $i -le 8180; $i++) {
                    try {
                        $tcpListener = New-Object System.Net.Sockets.TcpListener([System.Net.IPAddress]::Any, $i)
                        $tcpListener.Start()
                        $port = $i
                        break
                    }
                    catch {
                        continue
                    }
                    finally {
                        if ($tcpListener) {
                            $tcpListener.Stop()
                        }
                    }
                }
                
                # Update .env with new port
                $envContent = Get-Content ".\.env"
                $envContent = $envContent -replace "WEB_PORT=9080", "WEB_PORT=$port"
                Set-Content ".\.env" $envContent
                Write-Host "  Updated WEB_PORT to $port (9080 was in use)" -ForegroundColor Yellow
            }
            finally {
                if ($tcpListener) {
                    $tcpListener.Stop()
                }
            }
        } else {
            Write-Host "  Warning: No .env or .env.example file found" -ForegroundColor Red
        }
    } else {
        Write-Host "  .env file exists" -ForegroundColor Green
    }
}

# Main execution
Write-Host "Starting diagnostics and fixes..." -ForegroundColor Cyan
Write-Host ""

# Check if running as admin (informational)
if (Test-Administrator) {
    Write-Host "Running with Administrator privileges" -ForegroundColor Green
} else {
    Write-Host "Running without Administrator privileges" -ForegroundColor Yellow
    Write-Host "Some fixes may require admin rights" -ForegroundColor Yellow
}
Write-Host ""

# Run all checks and fixes
$dockerOk = Test-DockerDesktop
Write-Host ""

if ($dockerOk -or $Force) {
    Fix-LineEndings
    Write-Host ""
    
    Fix-Permissions
    Write-Host ""
    
    Ensure-EnvFile
    Write-Host ""
    
    if ($dockerOk) {
        Reset-DockerNetworks
        Write-Host ""
        
        Test-WSL2Backend
        Write-Host ""
    }
    
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "Fixes completed!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "You can now run:" -ForegroundColor Cyan
    Write-Host "  .\docker-start.ps1" -ForegroundColor Yellow
    Write-Host ""
} else {
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "Please fix Docker issues first" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "After Docker Desktop is running, run this script again" -ForegroundColor Yellow
    Write-Host ""
}

# Pause if running from explorer
if ($Host.Name -eq "ConsoleHost") {
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
}