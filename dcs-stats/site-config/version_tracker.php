<?php
/**
 * Version and Branch Tracking System
 * Detects current git branch and manages version metadata
 */

function getCurrentVersionInfo() {
    $rootPath = dirname(__DIR__);
    $metaFile = $rootPath . '/.version_meta.json';
    require_once __DIR__ . '/update_channel.php';
    $channelConfig = getUpdateChannelConfig();
    
    // Default values
    $info = [
        'version' => defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : 'V1.2',
        'branch' => 'main',
        'commit_sha' => null,
        'commit_date' => null,
        'updated_at' => null,
        'updated_by' => null
    ];
    
    $isDev = !empty($channelConfig['is_dev']);
    
    // Load existing metadata if available
    if (file_exists($metaFile)) {
        $meta = json_decode(file_get_contents($metaFile), true);
        if ($meta) {
            // Use all stored metadata
            $info['version'] = $meta['version'] ?? $info['version'];
            $info['branch'] = $meta['branch'] ?? $info['branch'];
            $info['commit_sha'] = $meta['commit_sha'] ?? null;
            $info['commit_date'] = $meta['commit_date'] ?? null;
            $info['updated_at'] = $meta['updated_at'] ?? null;
            $info['updated_by'] = $meta['updated_by'] ?? null;
        }
    }

    if (empty($info['commit_sha']) && defined('ADMIN_PANEL_VERSION')) {
        $info['version'] = ADMIN_PANEL_VERSION;
        $info['manual_download'] = true;
    }
    
    // Override with Dev if in development environment
    if ($isDev) {
        $info['branch'] = $channelConfig['branch'] ?? 'Dev';
        $info['is_dev_override'] = true;
    }
    
    return $info;
}

function getInstalledBuildLabel($versionInfo = null) {
    $versionInfo = is_array($versionInfo) ? $versionInfo : getCurrentVersionInfo();
    $version = $versionInfo['version'] ?? (defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : 'Unknown');

    if (!empty($versionInfo['manual_download']) || empty($versionInfo['commit_sha'])) {
        return $version . ' (Manual Download)';
    }

    return $version;
}

function getGitHubBranchVersionInfo($repo, $branch, $timeoutSeconds = 5) {
    if (!function_exists('curl_init') || empty($repo) || empty($branch)) {
        return null;
    }

    $maxResponseBytes = 65536;
    $branchData = '';
    $repoPath = str_replace('%2F', '/', rawurlencode($repo));
    $branchUrl = 'https://api.github.com/repos/' . $repoPath . '/branches/' . rawurlencode($branch);
    $ch = curl_init($branchUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Version-Tracker');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeoutSeconds);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSeconds);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) use (&$branchData, $maxResponseBytes) {
        $branchData .= $chunk;
        return strlen($branchData) > $maxResponseBytes ? 0 : strlen($chunk);
    });
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || $branchData === '' || strlen($branchData) > $maxResponseBytes) {
        return null;
    }

    $branchInfo = json_decode($branchData, true);
    if (!is_array($branchInfo)) {
        return null;
    }

    $commit = $branchInfo['commit'] ?? null;
    if (!is_array($commit)) {
        return null;
    }

    $sha = $commit['sha'] ?? null;
    if (!is_string($sha) || !preg_match('/^[a-f0-9]{40}$/i', $sha)) {
        return null;
    }

    $commitMeta = $commit['commit'] ?? null;
    $committer = is_array($commitMeta) ? ($commitMeta['committer'] ?? null) : null;
    $commitDate = is_array($committer) ? ($committer['date'] ?? null) : null;
    if (!is_string($commitDate) || strtotime($commitDate) === false) {
        $commitDate = null;
    }

    if (strlen((string)$branch) > 100) {
        return null;
    }

    return [
        'branch' => $branch,
        'commit_sha' => $sha,
        'commit_date' => $commitDate
    ];
}

function updateVersionMetadata($version = null, $branch = null, $username = null, $commitSha = null, $commitDate = null) {
    $rootPath = dirname(__DIR__);
    $metaFile = $rootPath . '/.version_meta.json';
    
    // Get current info
    $info = getCurrentVersionInfo();
    
    // Update with new values if provided
    if ($version !== null) {
        $info['version'] = $version;
    }
    if ($branch !== null) {
        $info['branch'] = $branch;
    }
    if ($commitSha !== null) {
        $info['commit_sha'] = $commitSha;
    }
    if ($commitDate !== null) {
        $info['commit_date'] = $commitDate;
    }
    
    $info['updated_at'] = date('Y-m-d H:i:s');
    $info['updated_by'] = $username ?? 'system';
    
    // Save metadata
    $metadata = [
        'version' => $info['version'],
        'branch' => $info['branch'],
        'commit_sha' => $info['commit_sha'],
        'commit_date' => $info['commit_date'],
        'updated_at' => $info['updated_at'],
        'updated_by' => $info['updated_by']
    ];
    
    file_put_contents($metaFile, json_encode($metadata, JSON_PRETTY_PRINT));
    
    return $info;
}

function initializeVersionTracking() {
    $info = getCurrentVersionInfo();
    
    // If no metadata file exists, create it
    $metaFile = dirname(__DIR__) . '/.version_meta.json';
    if (!file_exists($metaFile)) {
        updateVersionMetadata($info['version'], $info['branch'], 'initial-setup');
    }
    
    return $info;
}
