<?php

namespace DcsStats\Services\Admin;

final class AdminUsersActionService
{
    private AdminUserAccountService $accounts;

    public function __construct(?AdminUserAccountService $accounts = null)
    {
        $this->accounts = $accounts ?? new AdminUserAccountService();
    }

    public function handlePost(bool $demoRestricted): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [ERROR_MESSAGES['csrf_invalid'], 'error'];
        }

        if ($demoRestricted) {
            return [\demoWriteLockMessage(), 'error'];
        }

        $currentAdminId = (int)($_SESSION['admin_id'] ?? 0);
        switch ($_POST['action'] ?? '') {
            case 'add_admin':
                return $this->accounts->add($_POST, $currentAdminId);
            case 'remove_admin':
                return $this->accounts->remove((int)($_POST['admin_id'] ?? 0), $currentAdminId);
            case 'toggle_active':
                return $this->accounts->toggleActive((int)($_POST['admin_id'] ?? 0), $currentAdminId);
            case 'reset_password':
                return $this->accounts->resetPassword(
                    (int)($_POST['admin_id'] ?? 0),
                    (string)($_POST['new_password'] ?? ''),
                    $currentAdminId
                );
        }

        return ['', ''];
    }

    public function isProtectedAccount(array $admin): bool
    {
        return $this->accounts->isProtectedAccount($admin);
    }
}
