<?php

namespace DcsStats\Core;

final class AdminSessionLifecycle
{
    public function isLoggedIn(): bool
    {
        if (!empty($_SESSION['admin_logged_in'])) {
            if (time() - (int)($_SESSION['admin_last_activity'] ?? 0) > SESSION_LIFETIME) {
                $this->logout();
                return false;
            }

            $_SESSION['admin_last_activity'] = time();
            return true;
        }

        return AdminRememberMeSessions::restoreFromCookie($_COOKIE[ADMIN_COOKIE_NAME] ?? null);
    }

    public function logout(): void
    {
        if (isset($_SESSION['admin_id'])) {
            AdminAuditLog::write('LOGOUT', $_SESSION['admin_id']);
        }

        $_SESSION = [];
        session_destroy();

        if (isset($_COOKIE[ADMIN_COOKIE_NAME])) {
            AdminRememberMeSessions::removeCookieSession((string)$_COOKIE[ADMIN_COOKIE_NAME]);
            AdminRememberMeSessions::clearCookie();
        }
    }
}
