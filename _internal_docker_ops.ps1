# DCS Statistics Docker Startup Script for Windows PowerShell
# This script handles port availability checking and automatic port selection
#
# EXECUTION POLICY ERROR FIX:
# If you get "running scripts is disabled on this system" error, run:
#   powershell -ExecutionPolicy Bypass -File .\_internal_docker_ops.ps1
#
# Or permanently allow scripts for current user:
#   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

param(
    [Parameter(Position=0)]
    [ValidateSet("start", "stop", "restart", "status", "logs", "destroy", "sanitize", "pre-flight", "rebuild", "help")]
    [string]$Action = "start",
    
    [Parameter(Position=1)]
    [string]$Flag = ""
)

# Default configuration
$DefaultPort = 9080
$ContainerName = "dcs-statistics"
$EnvFile = ".env"

# Color functions
function Write-Info { Write-Host "[INFO] $($args[0])" -ForegroundColor Blue }
function Write-Success { Write-Host "[OK] $($args[0])" -ForegroundColor Green }
function Write-Warning { Write-Host "[WARN] $($args[0])" -ForegroundColor Yellow }
function Write-Error { Write-Host "[ERROR] $($args[0])" -ForegroundColor Red }

# Function to check if a port is available
function Test-PortAvailable {
    param([int]$Port)
    
    $tcpListener = $null
    try {
        $tcpListener = New-Object System.Net.Sockets.TcpListener([System.Net.IPAddress]::Any, $Port)
        $tcpListener.Start()
        return $true
    }
    catch {
        return $false
    }
    finally {
        if ($tcpListener) {
            $tcpListener.Stop()
        }
    }
}

# Function to find an available port
function Find-AvailablePort {
    param(
        [int]$StartPort = 9080,
        [int]$MaxAttempts = 100
    )
    
    for ($i = 0; $i -lt $MaxAttempts; $i++) {
        $port = $StartPort + $i
        if (Test-PortAvailable -Port $port) {
            return $port
        }
    }
    return $null
}

# Function to get current port from .env file
function Get-CurrentPort {
    if (Test-Path $EnvFile) {
        $content = Get-Content $EnvFile | Where-Object { $_ -match "^WEB_PORT=" }
        if ($content) {
            return [int]($content -replace "WEB_PORT=", "")
        }
    } else {
        # .env file does not exist
        if (Test-Path ".env.example") {
            Write-Warning "No .env file? Bold choice..."
            Write-Host "BTW, " -NoNewline
            Write-Host "dcs-docker-manager.bat pre-flight" -ForegroundColor Cyan -NoNewline
            Write-Host " creates one for you"
            Write-Host "   (Just a thought, no pressure...)"
            Write-Host ""
        }
    }
    return $DefaultPort
}

# Function to update .env file with new port
function Update-EnvPort {
    param([int]$Port)
    
    $envContent = @()
    $portSet = $false
    
    if (Test-Path $EnvFile) {
        $envContent = Get-Content $EnvFile | ForEach-Object {
            if ($_ -match "^WEB_PORT=") {
                $portSet = $true
                "WEB_PORT=$Port"
            } else {
                $_
            }
        }
    }
    
    if (-not $portSet) {
        $envContent += "WEB_PORT=$Port"
    }
    
    $envContent | Set-Content $EnvFile
}

# Function to check Docker installation
function Test-DockerInstalled {
    try {
        $null = docker --version 2>&1
        if ($LASTEXITCODE -ne 0) {
            throw "Docker not found"
        }
        
        # Check if Docker daemon is running
        $dockerInfo = docker info 2>&1
        if ($LASTEXITCODE -ne 0) {
            # Check if this is a permission issue on Windows
            if ($dockerInfo -match "permission denied" -or $dockerInfo -match "Access is denied") {
                throw "Docker daemon is playing hard to get. Is Docker Desktop actually running?"
            }
            else {
                throw "Docker daemon is having a nap. Wake up Docker Desktop first!"
            }
        }
        
        # Check for docker-compose or docker compose
        $null = docker-compose version 2>&1
        if ($LASTEXITCODE -eq 0) {
            $global:ComposeCmd = "docker-compose"
        }
        else {
            $null = docker compose version 2>&1
            if ($LASTEXITCODE -eq 0) {
                $global:ComposeCmd = "docker compose"
            }
            else {
                throw "Docker Compose not found"
            }
        }
        
        return $true
    }
    catch {
        Write-Error $_.Exception.Message
        if ($_.Exception.Message -match "Docker Desktop") {
            Write-Host ""
            Write-Host "Please install Docker Desktop from: https://www.docker.com/products/docker-desktop/" -ForegroundColor Yellow
        }
        return $false
    }
}

