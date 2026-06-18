<?php

namespace DcsStats\Core;

final class ApiConfig
{
    public static function defaults(string $apiHost = ''): array
    {
        return [
            'api_host' => $apiHost,
            'api_base_url' => $apiHost ? 'http://' . $apiHost : '',
            'api_key' => null,
            'timeout' => 30,
            'cache_ttl' => 300,
            'refresh_interval' => 300,
            'verify_ssl' => true,
            'use_api' => true,
            'enabled_endpoints' => ApiEndpointCatalog::dashboardEndpoints(),
            'endpoints' => ApiEndpointCatalog::restEndpointMappings(),
        ];
    }

    public static function environmentApiKey(): ?string
    {
        $apiKey = getenv('DCSBOT_API_KEY');
        if ($apiKey === false || trim((string)$apiKey) === '') {
            return null;
        }

        return trim((string)$apiKey);
    }

    public static function isEnvironmentApiKeyActive(): bool
    {
        return self::environmentApiKey() !== null;
    }

    public static function applyEnvironmentOverrides($config): array
    {
        if (!is_array($config)) {
            $config = self::defaults();
        }

        $envApiKey = self::environmentApiKey();
        if ($envApiKey !== null) {
            $config['api_key'] = $envApiKey;
        }

        return $config;
    }

    public static function validateAndFix($config): array
    {
        return (new ApiConfigValidator())->validateAndFix($config);
    }

    public static function writablePath(?string $preferredFile = null): string
    {
        return (new ApiConfigPathResolver())->writablePath($preferredFile);
    }

    public static function loadWithFix(?string $configFile = null): array
    {
        return (new ApiConfigStorage())->loadWithFix($configFile);
    }

    public static function createFromHost(string $apiHost, ?string $configFile = null): array
    {
        return (new ApiConfigStorage())->createFromHost($apiHost, $configFile);
    }
}
