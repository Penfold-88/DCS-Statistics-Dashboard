<?php

namespace DcsStats\Services\Admin;

final class ApiSettingsActionService
{
    public function handle(array $post, array $apiConfig, string $configFile, bool $envApiKeyActive, bool $demoRestricted): array
    {
        if (!isset($post['csrf_token']) || !\verifyCSRFToken($post['csrf_token'])) {
            return [$apiConfig, ERROR_MESSAGES['csrf_invalid'], 'error', null];
        }

        if ($demoRestricted) {
            return [$apiConfig, \demoWriteLockMessage(), 'error', null];
        }

        switch ($post['action'] ?? '') {
            case 'save':
                return $this->saveConfig($post, $apiConfig, $configFile, $envApiKeyActive);

            case 'test':
                return [$apiConfig, '', '', $this->testConnection($apiConfig, $configFile)];

            case 'clear_cache':
                $removed = \apiCacheClear();
                \logAdminActivity('API_CACHE_CLEAR', $_SESSION['admin_id'], 'settings', 'api_cache', ['removed' => $removed]);
                return [$apiConfig, \dcs_t('admin.api.cache_clear_success', ['count' => number_format($removed)]), 'success', null];
        }

        return [$apiConfig, '', '', null];
    }

    private function saveConfig(array $post, array $apiConfig, string $configFile, bool $envApiKeyActive): array
    {
        $apiHost = trim($post['api_host'] ?? '');
        $apiKeyInput = trim($post['api_key'] ?? '');
        $existingApiKey = $envApiKeyActive ? null : ($apiConfig['api_key'] ?? null);
        $timeout = intval($post['timeout'] ?? 30);
        $cacheTtl = intval($post['cache_ttl'] ?? 300);
        $refreshInterval = intval($post['refresh_interval'] ?? 300);
        $useApi = isset($post['use_api']);
        $saveResult = \createApiConfigFromHost($apiHost, $configFile);

        if (!$saveResult['success']) {
            return [$apiConfig, $saveResult['message'], 'error', null];
        }

        $apiConfig = $saveResult['config'];
        $apiConfig['timeout'] = $timeout;
        $apiConfig['cache_ttl'] = $cacheTtl;
        $apiConfig['refresh_interval'] = in_array($refreshInterval, [300, 600, 1800, 3600], true) ? $refreshInterval : 300;
        $apiConfig['use_api'] = $useApi;

        if ($apiKeyInput !== '') {
            $apiConfig['api_key'] = $apiKeyInput;
        } elseif (!empty($existingApiKey)) {
            $apiConfig['api_key'] = $existingApiKey;
        }

        unset($apiConfig['api_key_source'], $apiConfig['api_key_env_override'], $apiConfig['stored_api_key_present']);

        if (file_put_contents($configFile, json_encode($apiConfig, JSON_PRETTY_PRINT))) {
            \logAdminActivity('API_CONFIG_CHANGE', $_SESSION['admin_id'], 'settings', 'api_config', $apiConfig);
            return [$apiConfig, \dcs_t('admin.api.save_success'), 'success', null];
        }

        return [$apiConfig, '', '', null];
    }

    private function testConnection(array &$apiConfig, string $configFile): array
    {
        \DcsStats\Core\SupportBootstrap::apiClient();

        try {
            $client = \createEnhancedAPIClient();
            $result = $client->request('/servers', null, 'GET');

            if ($result === null) {
                return ['success' => false, 'message' => \dcs_t('admin.api.no_response')];
            }

            $protocol = $client->getDetectedProtocol();
            $detectedUrl = $client->getApiBaseUrl();
            $apiHost = $apiConfig['api_host'] ?? preg_replace('#^https?://#', '', $apiConfig['api_base_url']);
            $apiConfig['api_host'] = $apiHost;
            $apiConfig['api_base_url'] = $detectedUrl;
            $saveConfig = $apiConfig;
            $saveConfig['api_base_url'] = $detectedUrl;

            if (@file_put_contents($configFile, json_encode($saveConfig, JSON_PRETTY_PRINT))) {
                return [
                    'success' => true,
                    'message' => \dcs_t('admin.api.test_success_saved', ['protocol' => $protocol]),
                    'protocol' => $protocol,
                ];
            }

            return [
                'success' => true,
                'message' => \dcs_t('admin.api.test_success', ['protocol' => $protocol]),
                'protocol' => $protocol,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => \dcs_t('admin.api.connect_failed') . ': ' . $e->getMessage(),
            ];
        }
    }
}