# Function to get local IP addresses
function Get-LocalIPAddresses {
    $addresses = @()
    $adapters = Get-NetIPAddress -AddressFamily IPv4 | Where-Object {
        $_.IPAddress -ne "127.0.0.1" -and 
        $_.PrefixOrigin -ne "WellKnown" -and
        $_.InterfaceAlias -notmatch "Loopback"
    }
    
    foreach ($adapter in $adapters) {
        $addresses += $adapter.IPAddress
    }
    
    return $addresses
}

# Function to get external IP
function Get-ExternalIP {
    try {
        $ip = Invoke-RestMethod -Uri "https://api.ipify.org" -TimeoutSec 5
        return $ip
    }
    catch {
        return $null
    }
}

# Function to display access information
function Show-AccessInfo {
    param([int]$Port)
    
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "DCS Statistics Website is running!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Access your site at:"
    Write-Host "  Local:      " -NoNewline
    Write-Host "http://localhost:$Port" -ForegroundColor Cyan
    
    # Get local network IPs
    $localIPs = Get-LocalIPAddresses
    if ($localIPs.Count -gt 0) {
        Write-Host "  Network:"
        foreach ($ip in $localIPs) {
            Write-Host "              http://${ip}:$Port" -ForegroundColor Cyan
        }
    }
    
    # Get external IP
    $externalIP = Get-ExternalIP
    if ($externalIP) {
        Write-Host "  External:   " -NoNewline
        Write-Host "http://${externalIP}:$Port" -ForegroundColor Cyan
        Write-Host ""
        Write-Warning "Note: External access requires port forwarding on your router"
    }
    
    Write-Host ""
    Write-Host "Admin Panel: " -NoNewline
    Write-Host "http://localhost:$Port/site-config/install.php" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "To stop the server, run:"
    Write-Host '  dcs-docker-manager.bat stop' -ForegroundColor Gray
    Write-Host "  or"
    Write-Host "  $ComposeCmd down" -ForegroundColor Gray
    Write-Host ""
    
    if ($externalIP) {
        Write-Host "========================================" -ForegroundColor Yellow
        Write-Host "Port Forwarding Instructions:" -ForegroundColor Yellow
        Write-Host "========================================" -ForegroundColor Yellow
        Write-Host "If you want external access, configure your router to:"
        Write-Host "  1. Forward external port $Port to internal port $Port"
        Write-Host "  2. Point to this machine's IP address"
        Write-Host ""
        Write-Host "Common router interfaces:"
        Write-Host "  - http://192.168.1.1"
        Write-Host "  - http://192.168.0.1"
        Write-Host "  - http://10.0.0.1"
        Write-Host "========================================"
    }
}


