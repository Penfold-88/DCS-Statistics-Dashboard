<?php

namespace DcsStats\Services\Admin;

final class AdminAccountRemovalService
{
    private AdminAccountRepository $accounts;
    private DemoAdminAccountPolicy $demoPolicy;

    public function __construct(AdminAccountRepository $accounts, DemoAdminAccountPolicy $demoPolicy)
    {
        $this->accounts = $accounts;
        $this->demoPolicy = $demoPolicy;
    }

    public function remove(int $adminId, int $currentAdminId): array
    {
        if ($adminId === $currentAdminId) {
            return [\dcs_t('admin.admins.error_remove_self'), 'error'];
        }

        $newUsers = [];
        $removed = false;
        $protected = false;
        foreach ($this->accounts->all() as $user) {
            if ((int)$user['id'] !== $adminId) {
                $newUsers[] = $user;
                continue;
            }

            if ($this->demoPolicy->isProtected($user)) {
                $protected = true;
                $newUsers[] = $user;
            } else {
                $removed = true;
                $this->accounts->log('ADMIN_DELETE', $currentAdminId, $user['username']);
            }
        }

        if ($protected) {
            return [$this->demoPolicy->message('deleted'), 'error'];
        }
        if ($removed) {
            $this->accounts->save($newUsers);
            return [\dcs_t('admin.admins.removed_success'), 'success'];
        }

        return [\dcs_t('admin.admins.not_found'), 'error'];
    }
}
