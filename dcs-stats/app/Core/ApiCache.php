<?php

namespace DcsStats\Core;

final class ApiCache
{
    public static function directory(): string
    {
        return (new ApiCacheDirectory())->path();
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
        return (new ApiCacheKeyBuilder())->build($method, $baseUrl, $endpoint, $data);
    }

    public static function file(string $key): string
    {
        return (new ApiCacheDirectory())->file($key);
    }

    public static function read(string $method, string $baseUrl, string $endpoint, $data, array $config): ?array
    {
        return (new ApiCacheStorage())->read($method, $baseUrl, $endpoint, $data, $config);
    }

    public static function write(
        string $method,
        string $baseUrl,
        string $endpoint,
        $data,
        array $config,
        string $body,
        int $httpCode = 200
    ): bool {
        return (new ApiCacheStorage())->write($method, $baseUrl, $endpoint, $data, $config, $body, $httpCode);
    }

    public static function clear(): int
    {
        return (new ApiCacheDirectory())->clear();
    }

    public static function lastRefreshTimestamp(): ?int
    {
        return (new ApiCacheDirectory())->lastRefreshTimestamp();
    }
}