# Function to rebuild Docker image
function Rebuild-DockerImage {
    Write-Host "========================================"
    Write-Host "Rebuilding DCS Statistics Docker Image"
    Write-Host "========================================"
    Write-Host ""
    
    # Check Docker installation
    Write-Info "Checking Docker..."
    if (-not (Test-DockerInstalled)) {
        Write-Error "Docker is not available"
        return
    }
    
    # Stop existing container if running
    $existingContainer = docker ps -aq -f "name=$ContainerName"
    if ($existingContainer) {
        Write-Info "Stopping existing container..."
        & $ComposeCmd down 2>&1 | Out-Null
    }
    
    # Remove old image
    Write-Info "Removing old Docker image..."
    docker rmi dcs-statistics:latest 2>&1 | Out-Null
    docker rmi $(docker images -q -f "reference=*dcs-statistics*") 2>&1 | Out-Null
    Write-Success "Old image removed"
    
    # Build new image
    Write-Info "Building new Docker image..."
    Write-Info "This will take a few minutes..."
    
    $buildOutput = & $ComposeCmd build --no-cache 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Failed to rebuild Docker image"
        Write-Host "Check the full output above for errors"
        return
    }
    
    Write-Success "Docker image rebuilt successfully!"
    Write-Host ""
    Write-Host "You can now start the container with: " -NoNewline
    Write-Host "dcs-docker-manager.bat start" -ForegroundColor Cyan
}

