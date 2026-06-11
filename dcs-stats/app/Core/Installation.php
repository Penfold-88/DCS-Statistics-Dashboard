<?php

namespace DcsStats\Core;

final class Installation
{
    public static function isConfigured(): bool
    {
        $apiConfigExists = file_exists(DCS_ROOT_PATH . '/api_config.json')
            || file_exists(DCS_ROOT_PATH . '/site-config/data/api_config.json');

        return $apiConfigExists
            && file_exists(DCS_ROOT_PATH . '/site-config/data/users.json');
    }

    public static function redirectToInstaller(): void
    {
        header('Location: ' . url('site-config/install.php'));
        exit;
    }
}

