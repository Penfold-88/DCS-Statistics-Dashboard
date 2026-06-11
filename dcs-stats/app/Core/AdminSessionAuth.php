<?php

namespace DcsStats\Core;

final class AdminSessionAuth
{
    public static function attemptLogin($username, $password, bool $remember = false): array
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

        session_regenerate_id(true);
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        $_SESSION['admin_last_activity'] = time();
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));

        AdminUsers::update((int)$user['id'], [
            'last_login' => date(DATE_FORMAT),
            'last_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);

        if ($remember) {
            self::rememberUser($user);
        }

        AdminAuditLog::write('LOGIN', $user['id'], 'user', $user['username']);

        return ['success' => true, 'user' => $user];
    }

    public static function isLoggedIn(): bool
    {
        if (!empty($_SESSION['admin_logged_in'])) {
            if (time() - (int)($_SESSION['admin_last_activity'] ?? 0) > SESSION_LIFETIME) {
                self::logout();
                return false;
            }

            $_SESSION['admin_last_activity'] = time();
            return true;
        }

        if (!isset($_COOKIE[ADMIN_COOKIE_NAME])) {
            return false;
        }

        $parts = explode(':', (string)$_COOKIE[ADMIN_COOKIE_NAME], 2);
        if (count($parts) !== 2) {
            self::clearRememberCookie();
            return false;
        }

        [$userId, $token] = $parts;
        $userId = (int)$userId;
        $tokenHash = hash('sha256', $token);

        $sessionsFile = AdminEnvironment::dataFilePath('sessions');
        $sessions = json_decode((string)@file_get_contents($sessionsFile), true) ?: [];
        foreach ($sessions as $session) {
            if (
                (int)($session['admin_id'] ?? 0) === $userId
                && ($session['token_hash'] ?? '') === $tokenHash
                && strtotime((string)($session['expires_at'] ?? '')) > time()
            ) {
                $user = self::findUserById($userId);

                if ($user && !empty($user['is_active'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_role'] = $user['role'];
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_login_time'] = time();
                    $_SESSION['admin_last_activity'] = time();
                    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));

                    return true;
                }
            }
        }

        self::clearRememberCookie();
        return false;
    }

    public static function logout(): void
    {
        if (isset($_SESSION['admin_id'])) {
            AdminAuditLog::write('LOGOUT', $_SESSION['admin_id']);
        }

        $_SESSION = [];
        session_destroy();

        if (isset($_COOKIE[ADMIN_COOKIE_NAME])) {
            self::removeRememberSession((string)$_COOKIE[ADMIN_COOKIE_NAME]);
            self::clearRememberCookie();
        }
    }

    public static function hasPermission($permission): bool
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        $role = $_SESSION['admin_role'] ?? 0;

        if ($role === ROLE_AIR_BOSS) {
            return true;
        }

        if ($role === ROLE_LSO) {
            $customPermFile = DCS_ROOT_PATH . '/site-config/data/lso_permissions.json';
            if (!file_exists($customPermFile)) {
                $customPermFile = DCS_ROOT_PATH . '/lso_permissions.json';
                if (!file_exists($customPermFile)) {
                    $customPermFile = sys_get_temp_dir() . '/dcs_stats/lso_permissions.json';
                }
            }

            if (file_exists($customPermFile)) {
                $customPerms = json_decode((string)file_get_contents($customPermFile), true);
                if ($customPerms && isset($customPerms[$permission])) {
                    return $customPerms[$permission]['enabled'] ?? false;
                }
            }
        }

        $permissions = ROLE_PERMISSIONS[$role] ?? [];
        return in_array($permission, $permissions);
    }

    public static function panelUrl(string $path = ''): string
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $adminPos = strpos($scriptName, '/site-config');

        if ($adminPos !== false) {
            $basePath = substr($scriptName, 0, $adminPos + strlen('/site-config'));
        } else {
            $basePath = rtrim(dirname($scriptName), '/\\');
        }

        return rtrim($basePath, '/') . '/' . ltrim($path, '/');
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . self::panelUrl('login.php'));
            exit;
        }
    }

    public static function requirePermission($permission): void
    {
        self::requireLogin();

        if (!self::hasPermission($permission)) {
            http_response_code(403);
            die(ERROR_MESSAGES['access_denied']);
        }
    }

    private static function rememberUser(array $user): void
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $sessionsFile = AdminEnvironment::dataFilePath('sessions');
        $sessions = json_decode((string)@file_get_contents($sessionsFile), true) ?: [];
        $sessions[] = [
            'id' => count($sessions) + 1,
            'admin_id' => $user['id'],
            'token_hash' => $tokenHash,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'expires_at' => date(DATE_FORMAT, time() + ADMIN_COOKIE_LIFETIME),
            'created_at' => date(DATE_FORMAT),
        ];

        @file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod($sessionsFile, 0600);

        setcookie(
            ADMIN_COOKIE_NAME,
            $user['id'] . ':' . $token,
            time() + ADMIN_COOKIE_LIFETIME,
            '/',
            '',
            ENFORCE_HTTPS,
            true
        );
    }

    private static function findUserById(int $userId): ?array
    {
        foreach (AdminUsers::all() as $user) {
            if ((int)($user['id'] ?? 0) === $userId) {
                return $user;
            }
        }

        return null;
    }

    private static function clearRememberCookie(): void
    {
        setcookie(ADMIN_COOKIE_NAME, '', time() - 3600, '/');
    }

    private static function removeRememberSession(string $cookieValue): void
    {
        $parts = explode(':', $cookieValue, 2);
        if (count($parts) !== 2) {
            return;
        }

        [$userId, $token] = $parts;
        $tokenHash = hash('sha256', $token);

        $sessionsFile = AdminEnvironment::dataFilePath('sessions');
        $sessions = json_decode((string)@file_get_contents($sessionsFile), true) ?: [];
        $sessions = array_filter($sessions, function ($session) use ($userId, $tokenHash) {
            return !((int)($session['admin_id'] ?? 0) === (int)$userId && ($session['token_hash'] ?? '') === $tokenHash);
        });

        @file_put_contents($sessionsFile, json_encode(array_values($sessions), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod($sessionsFile, 0600);
    }
}
