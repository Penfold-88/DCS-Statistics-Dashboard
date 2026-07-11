<?php

namespace DcsStats\Core;

final class AdminLoginService
{
    public function attempt($username, $password, bool $remember = false): array
    {
        $user = AdminUsers::findByUsernameOrEmail((string)$username);

        if (!$user) {
            AdminAuditLog::write('LOGIN_FAILED', null, 'username', $username, ['reason' => 'User not found']);
            return ['success' => false, 'error' => ERROR_MESSAGES['invalid_credentials']];
        }

        if (AdminUsers::unlockIfExpired($user)) {
            AdminAuditLog::write('LOGIN_FAILED', $user['id'], 'user', $user['username'], ['reason' => 'Account locked']);
            return ['success' => false, 'error' => ERROR_MESSAGES['account_locked']];
        }

        if (empty($user['is_active'])) {
            AdminAuditLog::write('LOGIN_FAILED', $user['id'], 'user', $user['username'], ['reason' => 'Account inactive']);
            return ['success' => false, 'error' => ERROR_MESSAGES['invalid_credentials']];
        }

        if (!password_verify((string)$password, (string)$user['password_hash'])) {
            $failedAttempts = (int)($user['failed_attempts'] ?? 0) + 1;
            $updates = ['failed_attempts' => $failedAttempts];

            if ($failedAttempts >= LOGIN_THROTTLE_ATTEMPTS) {
                $updates['locked_until'] = date(DATE_FORMAT, time() + LOGIN_THROTTLE_WINDOW);
            }

            AdminUsers::update((int)$user['id'], $updates);
            AdminAuditLog::write('LOGIN_FAILED', $user['id'], 'user', $user['username'], ['reason' => 'Invalid password']);

            return ['success' => false, 'error' => ERROR_MESSAGES['invalid_credentials']];
        }

        AdminSessionState::applyUser($user);

        AdminUsers::update((int)$user['id'], [
            'last_login' => date(DATE_FORMAT),
            'last_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);

        if ($remember) {
            AdminRememberMeSessions::createForUser($user);
        }

        AdminAuditLog::write('LOGIN', $user['id'], 'user', $user['username']);

        return ['success' => true, 'user' => $user];
    }
}
