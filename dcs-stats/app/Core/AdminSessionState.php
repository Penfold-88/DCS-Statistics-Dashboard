<?php

namespace DcsStats\Core;

final class AdminSessionState
{
    public static function applyUser(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        $_SESSION['admin_last_activity'] = time();
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
}