# Main execution
function Start-DCSStatistics {
    Write-Host "========================================"
    Write-Host "DCS Statistics Docker Launcher"
    Write-Host "========================================"
    Write-Host ""
    
    # Quick check for common issues
    $needsFix = $false
    if (-not (Test-Path $EnvFile) -and (Test-Path ".env.example")) {
        $needsFix = $true
    }
    if (-not (Test-Path './dcs-stats/data')) {
        $needsFix = $true
    }
    
    if ($needsFix) {
        Write-Warning "Hold up! Looks like this is your first rodeo..."
        Write-Host "Just FYI: " -NoNewline
        Write-Host './dcs-docker-manager.bat pre-flight' -ForegroundColor Cyan -NoNewline
        Write-Host " exists for a reason"
        Write-Host "   (It is like a pre-flight check, but cooler)"
        Write-Host ""
    }
    
    # Check if running as administrator (informational only)
    $isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")
    if ($isAdmin) {
        Write-Info "Running with administrator privileges (not required)"
    }
    
    # Check Docker installation
    Write-Info "Checking Docker installation..."
    if (-not (Test-DockerInstalled)) {
        Write-Error "Docker is not home right now..."
        Write-Host "Once you get Docker Desktop installed, there is " -NoNewline
        Write-Host './dcs-docker-manager.bat pre-flight' -ForegroundColor Cyan
        Write-Host "   (It will make sure everything is perfect for Windows)"
        return
    }
    Write-Success "Docker is installed and running"
    
    # Stop existing container if running
    $existingContainer = docker ps -aq -f "name=$ContainerName"
    if ($existingContainer) {
        Write-Info "Stopping existing container..."
        & $ComposeCmd down 2>&1 | Out-Null
    }
    
    # Get desired port
    $desiredPort = Get-CurrentPort
    
    # Check if port is available
    Write-Info "Checking port $desiredPort availability..."
    
    if (Test-PortAvailable -Port $desiredPort) {
        Write-Success "Port $desiredPort is available"
        $selectedPort = $desiredPort
    } else {
        Write-Warning "Port $desiredPort is in use"
        Write-Info "Finding available port..."
        
        $selectedPort = Find-AvailablePort -StartPort $desiredPort
        if (-not $selectedPort) {
            Write-Error "No available ports found in range $desiredPort-$($desiredPort + 100)"
            Write-Host "Wow, ALL those ports are taken? That is... impressive"
            Write-Host "   Maybe " -NoNewline
            Write-Host "dcs-docker-manager.bat pre-flight" -ForegroundColor Cyan -NoNewline
            Write-Host " can help clear things up?"
            return
        }
        
        Write-Success "Using port $selectedPort instead"
    }
    
    # Check if Docker image exists BEFORE updating .env
    $imageExists = docker images --format "{{.Repository}}:{{.Tag}}" | Where-Object { $_ -match "dcs-statistics:latest" }
    
    if ($imageExists) {
        # Update .env file with selected port only if image already exists
        Update-EnvPort -Port $selectedPort
        Write-Success "Docker image exists, skipping build"
        Write-Info "Use 'dcs-docker-manager.bat rebuild' to force a rebuild"
    }
    else {
        # If no Docker image exists, this is a first build - prompt the user
        Write-Host ""
        Write-Warning "*** FIRST TIME SETUP DETECTED ***"
        Write-Host ""
        Write-Host "No Docker image found. This appears to be your first time building DCS Statistics."
        Write-Host "Pre-flight checks are REQUIRED for first-time setup."
        Write-Host ""
        Write-Host "Pre-flight will:"
        Write-Host "  - Create necessary directories"
        Write-Host "  - Set up environment files"
        Write-Host "  - Fix common permission issues"
        Write-Host "  - Ensure Docker is properly configured"
        Write-Host ""
        Write-Host "Type CONTINUE to run pre-flight checks and proceed with setup"
        Write-Host "Type anything else or press Enter to exit"
        Write-Host ""
        $response = Read-Host "Your choice"
        if ($response -ne "CONTINUE") {
            Write-Host ""
            Write-Info "Setup cancelled. To set up DCS Statistics, either:"
            Write-Host "  1. Run: " -NoNewline
            Write-Host "dcs-docker-manager.bat pre-flight" -ForegroundColor Cyan -NoNewline
            Write-Host " first, then"
            Write-Host "     Run: " -NoNewline
            Write-Host "dcs-docker-manager.bat start" -ForegroundColor Cyan
            Write-Host "  OR"
            Write-Host "  2. Run: " -NoNewline
            Write-Host "dcs-docker-manager.bat start" -ForegroundColor Cyan -NoNewline
            Write-Host " and type CONTINUE when prompted"
            return
        }
        Write-Host ""
        Write-Info "Running pre-flight checks before continuing..."
        Write-Host ""
        
        # Run pre-flight checks by calling the script with pre-flight argument
        # Set environment variable to indicate we're calling from start
        $env:FROM_START = "true"
        & powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$PSScriptRoot\_internal_docker_ops.ps1" "pre-flight"
        $env:FROM_START = $null
        if ($LASTEXITCODE -ne 0) {
            Write-Error "Pre-flight checks failed. Please fix the issues and try again."
            return
        }
        
        Write-Host ""
        Write-Success "Pre-flight checks completed successfully!"
        Write-Host ""
        
        # NOW update .env file with selected port after pre-flight has set up the file
        Update-EnvPort -Port $selectedPort
        
        Write-Info "Continuing with Docker build..."
        Write-Host ""
        
        Write-Info "Docker image not found, building now..."
        Write-Info "This may take a few minutes on first run..."
        
        $buildOutput = & $ComposeCmd build --no-cache 2>&1
        if ($LASTEXITCODE -ne 0) {
            Write-Error "Failed to build Docker image"
        
        # Check for common issues that fix script would solve
        $buildError = $buildOutput -join " "
        if ($buildError -match "invalid pool" -or $buildError -match "pool request") {
            Write-Warning "Oh snap! Network configuration went sideways!"
            Write-Host "There is a script for that: " -NoNewline
            Write-Host "dcs-docker-manager.bat pre-flight" -ForegroundColor Cyan -NoNewline
            Write-Host ""
            Write-Host "   (It literally fixes this in 2 seconds, just saying...)"
        }
        elseif ($buildError -match "no such file" -or $buildError -match "not found") {
            Write-Warning "Uh-oh! Missing some directories here!"
            Write-Host "Fun fact: " -NoNewline
            Write-Host "dcs-docker-manager.bat pre-flight" -ForegroundColor Cyan -NoNewline
            Write-Host " creates these for you"
            Write-Host "   (But hey, who reads documentation, right?)"
        }
        elseif ($buildError -match "/bin/sh" -or $buildError -match "exec format") {
            Write-Warning "Classic Windows vs Linux line endings drama!"
            Write-Host "Psst... " -NoNewline
            Write-Host "dcs-docker-manager.bat pre-flight" -ForegroundColor Cyan -NoNewline
            Write-Host " sorts this out automatically"
            Write-Host "   (Windows being Windows, as usual...)"
        }
        else {
            Write-Host "Well, that is a new one! Have not seen this error before..."
            Write-Host "   Maybe try " -NoNewline
            Write-Host "dcs-docker-manager.bat pre-flight" -ForegroundColor Cyan -NoNewline
            Write-Host " first? It fixes most things"
            Write-Host "   (Or run $ComposeCmd build for the gory details)"
        }
        return
    }
    Write-Success "Docker image built successfully"
    }
    
    Write-Info "Starting container..."
    
    $startOutput = & $ComposeCmd up -d 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Failed to start container"
        
        # Check for common issues
        $startError = $startOutput -join " "
        if ($startError -match "permission denied" -or $startError -match "access denied") {
            Write-Warning "Permission denied! The Docker gods are angry!"
            Write-Host "Plot twist: " -NoNewline
            Write-Host "dcs-docker-manager.bat pre-flight" -ForegroundColor Cyan -NoNewline
            Write-Host " handles permissions"
            Write-Host "   (I know, I know... should have mentioned it earlier)"
        }
        elseif ($startError -match "network.*not found") {
            Write-Warning "Docker networks playing hide and seek again!"
            Write-Host "Pro tip: " -NoNewline
            Write-Host "dcs-docker-manager.bat pre-flight" -ForegroundColor Cyan -NoNewline
            Write-Host " cleans these up"
            Write-Host "   (It is like a spa day for your Docker networks)"
        }
        elseif ($startError -match "port is already allocated" -or $startError -match "bind.*address already in use") {
            Write-Warning "Port $selectedPort is being a diva - says it is already taken!"
            Write-Host "That is awkward... I usually catch this. Try running again?"
            Write-Host "   (Sometimes ports are just moody like that)"
        }
        else {
            Write-Host "Something weird happened... and not the good kind of weird"
            Write-Host "   First aid kit: " -NoNewline
            Write-Host './dcs-docker-manager.bat pre-flight' -ForegroundColor Cyan
            Write-Host "   (If that does not help, run $ComposeCmd up for the full drama)"
        }
        return
    }
    Write-Success "Container started successfully"
    
    # Wait for service to be ready
    Write-Info "Waiting for service to be ready..."
    Start-Sleep -Seconds 3
    
    # Check if service is responding
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:$selectedPort/health-check.php" -UseBasicParsing -TimeoutSec 5
        if ($response.StatusCode -eq 200) {
            Write-Success "Service is ready!"
        }
    }
    catch {
        Write-Warning "Service is being shy... might still be waking up"
        Write-Host "Check the logs with: " -NoNewline
        Write-Host 'dcs-docker-manager.bat logs' -ForegroundColor Cyan
        Write-Host "   (Or just wait a sec and refresh the browser)"
    }
    
    Show-AccessInfo -Port $selectedPort
}

