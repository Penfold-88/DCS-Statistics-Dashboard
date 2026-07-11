<?php

namespace DcsStats\Controllers\Api;

use DcsStats\Services\Api\ApiProxyService;
use DcsStats\Services\Api\PublicApiRequestGuard;

final class ProxyController
{
    public function handle(): void
    {
        $requestGuard = new PublicApiRequestGuard();
        $requestGuard->prepareJson();
        header('Content-Type: application/json');
        header('Cache-Control: no-store');

        if (!$requestGuard->allow(240, 60)) {
            return;
        }

        $endpoint = (string)($_GET['endpoint'] ?? '');
        $method = strtoupper((string)($_GET['method'] ?? 'GET'));
        $requestMethod = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if (!in_array($method, ['GET', 'POST'], true) || $method !== $requestMethod) {
            http_response_code(405);
            header('Allow: GET, POST');
            echo json_encode(['error' => 'Request method not allowed']);
            return;
        }

        $data = [];

        if ($method === 'POST') {
            $input = file_get_contents('php://input');
            if ($input === false || strlen($input) > 65536) {
                http_response_code(413);
                echo json_encode(['error' => 'Request body too large']);
                return;
            }
            $data = json_decode($input, true) ?? [];
            if (!is_array($data)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON request body']);
                return;
            }
        }

        (new ApiProxyService())->handle($endpoint, $method, $data);
    }
}
