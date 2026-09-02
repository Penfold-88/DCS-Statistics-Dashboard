<?php

namespace DcsStats\Services\Admin;

final class ApiHealthCheckService
{
    public function run($baseUrl, $apiKey, $timeout, $endpoint): array
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
        curl_setopt($ch, CURLOPT_TIMEOUT, max(5, min(60, (int)$timeout)));
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

        $authenticationFailed = in_array((int)$httpCode, [401, 403], true);
        return [
            'status' => ($httpCode >= 200 && $httpCode < 300) ? \dcs_t('admin.api_health.ok') : 'HTTP ' . $httpCode,
            'ok' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'time_ms' => $timeMs,
            'summary' => $authenticationFailed
                ? \dcs_t('admin.api_health.auth_failed')
                : $this->summarizePayload($raw),
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
}
