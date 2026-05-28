<?php
/**
 * Lightweight file cache for DCSServerBot API responses.
 */

function apiCacheDirectory() {
    $dir = __DIR__ . '/site-config/data/api-cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    if (is_dir($dir)) {
        @chmod($dir, 0700);
    }
    return $dir;
}

function apiCacheNormalisePath($endpoint) {
    $parts = parse_url((string)$endpoint);
    return $parts['path'] ?? (string)$endpoint;
}

function apiCacheTtl($config) {
    $ttl = isset($config['cache_ttl']) ? (int)$config['cache_ttl'] : 300;
    return max(0, $ttl);
}

function apiCacheTtlForEndpoint($endpoint, $config) {
    $ttl = apiCacheTtl($config);
    $path = apiCacheNormalisePath($endpoint);

    $minimumEndpointTtls = [
        // This endpoint can be very large on busy installs. It mainly powers
        // summary widgets and Top 5 insight cards, so a longer cache is useful.
        '/server_attendance' => 900
    ];

    if (isset($minimumEndpointTtls[$path]) && $ttl > 0) {
        return max($ttl, $minimumEndpointTtls[$path]);
    }

    return $ttl;
}

function apiCacheIsCacheable($method, $endpoint, $config = []) {
    if (apiCacheTtlForEndpoint($endpoint, $config) <= 0) {
        return false;
    }

    $method = strtoupper((string)$method);
    $path = apiCacheNormalisePath($endpoint);

    $cacheableEndpoints = [
        '/credits',
        '/getuser',
        '/highscore',
        '/leaderboard',
        '/modulestats',
        '/player_info',
        '/player_squadrons',
        '/server_attendance',
        '/serverstats',
        '/squadron_credits',
        '/squadron_members',
        '/squadrons',
        '/stats',
        '/topkdr',
        '/topkills',
        '/traps',
        '/trueskill',
        '/weaponpk'
    ];

    if (!in_array($path, $cacheableEndpoints, true)) {
        return false;
    }

    return in_array($method, ['GET', 'POST'], true);
}

function apiCacheKey($method, $baseUrl, $endpoint, $data = null) {
    $payload = [
        'method' => strtoupper((string)$method),
        'base_url' => rtrim((string)$baseUrl, '/'),
        'endpoint' => (string)$endpoint,
        'data' => $data
    ];

    return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function apiCacheFile($key) {
    return apiCacheDirectory() . '/' . $key . '.json';
}

function apiCacheRead($method, $baseUrl, $endpoint, $data, $config) {
    if (!apiCacheIsCacheable($method, $endpoint, $config)) {
        return null;
    }

    $ttl = apiCacheTtlForEndpoint($endpoint, $config);
    $file = apiCacheFile(apiCacheKey($method, $baseUrl, $endpoint, $data));
    if (!is_file($file) || (time() - filemtime($file)) > $ttl) {
        return null;
    }

    $cached = json_decode((string)@file_get_contents($file), true);
    if (!is_array($cached) || !array_key_exists('body', $cached)) {
        return null;
    }

    return $cached;
}

function apiCacheWrite($method, $baseUrl, $endpoint, $data, $config, $body, $httpCode = 200) {
    if (!apiCacheIsCacheable($method, $endpoint, $config) || (int)$httpCode < 200 || (int)$httpCode >= 300) {
        return false;
    }

    $file = apiCacheFile(apiCacheKey($method, $baseUrl, $endpoint, $data));
    $payload = [
        'created_at' => time(),
        'http_code' => (int)$httpCode,
        'body' => $body
    ];

    $saved = @file_put_contents($file, json_encode($payload), LOCK_EX);
    if ($saved !== false) {
        @chmod($file, 0600);
        return true;
    }

    return false;
}

function apiCacheClear() {
    $dir = apiCacheDirectory();
    if (!is_dir($dir)) {
        return 0;
    }

    $removed = 0;
    foreach (glob($dir . '/*.json') ?: [] as $file) {
        if (is_file($file) && @unlink($file)) {
            $removed++;
        }
    }

    return $removed;
}
?>
