<?php
/**
 * Path Configuration
 * Automatically detects the base path for portable installations
 */

// Get the base URL path dynamically
function getBasePath() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptPath = dirname($scriptName);
    
    // Normalize the path
    if ($scriptPath === '/' || $scriptPath === '\\') {
        return '';
    }
    
    // Ensure it doesn't end with a slash
    return rtrim($scriptPath, '/\\');
}

// Get the full base URL including protocol and domain
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $basePath = getBasePath();
    
    return $protocol . $host . $basePath;
}

// Define constants if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', getBasePath());
}

if (!defined('BASE_URL')) {
    define('BASE_URL', getBaseUrl());
}

// Helper function to create proper URLs
function url($path = '') {
    if (empty($path)) {
        return BASE_PATH;
    }
    
    // Remove leading slash from path
    $path = ltrim($path, '/');
    
    // If BASE_PATH is empty (root installation), just return with leading slash
    if (empty(BASE_PATH)) {
        return '/' . $path;
    }
    
    return BASE_PATH . '/' . $path;
}

// Helper function for cache-busted asset URLs
function assetUrl($path = '') {
    $url = url($path);
    $path = ltrim((string)$path, '/');
    $assetPath = __DIR__ . '/' . $path;
    $versionParts = [];
    $metaFile = __DIR__ . '/.version_meta.json';

    if (file_exists($metaFile)) {
        $meta = json_decode((string)file_get_contents($metaFile), true);
        if (is_array($meta)) {
            if (!empty($meta['version'])) {
                $versionParts[] = (string)$meta['version'];
            }
            if (!empty($meta['commit_sha'])) {
                $versionParts[] = substr((string)$meta['commit_sha'], 0, 12);
            }
        }
    }

    if (defined('ADMIN_PANEL_VERSION')) {
        array_unshift($versionParts, ADMIN_PANEL_VERSION);
    }

    if (file_exists($assetPath)) {
        $versionParts[] = (string)filemtime($assetPath);
    }

    $version = implode('-', array_filter($versionParts));
    if ($version === '') {
        $version = 'asset';
    }

    $version = preg_replace('/[^A-Za-z0-9._-]/', '-', $version);
    $separator = (strpos($url, '?') === false) ? '?' : '&';

    return $url . $separator . 'v=' . rawurlencode($version);
}

// Helper function for absolute URLs
function absoluteUrl($path = '') {
    if (empty($path)) {
        return BASE_URL;
    }
    
    // Remove leading slash from path
    $path = ltrim($path, '/');
    
    return BASE_URL . '/' . $path;
}

// JavaScript configuration generator
function getJsConfig() {
    return json_encode([
        'basePath' => BASE_PATH,
        'baseUrl' => BASE_URL
    ]);
}
?>
