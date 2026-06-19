<?php

namespace DcsStats\Core;

final class AdminBootstrap
{
    public static function auth(): void
    {
        if (!defined('ADMIN_PANEL')) {
            define('ADMIN_PANEL', true);
        }

        if (!defined('DCS_SKIP_SESSION')) {
            define('DCS_SKIP_SESSION', true);
        }

        AdminConfig::load();
        require_once DCS_APP_PATH . '/Support/admin_auth_functions.php';
    }

    public static function panel(): void
    {
        self::auth();
        require_once DCS_APP_PATH . '/Support/admin_panel_functions.php';
    }

    public static function demo(): void
    {
        require_once DCS_APP_PATH . '/Support/demo_functions.php';
    }

}
