<?php

namespace DcsStats\Services\Admin;

final class ApiSettingsConfigWriter
{
    public function save(array $post, array $apiConfig, string $configFile, bool $envApiKeyActive): array
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

        if (\DcsStats\Core\ApiConfig::save($apiConfig, $configFile)) {
            $auditConfig = (new SensitiveDataRedactor())->redact($apiConfig);
            \logAdminActivity('API_CONFIG_CHANGE', $_SESSION['admin_id'], 'settings', 'api_config', $auditConfig);
            return [$apiConfig, \dcs_t('admin.api.save_success'), 'success', null];
        }

        return [$apiConfig, '', '', null];
    }
}
