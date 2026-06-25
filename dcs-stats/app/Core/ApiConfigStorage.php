<?php

namespace DcsStats\Core;

final class ApiConfigStorage
{
    private ApiConfigPathResolver $pathResolver;
    private ApiConfigValidator $validator;
    private ApiKeyVault $keyVault;

    public function __construct(
        ?ApiConfigPathResolver $pathResolver = null,
        ?ApiConfigValidator $validator = null,
        ?ApiKeyVault $keyVault = null
    )
    {
        $this->pathResolver = $pathResolver ?? new ApiConfigPathResolver();
        $this->validator = $validator ?? new ApiConfigValidator();
        $this->keyVault = $keyVault ?? new ApiKeyVault();
    }

    public function loadWithFix(?string $configFile = null): array
    {
        $configFile = $this->pathResolver->writablePath($configFile);

        if (!file_exists($configFile)) {
            $defaultConfig = ApiConfig::defaults();
            $this->save($defaultConfig, $configFile);
            return [
                'config' => ApiConfig::applyEnvironmentOverrides($defaultConfig),
                'created' => true,
                'fixed' => false,
                'changes' => ['Created new configuration file'],
                'config_path' => $configFile,
            ];
        }

        $configData = file_get_contents($configFile);
        $storedConfig = json_decode((string)$configData, true);
        $storedConfig = is_array($storedConfig) ? $storedConfig : [];
        $hadPlaintextKey = $this->keyVault->containsPlaintextKey($storedConfig);
        $config = $this->keyVault->hydrate($storedConfig, $configFile);
        $result = $this->validator->validateAndFix($config);
        if (!empty($storedConfig['api_key_encrypted']) && empty($config['api_key'])) {
            $result['changes'][] = 'Stored API key could not be decrypted';
        }

        if ($result['fixed'] || $hadPlaintextKey) {
            if ($this->save($result['config'], $configFile) && $hadPlaintextKey) {
                $result['changes'][] = 'Encrypted the stored API key';
                $result['fixed'] = true;
            }
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
            $storedConfig = json_decode((string)file_get_contents($configFile), true) ?: [];
            $existingConfig = $this->keyVault->hydrate($storedConfig, $configFile);
        }

        $newConfig = ApiConfig::defaults($apiHost);

        if (isset($existingConfig['api_key'])) {
            $newConfig['api_key'] = $existingConfig['api_key'];
        }
        if (!empty($existingConfig['api_key_encrypted'])) {
            $newConfig['api_key_encrypted'] = $existingConfig['api_key_encrypted'];
        }

        if (isset($existingConfig['api_base_url']) && strpos($existingConfig['api_base_url'], 'http://') === 0) {
            $newConfig['api_base_url'] = 'http://' . $apiHost;
        }

        if (!$this->save($newConfig, $configFile)) {
            return [
                'success' => false,
                'config' => $newConfig,
                'config_path' => $configFile,
                'message' => 'Could not save API configuration',
            ];
        }

        return [
            'success' => true,
            'config' => $newConfig,
            'config_path' => $configFile,
            'message' => 'API configuration saved successfully' .
                ($configFile !== DCS_ROOT_PATH . '/api_config.json' ? ' (using alternative location)' : ''),
        ];
    }

    public function save(array $config, string $configFile): bool
    {
        try {
            $storedConfig = $this->keyVault->protect($config, $configFile);
        } catch (\Throwable $e) {
            error_log('DCS Statistics API-key encryption failed: ' . $e->getMessage());
            return false;
        }
        $json = json_encode($storedConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || file_put_contents($configFile, $json, LOCK_EX) === false) {
            return false;
        }

        chmod($configFile, 0600);
        return true;
    }
}
