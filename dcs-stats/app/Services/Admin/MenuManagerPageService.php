<?php

namespace DcsStats\Services\Admin;

use DcsStats\Services\MenuManagerService;

final class MenuManagerPageService
{
    public function state(array $currentAdmin): array
    {
        $manager = new MenuManagerService();
        $items = $manager->items();
        $message = '';
        $messageType = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!\verifyCSRFToken(\getRequestCSRFToken())) {
                $message = \dcs_t('admin.menu_manager.csrf_invalid');
                $messageType = 'error';
            } elseif (\isDemoRestricted($currentAdmin)) {
                $message = \demoWriteLockMessage();
                $messageType = 'error';
            } else {
                $items = $manager->fromPost($_POST, $items);
                if ($manager->save($items)) {
                    \logAdminActivity('MENU_UPDATE', $_SESSION['admin_id'], 'settings', 'public_menu', ['item_count' => count($items)]);
                    $message = \dcs_t('admin.menu_manager.saved');
                    $messageType = 'success';
                } else {
                    $message = \dcs_t('admin.menu_manager.save_failed');
                    $messageType = 'error';
                }
            }
        }
        return compact('items', 'message', 'messageType') + [
            'pageTitle' => \dcs_t('admin.menu_manager.title'),
            'csrfToken' => \getCSRFToken(),
        ];
    }
}
