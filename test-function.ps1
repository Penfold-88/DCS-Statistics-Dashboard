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
# Test the function
Test-DockerInstalled
