<?php
/**
 * Version and Branch Tracking System
 * Detects current git branch and manages version metadata
 */

function getCurrentVersionInfo() {
    $rootPath = dirname(__DIR__);
    $metaFile = $rootPath . '/.version_meta.json';
    
    // Default values
    $info = [
        'version' => defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : 'V0.0.04',
        'branch' => 'main',
        'updated_at' => null,
        'updated_by' => null
    ];
    
    // Check for development environment indicators
    $isDev = false;
    
    // 1. Check for .dev file in site root
    if (file_exists($rootPath . '/.dev')) {
        $isDev = true;
    }
    
    // 2. Check for .dev file in parent directory (for site-config context)
    if (file_exists(dirname($rootPath) . '/.dev')) {
        $isDev = true;
    }
    
    // 3. Optional: Check environment variable (backward compatibility)
    if (getenv('DEV_BRANCH') === 'true') {
        $isDev = true;
    }
    
    // Load existing metadata if available
    if (file_exists($metaFile)) {
        $meta = json_decode(file_get_contents($metaFile), true);
        if ($meta) {
            // Use all stored metadata
            $info['version'] = $meta['version'] ?? $info['version'];
            $info['branch'] = $meta['branch'] ?? $info['branch'];
            $info['updated_at'] = $meta['updated_at'] ?? null;
            $info['updated_by'] = $meta['updated_by'] ?? null;
        }
    }
    
    // Override with Dev if in development environment
    if ($isDev) {
        $info['branch'] = 'Dev';
        $info['is_dev_override'] = true;
    }
    
    return $info;
}

function updateVersionMetadata($version = null, $branch = null, $username = null) {
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
    
    $info['updated_at'] = date('Y-m-d H:i:s');
    $info['updated_by'] = $username ?? 'system';
    
    // Save metadata
    $metadata = [
        'version' => $info['version'],
        'branch' => $info['branch'],
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