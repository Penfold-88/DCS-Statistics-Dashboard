<?php

namespace DcsStats\Services\Api;

final class DcsServerBotHttpClient
{
    public function request(
        string $method,
        string $baseUrl,
        string $endpoint,
        ?array $data,
        array $config,
        ?string $apiKey,
        int $timeout
    ): array {
        $method = strtoupper($method);
        $url = $baseUrl . $endpoint;
        $requestEndpoint = $endpoint;

        if ($method === 'GET' && $data) {
            $requestEndpoint .= '?' . http_build_query($data);
        }

        $cached = apiCacheRead($method, $baseUrl, $requestEndpoint, $method === 'POST' ? $data : null, $config);
        if ($cached !== null) {
            return [
                'body' => $cached['body'],
                'http_code' => $cached['http_code'] ?? 200,
                'cached' => true,
            ];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, max(5, min(60, $timeout)));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $headers = ['Accept: */*'];
        if ($apiKey) {
            $headers[] = 'X-API-Key: ' . $apiKey;
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            }
        } elseif ($method === 'GET' && $data) {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('API request failed: ' . $error);
        }

        if (in_array($httpCode, [401, 403], true)) {
            throw new \Exception('API authentication failed: API key is missing or invalid');
        }

        if ($httpCode >= 400) {
            throw new \Exception('API returned error code: ' . $httpCode);
        }

        apiCacheWrite($method, $baseUrl, $requestEndpoint, $method === 'POST' ? $data : null, $config, $body, $httpCode);

        return [
            'body' => $body,
            'http_code' => $httpCode,
            'cached' => false,
        ];
    }
}
