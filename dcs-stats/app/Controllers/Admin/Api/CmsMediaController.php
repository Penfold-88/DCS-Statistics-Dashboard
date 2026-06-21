<?php

namespace DcsStats\Controllers\Admin\Api;

use DcsStats\Core\AdminAuth;
use DcsStats\Core\AdminBootstrap;
use DcsStats\Core\ApiResponse;
use DcsStats\Core\Csrf;
use DcsStats\Services\Cms\CmsMediaService;

final class CmsMediaController
{
    public function handle(): void
    {
        \DcsStats\Core\SupportBootstrap::language();
        AdminBootstrap::panel();
        if (!AdminAuth::check()) {
            ApiResponse::json(['success' => false, 'message' => \dcs_t('admin.cms.media_unauthorized')], 401);
            return;
        }
        if (!AdminAuth::can('manage_pages')) {
            ApiResponse::json(['success' => false, 'message' => \dcs_t('admin.cms.media_forbidden')], 403);
            return;
        }
        AdminBootstrap::demo();
        $admin = getCurrentAdmin();
        if (\isDemoRestricted($admin)) {
            ApiResponse::json(['success' => false, 'message' => \demoWriteLockMessage()], 403);
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $service = new CmsMediaService();
        if ($method === 'GET') {
            ApiResponse::json(['success' => true, 'items' => $service->items()]);
            return;
        }
        if ($method !== 'POST') {
            ApiResponse::json(['success' => false, 'message' => \dcs_t('admin.cms.invalid_action')], 405);
            return;
        }
        if (!Csrf::verify(Csrf::requestToken())) {
            ApiResponse::json(['success' => false, 'message' => \dcs_t('admin.cms.csrf_invalid')], 403);
            return;
        }

        try {
            $action = (string)($_POST['action'] ?? 'upload');
            if ($action === 'delete') {
                $result = $service->delete((string)($_POST['media_id'] ?? ''));
                if (!empty($result['success'])) {
                    \logAdminActivity('CMS_MEDIA_DELETE', $_SESSION['admin_id'], 'cms_media', (string)($_POST['media_id'] ?? ''), []);
                }
                ApiResponse::json($result, !empty($result['success']) ? 200 : (!empty($result['in_use']) ? 409 : 400));
                return;
            }

            $result = $service->upload($_FILES['image'] ?? null);
            if (!empty($result['success'])) {
                \logAdminActivity('CMS_MEDIA_UPLOAD', $_SESSION['admin_id'], 'cms_media', $result['item']['id'], [
                    'mime' => $result['item']['mime'],
                    'bytes' => $result['item']['bytes'],
                    'width' => $result['item']['width'],
                    'height' => $result['item']['height'],
                ]);
            }
            ApiResponse::json($result, !empty($result['success']) ? 201 : 400);
        } catch (\Throwable $error) {
            error_log('CMS media request failed: ' . $error->getMessage());
            ApiResponse::json(['success' => false, 'message' => \dcs_t('admin.cms.media_server_error')], 500);
        }
    }
}
