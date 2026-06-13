<?php

namespace DcsStats\Services\Api;

final class PublicApiConfigService
{
    public function getBrowserConfig(): array
    {
        \DcsStats\Core\SupportBootstrap::apiConfig();

        $configResult = loadApiConfigWithFix();
        $config = $configResult['config'];

        $proxyAvailable = !empty($config['api_base_url']);

        return [
            'use_api' => !empty($config['use_api']) && $proxyAvailable,
            'proxy_available' => $proxyAvailable,
            'timeout' => $this->safeInt($config['timeout'] ?? 30, 30, 5, 300),
            'refresh_interval' => $this->safeInt($config['refresh_interval'] ?? 300, 300, 60, 3600),
        ];
    }

    public function getLeaderboardClientConfig(): array
    {
        \DcsStats\Core\SupportBootstrap::apiConfig();

        $configResult = loadApiConfigWithFix();
        $config = $configResult['config'];

        return [
            'api_base_url' => $config['api_base_url'] ?? 'http://localhost:8080',
            'use_api' => $config['use_api'] ?? false,
            'timeout' => $config['timeout'] ?? 30,
        ];
    }

    private function safeInt($value, int $default, int $min, int $max): int
    {
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        if ($validated === false) {
            return $default;
        }

        return max($min, min($max, $validated));
    }
}
