<?php

namespace DcsStats\Core;

final class ApiConfigStorage
{
    private ApiConfigPathResolver $pathResolver;
    private ApiConfigValidator $validator;

    public function __construct(?ApiConfigPathResolver $pathResolver = null, ?ApiConfigValidator $validator = null)
    {
        $this->pathResolver = $pathResolver ?? new ApiConfigPathResolver();
        $this->validator = $validator ?? new ApiConfigValidator();
    }

    public function loadWithFix(?string $configFile = null): array
    {
        $configFile = $this->pathResolver->writablePath($configFile);

        if (!file_exists($configFile)) {
            $defaultConfig = ApiConfig::defaults();
            @file_put_contents($configFile, json_encode($defaultConfig, JSON_PRETTY_PRINT));
            return [
                'config' => ApiConfig::applyEnvironmentOverrides($defaultConfig),
                'created' => true,
                'fixed' => false,
                'changes' => ['Created new configuration file'],
                'config_path' => $configFile,
            ];
        }

        $configData = @file_get_contents($configFile);
        $config = json_decode($configData, true);
        $result = $this->validator->validateAndFix($config);

        if ($result['fixed']) {
            @file_put_contents($configFile, json_encode($result['config'], JSON_PRETTY_PRINT));
        }

        $result['config_path'] = $configFile;
        $result['config'] = ApiConfig::applyEnvironmentOverrides($result['config']);

        return $result;
    }

    public function createFromHost(string $apiHost, ?string $configFile = null): array
    {
        $configFile = $this->pathResolver->writablePath($configFile);
        $apiHost = preg_replace('#^https?://#', '', trim($apiHost));

        $existingConfig = [];
        if (file_exists($configFile)) {
            $existingConfig = json_decode((string)@file_get_contents($configFile), true) ?: [];
        }

        $newConfig = ApiConfig::defaults($apiHost);

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
