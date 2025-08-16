# DCS Statistics Docker Startup Script for Windows PowerShell
# This script handles port availability checking and automatic port selection
#
# EXECUTION POLICY ERROR FIX:
# If you get "running scripts is disabled on this system" error, run:
#   powershell -ExecutionPolicy Bypass -File .\docker-start.ps1
#
# Or permanently allow scripts for current user:
#   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

param(
    [Parameter(Position=0)]
    [ValidateSet("start", "stop", "restart", "status", "logs")]
    [string]$Action = "start"
)

# Default configuration
$DefaultPort = 8080
$ContainerName = "dcs-statistics"
$EnvFile = ".env"

# Color functions
function Write-Info { Write-Host "ℹ $($args[0])" -ForegroundColor Blue }
function Write-Success { Write-Host "✓ $($args[0])" -ForegroundColor Green }
function Write-Warning { Write-Host "⚠ $($args[0])" -ForegroundColor Yellow }
function Write-Error { Write-Host "✗ $($args[0])" -ForegroundColor Red }

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
        [int]$StartPort = 8080,
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
            Write-Host "🤓 BTW, " -NoNewline
            Write-Host "fix-windows-issues.ps1" -ForegroundColor Cyan -NoNewline
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
                throw "Docker daemon is playing hard to get. Is Docker Desktop actually running? 🤔"
            } else {
                throw "Docker daemon is having a nap. Wake up Docker Desktop first! ☕"
            }
        }
        
        # Check for docker-compose or docker compose
        $null = docker-compose version 2>&1
        if ($LASTEXITCODE -eq 0) {
            $global:ComposeCmd = "docker-compose"
        } else {
            $null = docker compose version 2>&1
            if ($LASTEXITCODE -eq 0) {
                $global:ComposeCmd = "docker compose"
            } else {
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
    Write-Host "  .`\docker-start.ps1 stop" -ForegroundColor Gray
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
    if (-not (Test-Path ".`\dcs-stats`\data")) {
        $needsFix = $true
    }
    
    if ($needsFix) {
        Write-Warning "Hold up! Looks like this is your first rodeo..."
        Write-Host "💅 Just FYI: " -NoNewline
        Write-Host ".`\fix-windows-issues.ps1" -ForegroundColor Cyan -NoNewline
        Write-Host " exists for a reason"
        Write-Host "   (It`'s like a pre-flight check, but cooler)"
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
        Write-Error "Docker`'s not home right now..."
        Write-Host "🫠 Once you get Docker Desktop installed, there`'s " -NoNewline
        Write-Host ".`\fix-windows-issues.ps1" -ForegroundColor Cyan
        Write-Host "   (It`'ll make sure everything`'s perfect for Windows)"
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
            Write-Host "😤 Wow, ALL those ports are taken? That`'s... impressive"
            Write-Host "   Maybe " -NoNewline
            Write-Host "fix-windows-issues.ps1" -ForegroundColor Cyan -NoNewline
            Write-Host " can help clear things up?"
            return
        }
        
        Write-Success "Using port $selectedPort instead"
    }
    
    # Update .env file with selected port
    Update-EnvPort -Port $selectedPort
    
    # Build and start container
    Write-Info "Building Docker image (this may take a few minutes on first run)..."
    
    $buildOutput = & $ComposeCmd build --no-cache 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Failed to build Docker image"
        
        # Check for common issues that fix script would solve
        $buildError = $buildOutput -join " "
        if ($buildError -match "invalid pool" -or $buildError -match "pool request") {
            Write-Warning "Oh snap! Network configuration went sideways!"
            Write-Host "🙄 There`'s a script for that: " -NoNewline
            Write-Host "fix-windows-issues.ps1" -ForegroundColor Cyan -NoNewline
            Write-Host ""
            Write-Host "   (It literally fixes this in 2 seconds, just saying...)"
        }
        elseif ($buildError -match "no such file" -or $buildError -match "not found") {
            Write-Warning "Uh-oh! Missing some directories here!"
            Write-Host "🤔 Fun fact: " -NoNewline
            Write-Host "fix-windows-issues.ps1" -ForegroundColor Cyan -NoNewline
            Write-Host " creates these for you"
            Write-Host "   (But hey, who reads documentation, right?)"
        }
        elseif ($buildError -match "/bin/sh" -or $buildError -match "exec format") {
            Write-Warning "Classic Windows vs Linux line endings drama!"
            Write-Host "😏 Psst... " -NoNewline
            Write-Host "fix-windows-issues.ps1" -ForegroundColor Cyan -NoNewline
            Write-Host " sorts this out automatically"
            Write-Host "   (Windows being Windows, as usual...)"
        }
        else {
            Write-Host "🤯 Well, that`'s a new one! Haven`'t seen this error before..."
            Write-Host "   Maybe try " -NoNewline
            Write-Host "fix-windows-issues.ps1" -ForegroundColor Cyan -NoNewline
            Write-Host " first? It fixes most things"
            Write-Host "   (Or run `"$ComposeCmd build --no-cache`" for the gory details)"
        }
        return
    }
    Write-Success "Docker image built successfully"
    
    Write-Info "Starting container..."
    
    $startOutput = & $ComposeCmd up -d 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Failed to start container"
        
        # Check for common issues
        $startError = $startOutput -join " "
        if ($startError -match "permission denied" -or $startError -match "access denied") {
            Write-Warning "Permission denied! The Docker gods are angry!"
            Write-Host "🎭 Plot twist: " -NoNewline
            Write-Host "fix-windows-issues.ps1" -ForegroundColor Cyan -NoNewline
            Write-Host " handles permissions"
            Write-Host "   (I know, I know... should`'ve mentioned it earlier)"
        }
        elseif ($startError -match "network.*not found") {
            Write-Warning "Docker networks playing hide and seek again!"
            Write-Host "🎯 Pro tip: " -NoNewline
            Write-Host "fix-windows-issues.ps1" -ForegroundColor Cyan -NoNewline
            Write-Host " cleans these up"
            Write-Host "   (It`'s like a spa day for your Docker networks)"
        }
        elseif ($startError -match "port is already allocated" -or $startError -match "bind.*address already in use") {
            Write-Warning "Port $selectedPort is being a diva - says it`'s already taken!"
            Write-Host "🤷 That`'s awkward... I usually catch this. Try running again?"
            Write-Host "   (Sometimes ports are just moody like that)"
        }
        else {
            Write-Host "🫨 Something weird happened... and not the good kind of weird"
            Write-Host "   First aid kit: " -NoNewline
            Write-Host ".`\fix-windows-issues.ps1" -ForegroundColor Cyan
            Write-Host "   (If that doesn`'t help, run `"$ComposeCmd up`" for the full drama)"
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
        Write-Host "🔍 Check the logs with: " -NoNewline
        Write-Host ".`\docker-start.ps1 logs" -ForegroundColor Cyan
        Write-Host "   (Or just wait a sec and refresh the browser)"
    }
    
    Show-AccessInfo -Port $selectedPort
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
        docker logs -f $ContainerName
    }
}