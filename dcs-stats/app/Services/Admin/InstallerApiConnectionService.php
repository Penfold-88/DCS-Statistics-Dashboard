<?php

namespace DcsStats\Services\Admin;

final class InstallerApiConnectionService
{
    public function resolve(string $apiUrl, string $apiKey, bool $isDev): array
    {
        $apiUrl = trim($apiUrl);
        if ($apiUrl === '') {
            return ['connected' => false, 'error' => 'API URL is required', 'url' => ''];
        }

        if ($isDev) {
            return [
                'connected' => true,
                'error' => '',
                'url' => preg_match('#^https?://#', $apiUrl) ? $apiUrl : 'https://' . $apiUrl,
            ];
        }

        $host = preg_replace('#^https?://#', '', $apiUrl);
        $authenticationFailed = false;
        foreach (['https', 'http'] as $protocol) {
            $curl = curl_init($protocol . '://' . $host . '/servers');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            if ($apiKey !== '') {
                curl_setopt($curl, CURLOPT_HTTPHEADER, ['X-API-Key: ' . $apiKey]);
            }
            curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode === 200) {
                return ['connected' => true, 'error' => '', 'url' => $protocol . '://' . $host];
            }
            if (in_array($httpCode, [401, 403], true)) {
                $authenticationFailed = true;
            }
        }

        if ($authenticationFailed) {
            return [
                'connected' => false,
                'error' => 'DCSServerBot rejected the API key. Copy the api_key value from config/services/webservice.yaml and try again.',
                'url' => $host,
            ];
        }

        return [
            'connected' => false,
            'error' => "Could not connect to DCSServerBot API at $host. Please ensure:<br>
                • DCSServerBot is running<br>
                • The REST API is enabled in DCSServerBot<br>
                • The address and port are correct (default port is 9876)<br>
                • The API key matches api_key in config/services/webservice.yaml<br>
                • Firewall allows connections to the API port",
            'url' => $host,
        ];
    }
}
