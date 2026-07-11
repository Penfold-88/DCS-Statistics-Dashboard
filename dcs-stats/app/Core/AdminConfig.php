<?php

namespace DcsStats\Core;

class AdminConfig
{
    public static function load(): void
    {
        // Keep this literal here: the updater replaces it for versioned releases.
        self::define('ADMIN_PANEL_VERSION', 'V1.3');
        self::defineAll((new AdminScalarConfigProvider())->values());
        self::defineAll((new AdminRoleConfigProvider())->values());
        self::defineAll((new AdminMessageConfigProvider())->values());
    }

    private static function defineAll(array $values): void
    {
        foreach ($values as $name => $value) {
            self::define($name, $value);
        }
    }

    private static function define(string $name, $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}
