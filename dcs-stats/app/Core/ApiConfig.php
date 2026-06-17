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
        $configFile = self::writablePath($configFile);

        if (!file_exists($configFile)) {
            $defaultConfig = self::defaults();
            @file_put_contents($configFile, json_encode($defaultConfig, JSON_PRETTY_PRINT));
            return [
                'config' => self::applyEnvironmentOverrides($defaultConfig),
                'created' => true,
                'fixed' => false,
                'changes' => ['Created new configuration file'],
                'config_path' => $configFile,
            ];
        }

        $configData = @file_get_contents($configFile);
        $config = json_decode($configData, true);
        $result = self::validateAndFix($config);

        if ($result['fixed']) {
            @file_put_contents($configFile, json_encode($result['config'], JSON_PRETTY_PRINT));
        }

        $result['config_path'] = $configFile;
        $result['config'] = self::applyEnvironmentOverrides($result['config']);

        return $result;
    }

    public static function createFromHost(string $apiHost, ?string $configFile = null): array
    {
        $configFile = self::writablePath($configFile);
        $apiHost = preg_replace('#^https?://#', '', trim($apiHost));

        $existingConfig = [];
        if (file_exists($configFile)) {
            $existingConfig = json_decode((string)@file_get_contents($configFile), true) ?: [];
        }

        $newConfig = self::defaults($apiHost);

        if (isset($existingConfig['api_key'])) {
            $newConfig['api_key'] = $existingConfig['api_key'];
        }

        if (isset($existingConfig['api_base_url']) && strpos($existingConfig['api_base_url'], 'http://') === 0) {
            $newConfig['api_base_url'] = 'http://' . $apiHost;
        }

        @file_put_contents($configFile, json_encode($newConfig, JSON_PRETTY_PRINT));

        return [
            'success' => true,
            'config' => $newConfig,
            'config_path' => $configFile,
            'message' => 'API configuration saved successfully' .
                ($configFile !== DCS_ROOT_PATH . '/api_config.json' ? ' (using alternative location)' : ''),
        ];
    }
}
