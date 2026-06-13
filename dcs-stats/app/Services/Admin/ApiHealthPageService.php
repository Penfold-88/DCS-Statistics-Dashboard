<?php

namespace DcsStats\Services\Admin;

final class ApiHealthPageService
{
    public function state(): array
    {
        \DcsStats\Core\SupportBootstrap::apiConfig();
        \DcsStats\Core\SupportBootstrap::language();

        $configResult = \loadApiConfigWithFix();
        $apiConfig = $configResult['config'];
        $apiBaseUrl = rtrim((string)($apiConfig['api_base_url'] ?? ''), '/');
        $healthEndpoints = $this->healthEndpoints();
        $runChecks = isset($_POST['run_checks']) && \verifyCSRFToken($_POST['csrf_token'] ?? '');
        $checkResults = [];

        if ($runChecks) {
            foreach ($healthEndpoints as $endpoint) {
                $checkResults[$endpoint['endpoint']] = $this->runHealthCheck(
                    $apiBaseUrl,
                    $apiConfig['api_key'] ?? '',
                    max(1, (int)($apiConfig['timeout'] ?? 30)),
                    $endpoint
                );
            }

            \logAdminActivity('API_HEALTH_CHECK', $_SESSION['admin_id'], 'settings', 'api_health', [
                'endpoints' => count($healthEndpoints),
            ]);
        }

        $refreshInterval = max(60, (int)($apiConfig['refresh_interval'] ?? 300));
        $timeout = max(1, (int)($apiConfig['timeout'] ?? 30));
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

    private function healthEndpoints(): array
    {
        return [
            ['label' => \dcs_t('servers.title'), 'method' => 'GET', 'endpoint' => '/servers', 'note' => \dcs_t('admin.api_health.note_servers')],
            ['label' => \dcs_t('admin.api_health.server_stats'), 'method' => 'GET', 'endpoint' => '/serverstats', 'note' => \dcs_t('admin.api_health.note_serverstats')],
            ['label' => \dcs_t('admin.api_health.attendance'), 'method' => 'GET', 'endpoint' => '/server_attendance', 'note' => \dcs_t('admin.api_health.note_attendance')],
            ['label' => \dcs_t('leaderboard.title'), 'method' => 'GET', 'endpoint' => '/leaderboard?what=kills&limit=1', 'note' => \dcs_t('admin.api_health.note_leaderboard')],
            ['label' => \dcs_t('squadrons.title'), 'method' => 'GET', 'endpoint' => '/squadrons', 'note' => \dcs_t('admin.api_health.note_squadrons')],
            ['label' => \dcs_t('admin.api_health.current_server'), 'method' => 'GET', 'endpoint' => '/current_server', 'note' => \dcs_t('admin.api_health.note_current_server')],
        ];
    }

    private function summarizePayload($raw): string
    {
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return \dcs_t('admin.api_health.non_json_response', ['bytes' => strlen((string)$raw)]);
        }

        if (is_array($decoded)) {
            if (array_is_list($decoded)) {
                return \dcs_t(count($decoded) === 1 ? 'admin.api_health.one_list_item' : 'admin.api_health.list_items', ['count' => count($decoded)]);
            }

            if (isset($decoded['items']) && is_array($decoded['items'])) {
                return \dcs_t(count($decoded['items']) === 1 ? 'admin.api_health.one_item_in_items' : 'admin.api_health.items_in_items', ['count' => count($decoded['items'])]);
            }

            if (isset($decoded['error'])) {
                return \dcs_t('admin.api_health.error_prefix') . ': ' . (is_scalar($decoded['error']) ? $decoded['error'] : \dcs_t('admin.api_health.error_object'));
            }

            $keys = array_slice(array_keys($decoded), 0, 8);
            return \dcs_t('admin.api_health.object_keys') . ': ' . implode(', ', $keys);
        }

        return \dcs_t('admin.api_health.json_type', ['type' => gettype($decoded)]);
    }

    private function runHealthCheck($baseUrl, $apiKey, $timeout, $endpoint): array
    {
        if ($baseUrl === '') {
            return [
                'status' => \dcs_t('admin.api_health.not_configured'),
                'ok' => false,
                'http_code' => null,
                'time_ms' => null,
                'summary' => \dcs_t('admin.api_health.empty_base_url'),
            ];
        }

        if (!function_exists('curl_init')) {
            return [
                'status' => \dcs_t('admin.api_health.unavailable'),
                'ok' => false,
                'http_code' => null,
                'time_ms' => null,
                'summary' => \dcs_t('admin.api_health.curl_unavailable'),
            ];
        }

        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint['endpoint'], '/');
        $started = microtime(true);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $endpoint['method']);

        $headers = ['Accept: application/json'];
        if (!empty($apiKey)) {
            $headers[] = 'X-API-Key: ' . $apiKey;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $timeMs = (int)round((microtime(true) - $started) * 1000);

        if ($error) {
            return [
                'status' => \dcs_t('admin.api_health.failed'),
                'ok' => false,
                'http_code' => $httpCode ?: null,
                'time_ms' => $timeMs,
                'summary' => $error,
            ];
        }

        return [
            'status' => ($httpCode >= 200 && $httpCode < 300) ? \dcs_t('admin.api_health.ok') : 'HTTP ' . $httpCode,
            'ok' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'time_ms' => $timeMs,
            'summary' => $this->summarizePayload($raw),
        ];
    }
}
