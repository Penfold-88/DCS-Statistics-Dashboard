<?php

namespace DcsStats\Services\Admin;

final class AdminAccountStatusService
{
    private AdminAccountRepository $accounts;
    private AdminAccountInputValidator $validator;
    private DemoAdminAccountPolicy $demoPolicy;

    public function __construct(
        AdminAccountRepository $accounts,
        AdminAccountInputValidator $validator,
        DemoAdminAccountPolicy $demoPolicy
    ) {
        $this->accounts = $accounts;
        $this->validator = $validator;
        $this->demoPolicy = $demoPolicy;
    }

    public function toggleActive(int $adminId, int $currentAdminId): array
    {
        if ($adminId === $currentAdminId) {
            return [\dcs_t('admin.admins.error_deactivate_self'), 'error'];
        }

        $users = $this->accounts->all();
        foreach ($users as &$user) {
            if ((int)$user['id'] !== $adminId) {
                continue;
            }
            if ($this->demoPolicy->isProtected($user)) {
                return [$this->demoPolicy->message('deactivated'), 'error'];
            }

            $user['is_active'] = !$user['is_active'];
            $this->accounts->save($users);
            $action = $user['is_active'] ? 'activated' : 'deactivated';
            $this->accounts->log('ADMIN_EDIT', $currentAdminId, $user['username'], ['action' => $action]);

            return [\dcs_t($user['is_active'] ? 'admin.admins.activated_success' : 'admin.admins.deactivated_success'), 'success'];
        }

        return ['', ''];
    }

    public function resetPassword(int $adminId, string $newPassword, int $currentAdminId): array
    {
        $validation = $this->validator->validatePassword($newPassword);
        if ($validation !== null) {
            return $validation;
        }

        $users = $this->accounts->all();
        foreach ($users as &$user) {
            if ((int)$user['id'] !== $adminId) {
                continue;
            }
            if ($this->demoPolicy->isProtected($user)) {
                return [$this->demoPolicy->message('password reset'), 'error'];
            }

            $user['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
            $this->accounts->save($users);
            $this->accounts->log('ADMIN_EDIT', $currentAdminId, $user['username'], ['action' => 'password_reset']);

            return [\dcs_t('admin.admins.password_reset_success'), 'success'];
        }

        return ['', ''];
    }
}
