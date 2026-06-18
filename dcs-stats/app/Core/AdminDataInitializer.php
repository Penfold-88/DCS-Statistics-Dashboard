<?php

namespace DcsStats\Core;

final class AdminDataInitializer
{
    public static function initialize(): void
    {
        $isFirstTime = !is_dir(ADMIN_DATA_DIR);

        if ($isFirstTime && php_sapi_name() !== 'cli') {
            (new AdminSetupProgressRenderer())->show();
        }

        (new AdminDataDirectoryInitializer())->initialize();
    }

    public static function needsInitialization(): bool
    {
        return (new AdminDataDirectoryInitializer())->needsInitialization();
    }
}
