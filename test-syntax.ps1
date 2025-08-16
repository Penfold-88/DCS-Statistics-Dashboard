param(
    [string]$FilePath = ".\docker-start.ps1"
)

try {
    $tokens = $null
    $errors = $null
    $ast = [System.Management.Automation.Language.Parser]::ParseFile(
        $FilePath, 
        [ref]$tokens, 
        [ref]$errors
    )
    
    if ($errors.Count -gt 0) {
        Write-Host "Syntax errors found:" -ForegroundColor Red
        foreach ($error in $errors) {
            Write-Host "Line $($error.Extent.StartLineNumber): $($error.Message)" -ForegroundColor Yellow
            Write-Host "  Near: $($error.Extent.Text)" -ForegroundColor Gray
        }
        exit 1
    } else {
        Write-Host "No syntax errors found!" -ForegroundColor Green
        exit 0
    }
} catch {
    Write-Host "Error parsing file: $_" -ForegroundColor Red
    exit 1
}