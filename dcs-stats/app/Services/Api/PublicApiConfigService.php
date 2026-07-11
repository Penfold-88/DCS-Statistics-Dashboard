<?php

namespace DcsStats\Services\Api;

final class PublicApiConfigService
{
    public function getBrowserConfig(): array
    {
        \DcsStats\Core\SupportBootstrap::apiConfig();

        $configResult = loadApiConfigWithFix();
        $config = $configResult['config'];

        $proxyAvailable = !empty($config['api_base_url']);

        return [
            'use_api' => !empty($config['use_api']) && $proxyAvailable,
            'proxy_available' => $proxyAvailable,
            'timeout' => $this->safeInt($config['timeout'] ?? 30, 30, 5, 60),
            'api_unavailable_message' => $this->safeMessage($config['unavailable_message'] ?? ''),
            'refresh_interval' => $this->safeInt($config['refresh_interval'] ?? 300, 300, 60, 3600),
        ];
    }

    public function getLeaderboardClientConfig(): array
    {
        return $this->getBrowserConfig();
    }

    private function safeInt($value, int $default, int $min, int $max): int
    {
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        if ($validated === false) {
            return $default;
        }

        return max($min, min($max, $validated));
    }

    private function safeMessage($value): string
    {
        $message = trim((string)$value);
        if ($message === '') {
            return 'API Currently Unavailable';
        }
        $message = preg_replace('/[\x00-\x1F\x7F]/', ' ', $message);
        $message = trim(preg_replace('/\s+/', ' ', (string)$message));
        return substr($message, 0, 160);
    }
}
