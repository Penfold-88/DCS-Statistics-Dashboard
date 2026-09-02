<?php

namespace DcsStats\Services\Admin;

final class ApiSettingsConfigWriter
{
    public function save(array $post, array $apiConfig, string $configFile, bool $envApiKeyActive): array
    {
        $apiHost = trim($post['api_host'] ?? '');
        $apiKeyInput = trim($post['api_key'] ?? '');
        $existingApiKey = $envApiKeyActive ? null : ($apiConfig['api_key'] ?? null);
        if (!$envApiKeyActive && $apiKeyInput === '' && empty($existingApiKey)) {
            return [$apiConfig, \dcs_t('admin.api.api_key_required'), 'error', null];
        }
        $timeout = max(5, min(60, intval($post['timeout'] ?? 30)));
        $unavailableMessage = trim((string)($post['unavailable_message'] ?? ''));
        $unavailableMessage = preg_replace('/[\x00-\x1F\x7F]/', ' ', $unavailableMessage);
        $unavailableMessage = trim(preg_replace('/\s+/', ' ', (string)$unavailableMessage));
        if ($unavailableMessage === '') {
            $unavailableMessage = 'API Currently Unavailable';
        }
        if (strlen($unavailableMessage) > 160) {
            $unavailableMessage = substr($unavailableMessage, 0, 160);
        }
        $cacheTtl = intval($post['cache_ttl'] ?? 300);
        $refreshInterval = intval($post['refresh_interval'] ?? 300);
        $useApi = isset($post['use_api']);
        $saveResult = \DcsStats\Core\ApiConfig::createFromHost($apiHost, $configFile);

        if (!$saveResult['success']) {
            return [$apiConfig, $saveResult['message'], 'error', null];
        }

        $apiConfig = $saveResult['config'];
        $apiConfig['timeout'] = $timeout;
        $apiConfig['unavailable_message'] = $unavailableMessage;
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
