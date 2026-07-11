<?php

namespace DcsStats\Services\Admin;

final class ApiHealthPageService
{
    private ApiHealthEndpointCatalog $endpointCatalog;
    private ApiHealthCheckService $checkService;

    public function __construct(
        ?ApiHealthEndpointCatalog $endpointCatalog = null,
        ?ApiHealthCheckService $checkService = null
    ) {
        $this->endpointCatalog = $endpointCatalog ?? new ApiHealthEndpointCatalog();
        $this->checkService = $checkService ?? new ApiHealthCheckService();
    }

    public function state(): array
    {
        \DcsStats\Core\SupportBootstrap::apiConfig();
        \DcsStats\Core\SupportBootstrap::language();

        $configResult = \loadApiConfigWithFix();
        $apiConfig = $configResult['config'];
        $apiBaseUrl = rtrim((string)($apiConfig['api_base_url'] ?? ''), '/');
        $healthEndpoints = $this->endpointCatalog->endpoints();
        $runChecks = isset($_POST['run_checks']) && \verifyCSRFToken($_POST['csrf_token'] ?? '');
        $checkResults = [];

        if ($runChecks) {
            foreach ($healthEndpoints as $endpoint) {
                $checkResults[$endpoint['endpoint']] = $this->checkService->run(
                    $apiBaseUrl,
                    $apiConfig['api_key'] ?? '',
                    max(5, min(60, (int)($apiConfig['timeout'] ?? 30))),
                    $endpoint
                );
            }

            \logAdminActivity('API_HEALTH_CHECK', $_SESSION['admin_id'], 'settings', 'api_health', [
                'endpoints' => count($healthEndpoints),
            ]);
        }

        $refreshInterval = max(60, (int)($apiConfig['refresh_interval'] ?? 300));
        $timeout = max(5, min(60, (int)($apiConfig['timeout'] ?? 30)));
        $cacheTtl = max(0, (int)($apiConfig['cache_ttl'] ?? 300));

        return [
            'apiBaseUrl' => $apiBaseUrl,
            'apiConfig' => $apiConfig,
            'apiHost' => $apiConfig['api_host'] ?? preg_replace('#^https?://#', '', $apiBaseUrl),
            'cacheTtl' => $cacheTtl,
            'checkResults' => $checkResults,
            'configFile' => $configResult['config_path'],
            'formattedCacheTtl' => $this->formatSeconds($cacheTtl),
            'formattedRefreshInterval' => $this->formatSeconds($refreshInterval),
            'formattedTimeout' => $this->formatSeconds($timeout),
            'hasApiKey' => !empty($apiConfig['api_key']),
            'healthEndpoints' => $healthEndpoints,
            'pageTitle' => \dcs_t('admin.api_health.title'),
            'refreshInterval' => $refreshInterval,
            'runChecks' => $runChecks,
            'timeout' => $timeout,
        ];
    }

    public function formatSeconds($seconds): string
    {
        $seconds = (int)$seconds;
        if ($seconds >= 3600 && $seconds % 3600 === 0) {
            return \dcs_t($seconds === 3600 ? 'admin.api_health.one_hour' : 'admin.api_health.hours', ['count' => ($seconds / 3600)]);
        }
        if ($seconds >= 60 && $seconds % 60 === 0) {
            return \dcs_t('admin.api_health.minutes', ['count' => ($seconds / 60)]);
        }
        return \dcs_t('admin.api_health.seconds', ['count' => $seconds]);
    }

}
