<?php

namespace DcsStats\Services\Admin;

final class ApiSettingsPageService
{
    public function state(array $currentAdmin): array
    {
        require_once DCS_ROOT_PATH . '/api_config_helper.php';
        require_once DCS_ROOT_PATH . '/api_cache.php';
        require_once DCS_ROOT_PATH . '/language.php';

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $configResult = \loadApiConfigWithFix();
        $apiConfig = $configResult['config'];
        $configFile = $configResult['config_path'];
        $envApiKeyActive = \isEnvironmentApiKeyActive();
        $autoFixMessage = $this->autoFixMessage($configResult, $configFile, $demoRestricted);
        $message = '';
        $messageType = '';
        $testResult = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$apiConfig, $message, $messageType, $testResult] = $this->handlePost(
                $apiConfig,
                $configFile,
                $envApiKeyActive,
                $demoRestricted
            );
        }

        $apiHostValue = $apiConfig['api_host'] ?? preg_replace('#^https?://#', '', $apiConfig['api_base_url']);

        return [
            'apiConfig' => $apiConfig,
            'apiHostValue' => $apiHostValue,
            'autoFixMessage' => $autoFixMessage,
            'configFile' => $configFile,
            'demoRestricted' => $demoRestricted,
            'displayApiHost' => $demoRestricted ? \maskDemoValue($apiHostValue) : $apiHostValue,
            'envApiKeyActive' => $envApiKeyActive,
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.api.title'),
            'testResult' => $testResult,
        ];
    }

    private function autoFixMessage(array $configResult, string $configFile, bool $demoRestricted): string
    {
        $message = '';

        if (isset($configResult['fixed']) && $configResult['fixed'] && !empty($configResult['changes'])) {
            $message = \dcs_t('admin.api.auto_fixed') . ': ' . implode(', ', $configResult['changes']);
        }

        if (!$demoRestricted && $configFile !== DCS_ROOT_PATH . '/api_config.json') {
            $message .= ($message ? ' | ' : '') . \dcs_t('admin.api.config_location') . ': ' . $configFile;
        }

        return $message;
    }

    private function handlePost(array $apiConfig, string $configFile, bool $envApiKeyActive, bool $demoRestricted): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [$apiConfig, ERROR_MESSAGES['csrf_invalid'], 'error', null];
        }

        if ($demoRestricted) {
            return [$apiConfig, \demoWriteLockMessage(), 'error', null];
        }

        switch ($_POST['action'] ?? '') {
            case 'save':
                return $this->saveConfig($apiConfig, $configFile, $envApiKeyActive);

            case 'test':
                return [$apiConfig, '', '', $this->testConnection($apiConfig, $configFile)];

            case 'clear_cache':
                $removed = \apiCacheClear();
                \logAdminActivity('API_CACHE_CLEAR', $_SESSION['admin_id'], 'settings', 'api_cache', ['removed' => $removed]);
                return [$apiConfig, \dcs_t('admin.api.cache_clear_success', ['count' => number_format($removed)]), 'success', null];
        }

        return [$apiConfig, '', '', null];
    }

    private function saveConfig(array $apiConfig, string $configFile, bool $envApiKeyActive): array
    {
        $apiHost = trim($_POST['api_host'] ?? '');
        $apiKeyInput = trim($_POST['api_key'] ?? '');
        $existingApiKey = $envApiKeyActive ? null : ($apiConfig['api_key'] ?? null);
        $timeout = intval($_POST['timeout'] ?? 30);
        $cacheTtl = intval($_POST['cache_ttl'] ?? 300);
        $refreshInterval = intval($_POST['refresh_interval'] ?? 300);
        $useApi = isset($_POST['use_api']);
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
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';

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
