<?php

namespace DcsStats\Controllers\Admin\Api;

use DcsStats\Core\AdminAuth;
use DcsStats\Core\ApiResponse;
use DcsStats\Core\Csrf;
use DcsStats\Services\Admin\BackupRestoreService;
use DcsStats\Services\Admin\BackupService;

final class BackupsController
{
    public function index(): void
    {
        AdminAuth::requirePermission('manage_updates');

        ApiResponse::json([
            'backups' => (new BackupService())->listBackups(),
        ]);
    }

    public function delete(): void
    {
        AdminAuth::requirePermission('manage_updates');

        require_once DCS_ROOT_PATH . '/site-config/demo_helpers.php';
        if (isDemoRestricted()) {
            ApiResponse::json([
                'success' => false,
                'error' => demoRestrictionMessage(),
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        Csrf::requireValid($input);

        ApiResponse::json((new BackupService())->deleteBackup((string)($input['backup'] ?? '')));
    }

    public function create(): void
    {
        AdminAuth::requirePermission('manage_updates');
        Csrf::requireValid();

        set_time_limit(0);
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');

        require_once DCS_ROOT_PATH . '/site-config/demo_helpers.php';
        if (isDemoRestricted()) {
            $this->logMessage(demoRestrictionMessage());
            return;
        }

        (new BackupService())->createBackup(function (string $message) {
            $this->logMessage($message);
        });
    }

    public function restore(): void
    {
        AdminAuth::requirePermission('manage_updates');

        set_time_limit(0);
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');

        require_once DCS_ROOT_PATH . '/site-config/demo_helpers.php';
        if (isDemoRestricted()) {
            $this->logMessage(demoRestrictionMessage());
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        Csrf::requireValid($input);

        (new BackupRestoreService())->restoreBackup((string)($input['backup'] ?? ''), function (string $message) {
            $this->logMessage($message);
        });
    }

    private function logMessage(string $message): void
    {
        echo $message . "\n";
        @ob_flush();
        flush();
    }
}
