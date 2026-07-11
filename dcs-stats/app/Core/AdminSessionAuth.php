<?php

namespace DcsStats\Core;

final class AdminSessionAuth
{
    public static function attemptLogin($username, $password, bool $remember = false): array
    {
        return (new AdminLoginService())->attempt($username, $password, $remember);
    }

    public static function isLoggedIn(): bool
    {
        return (new AdminSessionLifecycle())->isLoggedIn();
    }

    public static function logout(): void
    {
        (new AdminSessionLifecycle())->logout();
    }

    public static function hasPermission($permission): bool
    {
        return (new AdminPermissionService())->has($permission);
    }

    public static function panelUrl(string $path = ''): string
    {
        return (new AdminPanelUrlResolver())->resolve($path);
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

}
