<?php

namespace DcsStats\Controllers\Admin\Api;

use DcsStats\Core\AdminAuth;
use DcsStats\Core\ApiResponse;
use DcsStats\Core\Csrf;
use DcsStats\Services\Admin\FeatureSettingsService;

final class SettingsController
{
    public function handle(): void
    {
        if (!AdminAuth::check()) {
            ApiResponse::json(['error' => 'Unauthorized'], 401);
            return;
        }

        if (!AdminAuth::can('manage_features')) {
            ApiResponse::json(['error' => 'Insufficient permissions'], 403);
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $currentAdmin = getCurrentAdmin();

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            require_once DCS_ROOT_PATH . '/site-config/demo_helpers.php';
            blockDemoWriteRequest($currentAdmin, true);
        }

        $service = new FeatureSettingsService();

        switch ($method) {
            case 'GET':
                ApiResponse::json($service->getSettings());
                return;

            case 'POST':
            case 'PUT':
                $input = json_decode(file_get_contents('php://input'), true) ?: [];
                Csrf::requireValid($input);
                $result = $service->updateAll($input, $currentAdmin);
                ApiResponse::json($result['payload'], $result['status']);
                return;

            case 'PATCH':
                $input = json_decode(file_get_contents('php://input'), true) ?: [];
                Csrf::requireValid($input);
                $result = $service->toggle($input, $currentAdmin);
                ApiResponse::json($result['payload'], $result['status']);
                return;

            default:
                ApiResponse::json(['error' => 'Method not allowed'], 405);
                return;
        }
    }
}

