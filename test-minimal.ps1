# Minimal test
function Test-DockerInstalled {
    try {
        $null = docker --version 2>&1
        if ($LASTEXITCODE -ne 0) {
            throw "Docker not found"
        }
        return $true
    }
    catch {
        Write-Host "Error: $_"
        return $false
    }
}

# Test it
if (Test-DockerInstalled) {
    Write-Host "Docker is installed"
} else {
    Write-Host "Docker is not installed"
}