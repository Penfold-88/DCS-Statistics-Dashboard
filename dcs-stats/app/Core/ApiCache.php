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
        return (new ApiCachePolicy())->maxFiles($config);
    }

    public static function prune(array $config = []): int
    {
        return (new ApiCachePruner())->prune(self::directory(), $config);
    }

    public static function normalisePath(string $endpoint): string
    {
        return (new ApiCachePolicy())->normalisePath($endpoint);
    }

    public static function ttl(array $config): int
    {
        return (new ApiCachePolicy())->ttl($config);
    }

    public static function ttlForEndpoint(string $endpoint, array $config): int
    {
        return (new ApiCachePolicy())->ttlForEndpoint($endpoint, $config);
    }

    public static function isCacheable(string $method, string $endpoint, array $config = []): bool
    {
        return (new ApiCachePolicy())->isCacheable($method, $endpoint, $config);
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