# Detect docker-compose command
$null = docker-compose version 2>&1
if ($LASTEXITCODE -eq 0) {
    $ComposeCmd = "docker-compose"
} else {
    $null = docker compose version 2>&1
    if ($LASTEXITCODE -eq 0) {
        $ComposeCmd = "docker compose"
    } else {
        Write-Error "Docker Compose not found"
        exit 1
    }
}

# Handle script actions
switch ($Action) {
    "start" {
        Start-DCSStatistics
    }
    "stop" {
        Write-Info "Stopping DCS Statistics..."
        & $ComposeCmd down
        Write-Success "Stopped"
    }
    "restart" {
        Write-Info "Restarting DCS Statistics..."
        & $ComposeCmd down
        Start-DCSStatistics
    }
    "status" {
        $running = docker ps -q -f "name=$ContainerName"
        if ($running) {
            $port = Get-CurrentPort
            Write-Success "DCS Statistics is running on port $port"
            Write-Host "Access at: http://localhost:$port"
        } else {
            Write-Info "DCS Statistics is not running"
        }
    }
    "logs" {
        Write-Info "Showing last 100 lines of logs..."
        Write-Host ""
        docker logs --tail 100 $ContainerName
        Write-Host ""
        Write-Info "End of logs. Use 'docker logs -f $ContainerName' if you want to follow live logs."
    }
    "rebuild" {
        Rebuild-DockerImage
    }
    "pre-flight" {
        Write-Host "========================================"
        Write-Host "Pre-Flight Check for DCS Statistics"
        Write-Host "========================================"
        Write-Host ""
        Write-Info "Running pre-flight checks and fixes..."
        Write-Host ""
        
        # Check Docker installation
        Write-Info "Checking Docker installation..."
        $dockerOk = $false
        try {
            $null = docker --version 2>&1
            if ($LASTEXITCODE -eq 0) {
                Write-Success "Docker is installed"
                
                $dockerInfo = docker info 2>&1
                if ($LASTEXITCODE -eq 0) {
                    Write-Success "Docker daemon is running"
                    $dockerOk = $true
                }
                else {
                    Write-Warning "Docker daemon is not running"
                    Write-Host "Please start Docker Desktop and try again"
                }
            }
            else {
                Write-Error "Docker is not installed"
                Write-Host "Please install Docker Desktop from: https://www.docker.com/products/docker-desktop/"
            }
        }
        catch {
            Write-Error "Error checking Docker: $_"
        }
        Write-Host ""
        
        if ($dockerOk) {
            # Fix line endings
            Write-Info "Fixing line endings for Docker files..."
            $filesToFix = @("*.sh", "Dockerfile*", "docker-compose*.yml", ".dockerignore", ".env*")
            foreach ($pattern in $filesToFix) {
                $files = Get-ChildItem -Path . -Filter $pattern -ErrorAction SilentlyContinue
                foreach ($file in $files) {
                    if (Test-Path $file.FullName -PathType Leaf) {
                        $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
                        if ($content) {
                            $fixed = $content -replace "`r`n", "`n"
                            [System.IO.File]::WriteAllText($file.FullName, $fixed, [System.Text.Encoding]::UTF8)
                            Write-Host "  Fixed: $($file.Name)" -ForegroundColor Green
                        }
                    }
                }
            }
            Write-Host ""
            
            # Create required directories
            Write-Info "Ensuring required directories exist..."
            $dirs = @("./dcs-stats/data", "./dcs-stats/site-config/data", "./dcs-stats/backups")
            foreach ($dir in $dirs) {
                if (-not (Test-Path $dir)) {
                    New-Item -ItemType Directory -Path $dir -Force | Out-Null
                    Write-Success "Created directory: $dir"
                }
                else {
                    Write-Host "  Directory exists: $dir" -ForegroundColor Gray
                }
            }
            Write-Host ""
            
            # Create or update .env file
            Write-Info "Checking .env file..."
            if (-not (Test-Path "./.env")) {
                if (Test-Path "./.env.example") {
                    Copy-Item "./.env.example" "./.env"
                    Write-Success "Created .env file from .env.example"
                }
                else {
                    Write-Warning "No .env.example file found"
                }
            }
            else {
                Write-Success ".env file exists"
                
                # Check if using old port 8080 and update to 9080
                $envContent = Get-Content "./.env" -Raw
                if ($envContent -match "WEB_PORT=8080") {
                    $envContent = $envContent -replace "WEB_PORT=8080", "WEB_PORT=9080"
                    $envContent = $envContent -replace "SITE_URL=http://localhost:8080", "SITE_URL=http://localhost:9080"
                    Set-Content "./.env" $envContent
                    Write-Success "Updated .env file from port 8080 to 9080"
                }
            }
            Write-Host ""
            
            # Clean up Docker networks
            Write-Info "Cleaning up Docker networks..."
            docker network prune -f 2>&1 | Out-Null
            Write-Success "Docker networks cleaned"
            Write-Host ""
            
            Write-Success "Pre-flight checks completed successfully!"
            Write-Host ""
            # Only show the "run start" message if we're not already in the start process
            if ($env:FROM_START -ne "true") {
                Write-Host "You can now run: " -NoNewline
                Write-Host "dcs-docker-manager.bat start" -ForegroundColor Cyan
                Write-Host "to launch the DCS Statistics website."
            }
        }
        else {
            Write-Error "Pre-flight checks failed. Please fix the issues above and try again."
        }
    }
    "destroy" {
        Write-Warning "This will DESTROY everything related to DCS Statistics Docker setup!"
        Write-Host ""
        Write-Host "This action will remove:" -ForegroundColor Yellow
        Write-Host "  - The DCS Statistics container" -ForegroundColor Yellow
        Write-Host "  - The DCS Statistics Docker image" -ForegroundColor Yellow
        Write-Host "  - All Docker volumes created by this project" -ForegroundColor Yellow
        Write-Host "  - The Docker network (if created)" -ForegroundColor Yellow
        Write-Host "  - Your .env configuration file" -ForegroundColor Yellow
        Write-Host ""
        # Display preserved data message in cyan (blinking not supported in PowerShell)
        Write-Host "[INFO] Your data in ./dcs-stats will be preserved" -ForegroundColor Cyan
        Write-Host ""
        
        # Check if force flag is provided
        if ($Flag -eq "-f" -or $Flag -eq "--force") {
            Write-Info "Force mode enabled - skipping confirmation"
            $confirmation = "DESTROY"
        }
        else {
            $confirmation = Read-Host "Type 'DESTROY' to confirm (or anything else to cancel)"
        }
        
        if ($confirmation -eq "DESTROY") {
            Write-Info "Starting destruction process..."
            
            # Stop and remove container
            Write-Info "Stopping and removing container..."
            & $ComposeCmd down -v 2>&1 | Out-Null
            
            # Remove the image
            Write-Info "Removing Docker image..."
            docker rmi dcs-statistics:latest 2>&1 | Out-Null
            docker rmi $(docker images -q -f "reference=dcs-statistics") 2>&1 | Out-Null
            
            # Clean up any dangling images
            Write-Info "Cleaning up dangling images..."
            docker image prune -f 2>&1 | Out-Null
            
            # Remove any project-specific volumes
            Write-Info "Removing volumes..."
            docker volume rm $(docker volume ls -q -f "name=dcs-statistics") 2>&1 | Out-Null
            
            # Clean up networks
            Write-Info "Cleaning up networks..."
            docker network prune -f 2>&1 | Out-Null
            
            # Remove .env file
            Write-Info "Removing .env configuration file..."
            if (Test-Path "./.env") {
                Remove-Item "./.env" -Force
                Write-Success "Removed .env file"
            }
            
            Write-Success "Destruction complete!"
            Write-Host ""
            Write-Host "The following items were preserved:" -ForegroundColor Green
            Write-Host "  - Your data in ./dcs-stats directory" -ForegroundColor Green
            Write-Host ""
            Write-Host "To completely start fresh, run:" -ForegroundColor Cyan
            Write-Host "  dcs-docker-manager.bat pre-flight" -ForegroundColor Cyan
            Write-Host "  dcs-docker-manager.bat start" -ForegroundColor Cyan
        }
        else {
            Write-Info "Destruction cancelled"
        }
    }
    "sanitize" {
        Write-Warning "*** COMPLETE SANITIZATION WARNING ***"
        Write-Host ""
        Write-Host "This will PERMANENTLY DELETE:" -ForegroundColor Red
        Write-Host "  - The DCS Statistics container" -ForegroundColor Red
        Write-Host "  - The DCS Statistics Docker image" -ForegroundColor Red
        Write-Host "  - All Docker volumes and networks" -ForegroundColor Red
        Write-Host "  - Your .env configuration file" -ForegroundColor Red
        Write-Host "  - ALL DATA in ./dcs-stats/data directory" -ForegroundColor Red
        Write-Host "  - ALL DATA in ./dcs-stats/site-config/data directory" -ForegroundColor Red
        Write-Host "  - ALL BACKUPS in ./dcs-stats/backups directory" -ForegroundColor Red
        Write-Host ""
        Write-Warning "*** THIS CANNOT BE UNDONE! ***"
        Write-Host ""
        
        # Check if force flag is provided
        if ($Flag -eq "-f" -or $Flag -eq "--force") {
            Write-Info "Force mode enabled - skipping confirmation"
            $confirmation = "SANITIZE"
        }
        else {
            $confirmation = Read-Host "Type 'SANITIZE' to confirm complete data wipe (or anything else to cancel)"
        }
        
        if ($confirmation -eq "SANITIZE") {
            Write-Info "Starting complete sanitization..."
            
            # Stop and remove container
            Write-Info "Stopping and removing container..."
            & $ComposeCmd down -v 2>&1 | Out-Null
            
            # Remove the image
            Write-Info "Removing Docker image..."
            docker rmi dcs-statistics:latest 2>&1 | Out-Null
            docker rmi $(docker images -q -f "reference=dcs-statistics") 2>&1 | Out-Null
            
            # Clean up any dangling images
            Write-Info "Cleaning up dangling images..."
            docker image prune -f 2>&1 | Out-Null
            
            # Remove any project-specific volumes
            Write-Info "Removing volumes..."
            docker volume rm $(docker volume ls -q -f "name=dcs-statistics") 2>&1 | Out-Null
            
            # Clean up networks
            Write-Info "Cleaning up networks..."
            docker network prune -f 2>&1 | Out-Null
            
            # Remove .env file
            Write-Info "Removing .env configuration file..."
            if (Test-Path "./.env") {
                Remove-Item "./.env" -Force
                Write-Success "Removed .env file"
            }
            
            # DELETE ALL DATA
            Write-Warning "Deleting ALL user data..."
            
            # Remove data directories
            if (Test-Path "./dcs-stats/data") {
                Write-Info "Removing ./dcs-stats/data directory..."
                Remove-Item "./dcs-stats/data" -Recurse -Force
                Write-Success "Removed data directory"
            }
            
            if (Test-Path "./dcs-stats/site-config/data") {
                Write-Info "Removing ./dcs-stats/site-config/data directory..."
                Remove-Item "./dcs-stats/site-config/data" -Recurse -Force
                Write-Success "Removed site-config data directory"
            }
            
            # Remove backups
            if (Test-Path "./dcs-stats/backups") {
                Write-Info "Removing ./dcs-stats/backups directory..."
                Remove-Item "./dcs-stats/backups" -Recurse -Force
                Write-Success "Removed backups directory"
            }
            
            Write-Success "Complete sanitization finished!"
            Write-Host ""
            Write-Warning "ALL DATA HAS BEEN PERMANENTLY DELETED"
            Write-Host ""
            Write-Host "To start completely fresh:" -ForegroundColor Cyan
            Write-Host "  1. Run: dcs-docker-manager.bat pre-flight" -ForegroundColor Cyan
            Write-Host "  2. Run: dcs-docker-manager.bat start" -ForegroundColor Cyan
            Write-Host "  3. Complete setup at http://localhost:9080/site-config/install.php" -ForegroundColor Cyan
        }
        else {
            Write-Info "Sanitization cancelled"
        }
    }
    "help" {
        # Help is handled by the BAT file, but if called directly show basic help
        Write-Host "========================================"
        Write-Host "DCS Statistics Docker Manager" -ForegroundColor Cyan
        Write-Host "========================================"
        Write-Host ""
        Write-Host "This script should be called via dcs-docker-manager.bat"
        Write-Host ""
        Write-Host "Usage: dcs-docker-manager.bat [COMMAND]"
        Write-Host ""
        Write-Host "Available Commands:"
        Write-Host "  pre-flight  - Run pre-flight checks"
        Write-Host "  start       - Start container"
        Write-Host "  stop        - Stop container" 
        Write-Host "  restart     - Restart container"
        Write-Host "  status      - Check status"
        Write-Host "  logs        - Show logs"
        Write-Host "  destroy     - Remove everything except data"
        Write-Host "  sanitize    - Remove EVERYTHING including all data"
        Write-Host "  help        - Show help"
    }
}