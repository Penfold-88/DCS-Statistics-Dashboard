<?php

namespace DcsStats\Controllers\Api;

use DcsStats\Services\Api\ApiProxyService;

final class ProxyController
{
    public function handle(): void
    {
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');

        $endpoint = (string)($_GET['endpoint'] ?? '');
        $method = strtoupper((string)($_GET['method'] ?? 'GET'));
        $data = [];

        if ($method === 'POST') {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true) ?? [];
        }

        (new ApiProxyService())->handle($endpoint, $method, $data);
    }
}

