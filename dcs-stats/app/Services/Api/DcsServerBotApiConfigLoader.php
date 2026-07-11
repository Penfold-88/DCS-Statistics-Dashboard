<?php

namespace DcsStats\Services\Api;

final class DcsServerBotApiConfigLoader
{
    public function load(): array
    {
        $configResult = \loadApiConfigWithFix();
        if (!empty($configResult['config']) && is_array($configResult['config'])) {
            return $configResult['config'];
        }

        return [
            'api_base_url' => getenv('DCSBOT_API_URL') ?: 'http://localhost:8080',
            'api_key' => getenv('DCSBOT_API_KEY') ?: null,
            'timeout' => 30,
            'unavailable_message' => 'API Currently Unavailable',
        ];
    }
}
