<?php

namespace DcsStats\Services\Admin;

final class ApiSettingsActionService
{
    private ApiSettingsConfigWriter $configWriter;
    private ApiConnectionTestService $connectionTester;

    public function __construct(?ApiSettingsConfigWriter $configWriter = null, ?ApiConnectionTestService $connectionTester = null)
    {
        $this->configWriter = $configWriter ?? new ApiSettingsConfigWriter();
        $this->connectionTester = $connectionTester ?? new ApiConnectionTestService();
    }

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
                return $this->configWriter->save($post, $apiConfig, $configFile, $envApiKeyActive);

            case 'test':
                return [$apiConfig, '', '', $this->connectionTester->test($apiConfig, $configFile)];

            case 'clear_cache':
                $removed = \apiCacheClear();
                \logAdminActivity('API_CACHE_CLEAR', $_SESSION['admin_id'], 'settings', 'api_cache', ['removed' => $removed]);
                return [$apiConfig, \dcs_t('admin.api.cache_clear_success', ['count' => number_format($removed)]), 'success', null];
        }

        return [$apiConfig, '', '', null];
    }

}
