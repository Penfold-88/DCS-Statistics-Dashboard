<?php

namespace DcsStats\Core;

final class AdminAuth
{
    public static function loadLegacyAuth(): void
    {
        require_once DCS_ROOT_PATH . '/site-config/auth.php';
    }

    public static function check(): bool
    {
        self::loadLegacyAuth();

        return isAdminLoggedIn();
    }

    public static function requireLogin(): void
    {
        self::loadLegacyAuth();
        requireAdmin();
    }

    public static function can(string $permission): bool
    {
        self::loadLegacyAuth();

        return hasPermission($permission);
    }

    public static function requirePermission(string $permission): void
    {
        self::loadLegacyAuth();
        requirePermission($permission);
    }

    public static function logout(): void
    {
        self::loadLegacyAuth();
        logout();
    }
}

