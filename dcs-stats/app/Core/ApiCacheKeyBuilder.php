<?php

namespace DcsStats\Core;

final class ApiCacheKeyBuilder
{
    public function build(string $method, string $baseUrl, string $endpoint, $data = null): string
    {
        $payload = [
            'method' => strtoupper($method),
            'base_url' => rtrim($baseUrl, '/'),
            'endpoint' => $endpoint,
            'data' => $data,
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
