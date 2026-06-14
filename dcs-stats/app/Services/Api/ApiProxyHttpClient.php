<?php

namespace DcsStats\Services\Api;

final class ApiProxyHttpClient
{
    public function send(array $apiConfig, array $request): array
    {
        $url = rtrim($apiConfig['api_base_url'], '/') . '/' . ltrim($request['path'], '/');
        if (!empty($request['query_params'])) {
            $url .= '?' . http_build_query($request['query_params']);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $apiConfig['timeout'] ?? 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (stripos($url, 'https://') === 0) {
            $verifySsl = filter_var($apiConfig['verify_ssl'] ?? true, FILTER_VALIDATE_BOOLEAN);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySsl ? 2 : 0);
        }

        $headers = [];
        if (!empty($apiConfig['api_key'])) {
            $headers[] = 'X-API-Key: ' . $apiConfig['api_key'];
        }

        if ($request['method'] === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, !empty($request['data']) ? http_build_query($request['data']) : '');
            if (!empty($request['data'])) {
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            }
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'body' => $body,
            'http_code' => $httpCode,
            'error' => $error,
        ];
    }
}
