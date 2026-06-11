<?php

namespace DcsStats\Core;

final class ApiCache
{
    public static function directory(): string
    {
        $dir = DCS_ROOT_PATH . '/site-config/data/api-cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        if (is_dir($dir)) {
            @chmod($dir, 0700);
        }

        return $dir;
    }

    public static function maxFiles(array $config = []): int
    {
        $maxFiles = isset($config['cache_max_files']) ? (int)$config['cache_max_files'] : 500;

        return max(50, $maxFiles);
    }

    public static function prune(array $config = []): int
    {
        $dir = self::directory();
        if (!is_dir($dir)) {
            return 0;
        }

        $markerFile = $dir . '/.last-prune';
        if (is_file($markerFile) && (time() - filemtime($markerFile)) < 7200) {
            return 0;
        }
        @file_put_contents($markerFile, (string)time(), LOCK_EX);
        @chmod($markerFile, 0600);

        $files = glob($dir . '/*.json') ?: [];
        if (empty($files)) {
            return 0;
        }

        $maxAge = max(self::ttl($config), 900) + 300;
        $now = time();
        $removed = 0;
        $remaining = [];

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $fileAge = $now - (int)filemtime($file);
            if ($fileAge > $maxAge) {
                if (@unlink($file)) {
                    $removed++;
                }
                continue;
            }

            $remaining[] = $file;
        }

        $maxFiles = self::maxFiles($config);
        if (count($remaining) > $maxFiles) {
            usort($remaining, function ($a, $b) {
                return filemtime($a) <=> filemtime($b);
            });

            $deleteCount = count($remaining) - $maxFiles;
            for ($i = 0; $i < $deleteCount; $i++) {
                if (@unlink($remaining[$i])) {
                    $removed++;
                }
            }
        }

        return $removed;
    }

    public static function normalisePath(string $endpoint): string
    {
        $parts = parse_url($endpoint);

        return $parts['path'] ?? $endpoint;
    }

    public static function ttl(array $config): int
    {
        $ttl = isset($config['cache_ttl']) ? (int)$config['cache_ttl'] : 300;

        return max(0, $ttl);
    }

    public static function ttlForEndpoint(string $endpoint, array $config): int
    {
        $ttl = self::ttl($config);
        $path = self::normalisePath($endpoint);

        $minimumEndpointTtls = [
            '/server_attendance' => 900,
        ];

        if (isset($minimumEndpointTtls[$path]) && $ttl > 0) {
            return max($ttl, $minimumEndpointTtls[$path]);
        }

        return $ttl;
    }

    public static function isCacheable(string $method, string $endpoint, array $config = []): bool
    {
        if (self::ttlForEndpoint($endpoint, $config) <= 0) {
            return false;
        }

        $method = strtoupper($method);
        $path = self::normalisePath($endpoint);

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
            '/weaponpk',
        ];

        if (!in_array($path, $cacheableEndpoints, true)) {
            return false;
        }

        return in_array($method, ['GET', 'POST'], true);
    }

    public static function key(string $method, string $baseUrl, string $endpoint, $data = null): string
    {
        $payload = [
            'method' => strtoupper($method),
            'base_url' => rtrim($baseUrl, '/'),
            'endpoint' => $endpoint,
            'data' => $data,
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public static function file(string $key): string
    {
        return self::directory() . '/' . $key . '.json';
    }

    public static function read(string $method, string $baseUrl, string $endpoint, $data, array $config): ?array
    {
        if (!self::isCacheable($method, $endpoint, $config)) {
            return null;
        }

        self::prune($config);

        $ttl = self::ttlForEndpoint($endpoint, $config);
        $file = self::file(self::key($method, $baseUrl, $endpoint, $data));
        if (!is_file($file) || (time() - filemtime($file)) > $ttl) {
            return null;
        }

        $cached = json_decode((string)@file_get_contents($file), true);
        if (!is_array($cached) || !array_key_exists('body', $cached)) {
            return null;
        }

        return $cached;
    }

    public static function write(string $method, string $baseUrl, string $endpoint, $data, array $config, string $body, int $httpCode = 200): bool
    {
        if (!self::isCacheable($method, $endpoint, $config) || $httpCode < 200 || $httpCode >= 300) {
            return false;
        }

        $file = self::file(self::key($method, $baseUrl, $endpoint, $data));
        $payload = [
            'created_at' => time(),
            'http_code' => $httpCode,
            'body' => $body,
        ];

        $saved = @file_put_contents($file, json_encode($payload), LOCK_EX);
        if ($saved !== false) {
            @chmod($file, 0600);
            self::prune($config);
            return true;
        }

        return false;
    }

    public static function clear(): int
    {
        $dir = self::directory();
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

    public static function lastRefreshTimestamp(): ?int
    {
        $dir = self::directory();
        if (!is_dir($dir)) {
            return null;
        }

        $latest = null;
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            if (!is_file($file)) {
                continue;
            }

            $timestamp = filemtime($file);
            if ($timestamp !== false && ($latest === null || $timestamp > $latest)) {
                $latest = $timestamp;
            }
        }

        return $latest;
    }
}
