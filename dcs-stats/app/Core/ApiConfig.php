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
        $default = self::defaults();
        $fixed = false;

        if (!is_array($config)) {
            return ['config' => $default, 'fixed' => true, 'changes' => ['Replaced invalid config with default']];
        }

        $changes = [];

        if (empty($config['api_host']) && !empty($config['api_base_url'])) {
            $config['api_host'] = preg_replace('#^https?://#', '', $config['api_base_url']);
            $changes[] = 'Extracted api_host from api_base_url';
            $fixed = true;
        }

        if (!empty($config['api_host']) && empty($config['api_base_url'])) {
            $config['api_base_url'] = 'https://' . $config['api_host'];
            $changes[] = 'Generated api_base_url from api_host';
            $fixed = true;
        }

        $requiredFields = ['timeout', 'cache_ttl', 'refresh_interval', 'use_api', 'verify_ssl'];
        foreach ($requiredFields as $field) {
            if (!isset($config[$field])) {
                $config[$field] = $default[$field];
                $changes[] = "Added missing field: $field";
                $fixed = true;
            }
        }

        if (isset($config['fallback_to_json'])) {
            unset($config['fallback_to_json']);
            $changes[] = 'Removed deprecated fallback_to_json setting';
            $fixed = true;
        }

        if (!isset($config['enabled_endpoints']) || !is_array($config['enabled_endpoints'])) {
            $config['enabled_endpoints'] = $default['enabled_endpoints'];
            $changes[] = 'Added all enabled endpoints';
            $fixed = true;
        } else {
            $missingEndpoints = array_diff($default['enabled_endpoints'], $config['enabled_endpoints']);
            if (!empty($missingEndpoints)) {
                $config['enabled_endpoints'] = array_unique(array_merge($config['enabled_endpoints'], $missingEndpoints));
                $changes[] = 'Added missing endpoints: ' . implode(', ', $missingEndpoints);
                $fixed = true;
            }
        }

        if (!isset($config['endpoints']) || !is_array($config['endpoints'])) {
            $config['endpoints'] = $default['endpoints'];
            $changes[] = 'Added endpoint mappings';
            $fixed = true;
        } else {
            foreach ($default['endpoints'] as $key => $value) {
                if (!isset($config['endpoints'][$key])) {
                    $config['endpoints'][$key] = $value;
                    $changes[] = "Added missing endpoint mapping: $key";
                    $fixed = true;
                }
            }
        }

        if (!is_int($config['timeout']) || $config['timeout'] < 1) {
            $config['timeout'] = 30;
            $changes[] = 'Fixed invalid timeout value';
            $fixed = true;
        }

        if (!is_int($config['cache_ttl']) || $config['cache_ttl'] < 0) {
            $config['cache_ttl'] = 300;
            $changes[] = 'Fixed invalid cache_ttl value';
            $fixed = true;
        }

        if (!is_int($config['refresh_interval']) || $config['refresh_interval'] < 60) {
            $config['refresh_interval'] = 300;
            $changes[] = 'Fixed invalid refresh_interval value';
            $fixed = true;
        }

        $config['verify_ssl'] = filter_var($config['verify_ssl'], FILTER_VALIDATE_BOOLEAN);

        if ($config['use_api'] !== true) {
            $config['use_api'] = true;
            $changes[] = 'Set use_api to true (API-only mode)';
            $fixed = true;
        }

        return [
            'config' => $config,
            'fixed' => $fixed,
            'changes' => $changes,
        ];
    }

    public static function writablePath(?string $preferredFile = null): string
    {
        if ($preferredFile === null) {
            $legacyFile = DCS_ROOT_PATH . '/api_config.json';
            if (file_exists($legacyFile)) {
                return $legacyFile;
            }
            $preferredFile = DCS_ROOT_PATH . '/site-config/data/api_config.json';
        }

        if (file_exists($preferredFile) && is_writable($preferredFile)) {
            return $preferredFile;
        }

        $dir = dirname($preferredFile);
        if (is_dir($dir) && is_writable($dir)) {
            return $preferredFile;
        }

        $dataDir = DCS_ROOT_PATH . '/site-config/data/';
        if (is_dir($dataDir) && is_writable($dataDir)) {
            return $dataDir . 'api_config.json';
        }

        $dataDir = DCS_ROOT_PATH . '/data/';
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0700, true);
            @chmod($dataDir, 0700);
        }
        if (is_writable($dataDir)) {
            return $dataDir . 'api_config.json';
        }

        if (is_writable(DCS_ROOT_PATH)) {
            return DCS_ROOT_PATH . '/api_config.json';
        }

        $tempDir = sys_get_temp_dir() . '/dcs_stats/';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0700, true);
        }

        return $tempDir . 'api_config.json';
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
