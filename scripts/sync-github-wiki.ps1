param(
    [string]$WikiRepoUrl = "https://github.com/Penfold-88/DCS-Statistics-Dashboard.wiki.git",
    [string]$CommitMessage = "Update wiki pages"
)

$ErrorActionPreference = "Stop"

$repoRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
$wikiSource = Join-Path $repoRoot "docs\github-wiki"
$syncRoot = Join-Path $repoRoot ".wiki-sync"
$wikiCheckout = Join-Path $syncRoot "DCS-Statistics-Dashboard.wiki"

if (-not (Test-Path $wikiSource)) {
    throw "Wiki source folder not found: $wikiSource"
}

if (-not (Test-Path $syncRoot)) {
    New-Item -ItemType Directory -Path $syncRoot | Out-Null
}

if (-not (Test-Path (Join-Path $wikiCheckout ".git"))) {
    if (Test-Path $wikiCheckout) {
        Remove-Item -LiteralPath $wikiCheckout -Recurse -Force
    }
    git clone $WikiRepoUrl $wikiCheckout
} else {
    git -C $wikiCheckout pull --ff-only
}

Get-ChildItem -LiteralPath $wikiCheckout -File -Filter "*.md" |
    Remove-Item -Force

Get-ChildItem -LiteralPath $wikiSource -File -Filter "*.md" |
    Copy-Item -Destination $wikiCheckout -Force

git -C $wikiCheckout add .

$status = git -C $wikiCheckout status --porcelain
if (-not $status) {
    Write-Host "Wiki is already up to date."
    exit 0
}

git -C $wikiCheckout commit -m $CommitMessage
git -C $wikiCheckout push

Write-Host "Wiki updated successfully."
