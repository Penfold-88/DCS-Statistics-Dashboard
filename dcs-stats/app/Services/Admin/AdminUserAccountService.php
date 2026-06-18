<?php

namespace DcsStats\Services\Admin;

final class AdminUserAccountService
{
    public function isProtectedAccount(array $admin): bool
    {
        return \isDemoMode() && \isDemoOwner($admin);
    }

    public function add(array $post, int $currentAdminId): array
    {
        $username = trim($post['username'] ?? '');
        $email = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';
        $role = intval($post['role'] ?? ROLE_LSO);

        if ($username === '' || $email === '' || $password === '') {
            return [\dcs_t('admin.admins.error_all_fields'), 'error'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [\dcs_t('admin.admins.error_invalid_email'), 'error'];
        }
        if (strlen($password) < 8) {
            return [\dcs_t('admin.admins.error_password_length'), 'error'];
        }

        $users = \getAdminUsers();
        foreach ($users as $user) {
            if ($user['username'] === $username || $user['email'] === $email) {
                return [\dcs_t('admin.admins.error_exists'), 'error'];
            }
        }

        $users[] = [
            'id' => $this->generateId($users),
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role' => $role,
            'created_at' => date(DATE_FORMAT),
            'last_login' => null,
            'is_active' => true,
            'failed_attempts' => 0,
            'locked_until' => null,
        ];

        \saveAdminUsers($users);
        \logAdminActivity('ADMIN_CREATE', $currentAdminId, 'admin', $username);

        return [SUCCESS_MESSAGES['admin_created'], 'success'];
    }

    public function remove(int $adminId, int $currentAdminId): array
    {
        if ($adminId === $currentAdminId) {
            return [\dcs_t('admin.admins.error_remove_self'), 'error'];
        }

        $users = \getAdminUsers();
        $newUsers = [];
        $removed = false;
        $protected = false;

        foreach ($users as $user) {
            if ((int)$user['id'] === $adminId) {
                if ($this->isProtectedAccount($user)) {
                    $protected = true;
                    $newUsers[] = $user;
                } else {
                    $removed = true;
                    \logAdminActivity('ADMIN_DELETE', $currentAdminId, 'admin', $user['username']);
                }
            } else {
                $newUsers[] = $user;
            }
        }

        if ($protected) {
            return [$this->protectedMessage('deleted'), 'error'];
        }
        if ($removed) {
            \saveAdminUsers($newUsers);
            return [\dcs_t('admin.admins.removed_success'), 'success'];
        }

        return [\dcs_t('admin.admins.not_found'), 'error'];
    }

    public function toggleActive(int $adminId, int $currentAdminId): array
    {
        if ($adminId === $currentAdminId) {
            return [\dcs_t('admin.admins.error_deactivate_self'), 'error'];
        }

        $users = \getAdminUsers();
        foreach ($users as &$user) {
            if ((int)$user['id'] === $adminId) {
                if ($this->isProtectedAccount($user)) {
                    return [$this->protectedMessage('deactivated'), 'error'];
                }

                $user['is_active'] = !$user['is_active'];
                \saveAdminUsers($users);
                $action = $user['is_active'] ? 'activated' : 'deactivated';
                \logAdminActivity('ADMIN_EDIT', $currentAdminId, 'admin', $user['username'], ['action' => $action]);

                return [\dcs_t($user['is_active'] ? 'admin.admins.activated_success' : 'admin.admins.deactivated_success'), 'success'];
            }
        }

        return ['', ''];
    }

    public function resetPassword(int $adminId, string $newPassword, int $currentAdminId): array
    {
        if (strlen($newPassword) < 8) {
            return [\dcs_t('admin.admins.error_password_length'), 'error'];
        }

        $users = \getAdminUsers();
        foreach ($users as &$user) {
            if ((int)$user['id'] === $adminId) {
                if ($this->isProtectedAccount($user)) {
                    return [$this->protectedMessage('password reset'), 'error'];
                }

                $user['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
                \saveAdminUsers($users);
                \logAdminActivity('ADMIN_EDIT', $currentAdminId, 'admin', $user['username'], ['action' => 'password_reset']);

                return [\dcs_t('admin.admins.password_reset_success'), 'success'];
            }
        }

        return ['', ''];
    }

    private function protectedMessage(string $action): string
    {
        $username = \getDemoProtectedUsername();
        $label = $username !== '' ? $username : 'The configured demo owner';
        return $label . ' admin account is protected and cannot be ' . $action . '.';
    }

    private function generateId(array $users): int
    {
        $existingIds = [];
        foreach ($users as $user) {
            $existingIds[(int)($user['id'] ?? 0)] = true;
        }

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $id = random_int(100000, 2147483647);
            if (!isset($existingIds[$id])) {
                return $id;
            }
        }

        $maxId = empty($existingIds) ? 0 : max(array_keys($existingIds));
        return $maxId + random_int(1, 1000);
    }
}
