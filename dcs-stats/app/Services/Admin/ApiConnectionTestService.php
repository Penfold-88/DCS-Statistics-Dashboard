<?php

namespace DcsStats\Services\Admin;

final class ApiConnectionTestService
{
    public function test(array &$apiConfig, string $configFile): array
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
