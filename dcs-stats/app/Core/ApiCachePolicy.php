<?php

namespace DcsStats\Core;

final class ApiCachePolicy
{
    public function maxFiles(array $config = []): int
    {
        $maxFiles = isset($config['cache_max_files']) ? (int)$config['cache_max_files'] : 500;

        return max(50, $maxFiles);
    }

    public function ttl(array $config): int
    {
        $ttl = isset($config['cache_ttl']) ? (int)$config['cache_ttl'] : 300;

        return max(0, $ttl);
    }

    public function ttlForEndpoint(string $endpoint, array $config): int
    {
        $ttl = $this->ttl($config);
        $path = $this->normalisePath($endpoint);

        $minimumEndpointTtls = [
            '/server_attendance' => 900,
        ];

        if (isset($minimumEndpointTtls[$path]) && $ttl > 0) {
            return max($ttl, $minimumEndpointTtls[$path]);
        }

        return $ttl;
    }

    public function isCacheable(string $method, string $endpoint, array $config = []): bool
    {
        if ($this->ttlForEndpoint($endpoint, $config) <= 0) {
            return false;
        }

        $method = strtoupper($method);
        $path = $this->normalisePath($endpoint);

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

    public function normalisePath(string $endpoint): string
    {
        $parts = parse_url($endpoint);

        return $parts['path'] ?? $endpoint;
    }
}
