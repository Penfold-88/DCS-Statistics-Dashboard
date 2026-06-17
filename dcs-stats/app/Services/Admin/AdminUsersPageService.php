<?php

namespace DcsStats\Services\Admin;

final class AdminUsersPageService
{
    private AdminUsersActionService $actions;

    public function __construct(?AdminUsersActionService $actions = null)
    {
        $this->actions = $actions ?? new AdminUsersActionService();
    }

    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::language();

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$message, $messageType] = $this->actions->handlePost($demoRestricted);
        }

        $admins = \getAdminUsers();
        $protectedAdminIds = [];
        foreach ($admins as $admin) {
            if ($this->isProtectedAccount($admin)) {
                $protectedAdminIds[(int)($admin['id'] ?? 0)] = true;
            }
        }

        return [
            'admins' => $admins,
            'demoRestricted' => $demoRestricted,
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.admins.title'),
            'protectedAdminIds' => $protectedAdminIds,
        ];
    }

    public function isProtectedAccount(array $admin): bool
    {
        return $this->actions->isProtectedAccount($admin);
    }
}
