<?php

namespace DcsStats\Services\Admin;

final class InstallerSupportService
{
    public function validateInputs(array $input): array
    {
        $errors = [];

        if (trim((string)($input['username'] ?? '')) === '') {
            $errors[] = 'Username is required';
        }
        if (trim((string)($input['email'] ?? '')) === '') {
            $errors[] = 'Email is required';
        }
        if (strlen((string)($input['password'] ?? '')) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if (trim((string)($input['api_url'] ?? '')) === '') {
            $errors[] = 'API URL is required';
        }
        if (!$this->isValidApiKey(trim((string)($input['api_key'] ?? '')))) {
            $errors[] = 'API key contains invalid characters';
        }

        return $errors;
    }

    public function isValidApiKey($apiKey): bool
    {
        if ($apiKey === '') {
            return true;
        }

        return strlen($apiKey) <= 256 && preg_match('/^[A-Za-z0-9._~:+\/=-]+$/', $apiKey);
    }

    public function canCreateInPath(string $path): bool
    {
        $parent = dirname($path);
        while ($parent && $parent !== dirname($parent)) {
            if (file_exists($parent)) {
                return is_dir($parent) && is_writable($parent);
            }
            $parent = dirname($parent);
        }

        return false;
    }

    public function pathIsWritable(string $path, string $type): bool
    {
        if ($type === 'dir') {
            return is_dir($path) ? is_writable($path) : $this->canCreateInPath($path);
        }

        return file_exists($path) ? is_writable($path) : $this->canCreateInPath($path);
    }

    public function permissionStatuses(array $paths, string $type): array
    {
        $statuses = [];
        foreach ($paths as $label => $path) {
            $statuses[] = [
                'label' => $label,
                'writable' => $this->pathIsWritable($path, $type),
            ];
        }

        return $statuses;
    }

    public function resolveApiUrl(string $apiUrl, string $apiKey, bool $isDev): array
    {
        $apiUrl = trim($apiUrl);
        if ($apiUrl === '') {
            return [
                'connected' => false,
                'error' => 'API URL is required',
                'url' => '',
            ];
        }

        if ($isDev) {
            return [
                'connected' => true,
                'error' => '',
                'url' => preg_match('#^https?://#', $apiUrl) ? $apiUrl : 'https://' . $apiUrl,
            ];
        }

        $host = preg_replace('#^https?://#', '', $apiUrl);
        foreach (['https', 'http'] as $protocol) {
            $testUrl = $protocol . '://' . $host . '/servers';
            $ch = curl_init($testUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            if ($apiKey !== '') {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-Key: ' . $apiKey]);
            }
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return [
                    'connected' => true,
                    'error' => '',
                    'url' => $protocol . '://' . $host,
                ];
            }
        }

        return [
            'connected' => false,
            'error' => "Could not connect to DCSServerBot API at $host. Please ensure:<br>
                • DCSServerBot is running<br>
                • The REST API is enabled in DCSServerBot<br>
                • The address and port are correct (default port is 9876)<br>
                • The API key is correct if your DCSServerBot REST API requires one<br>
                • Firewall allows connections to the API port",
            'url' => $host,
        ];
    }
}
