<?php

namespace DcsStats\Controllers\Admin\Api;

use DcsStats\Core\AdminAuth;
use DcsStats\Core\ApiResponse;
use DcsStats\Core\Csrf;
use DcsStats\Services\Admin\VersionTrackingService;

final class VersionController
{
    public function initialize(): void
    {
        AdminAuth::requirePermission('manage_updates');

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::json([
                'success' => false,
                'error' => 'Method not allowed',
            ], 405);
            return;
        }

        Csrf::requireValid();

        require_once DCS_ROOT_PATH . '/site-config/admin_functions.php';
        require_once DCS_ROOT_PATH . '/site-config/demo_helpers.php';
        blockDemoWriteRequest(getCurrentAdmin(), true);

        try {
            $versionInfo = (new VersionTrackingService())->initialize();

            ApiResponse::json([
                'success' => true,
                'version_info' => $versionInfo,
                'message' => 'Version tracking initialized successfully',
            ]);
        } catch (\Exception $e) {
            ApiResponse::json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
