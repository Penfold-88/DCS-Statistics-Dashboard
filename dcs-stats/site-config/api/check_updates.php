<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../admin_functions.php';
require_once __DIR__ . '/../update_channel.php';

requireAdmin();
requirePermission('manage_updates');

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache');

$channelConfig = getUpdateChannelConfig();
$repo = $channelConfig['repo'];
$branch = $channelConfig['branch'];
// Get version info including branch detection
require_once dirname(__DIR__) . '/version_tracker.php';
$versionInfo = getCurrentVersionInfo();
$currentBranch = $versionInfo['branch'];
$currentVersion = $versionInfo['version'] ?? (defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : '1.0.0');
$installedBuild = getInstalledBuildLabel($versionInfo);

echo "Current Version: $currentVersion\n";
if (!empty($versionInfo['version'])) {
    echo "Installed Build: {$installedBuild}\n";
}
if (!empty($versionInfo['commit_sha'])) {
    echo "Installed Commit: " . substr($versionInfo['commit_sha'], 0, 12) . "\n";
}
if (!empty($versionInfo['commit_date'])) {
    echo "Installed Date: " . date('Y-m-d H:i:s', strtotime($versionInfo['commit_date'])) . "\n";
}
echo "Update Channel: {$channelConfig['channel']}\n";
echo "GitHub Branch: $branch\n";
echo "----------------------------------------\n\n";

echo "Checking selected GitHub branch...\n";
$branchUrl = "https://api.github.com/repos/$repo/branches/" . rawurlencode($branch);
$ch = curl_init($branchUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
$branchData = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 && $branch === 'master') {
    echo "Master branch not found. Checking fallback branch: main\n";
    $branch = 'main';
    $branchUrl = "https://api.github.com/repos/$repo/branches/main";
    $ch = curl_init($branchUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
    $branchData = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}

if ($httpCode === 200 && $branchData) {
    $branchInfo = json_decode($branchData, true);
    $sha = $branchInfo['commit']['sha'] ?? null;
    $commitDate = $branchInfo['commit']['commit']['committer']['date'] ?? null;
    if ($sha) {
        echo "Latest Commit: " . substr($sha, 0, 12) . "\n";
        if ($commitDate) {
            echo "Latest Date: " . date('Y-m-d H:i:s', strtotime($commitDate)) . "\n";
        }

        if (!empty($versionInfo['commit_sha']) && strtolower($versionInfo['commit_sha']) === strtolower($sha)) {
            echo "\n✓ You are running the latest $branch build.\n";
        } else {
            echo "\n✅ Update Available!\n";
            echo "Click Update Now to download the latest code from $branch.\n";
        }
    }
} else {
    echo "Could not fetch branch information.\n";
    echo "HTTP Code: $httpCode\n";
    if (!empty($curlError)) {
        echo "Connection Error: $curlError\n";
    }
}

// Check all available releases
echo "\n----------------------------------------\n";
echo "Available versions for downgrade:\n";

$releasesUrl = "https://api.github.com/repos/$repo/releases?per_page=10";
$ch = curl_init($releasesUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
$releasesData = curl_exec($ch);
curl_close($ch);

if ($releasesData) {
    $releases = json_decode($releasesData, true);
    if (is_array($releases)) {
        foreach ($releases as $rel) {
            if (isset($rel['tag_name'])) {
                $marker = version_compare($currentVersion, $rel['tag_name'], '==') ? ' (current)' : '';
                echo "- " . $rel['tag_name'] . $marker . "\n";
            }
        }
    }
}
