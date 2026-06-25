<?php

namespace DcsStats\Services\Admin;

final class InstallerPermissionCatalog
{
    public function folders(): array
    {
        return [
            'dcs-stats/site-config/data/' => DCS_ROOT_PATH . '/site-config/data',
            'dcs-stats/uploads/' => DCS_ROOT_PATH . '/uploads',
            'dcs-stats/custom/' => DCS_ROOT_PATH . '/custom',
            'dcs-stats/backups/' => DCS_ROOT_PATH . '/backups',
        ];
    }

    public function files(): array
    {
        return [
            'dcs-stats/site_config.json' => DCS_ROOT_PATH . '/site_config.json',
            'dcs-stats/menu_config.json' => DCS_ROOT_PATH . '/menu_config.json',
            'dcs-stats/custom_theme.css' => DCS_ROOT_PATH . '/custom_theme.css',
            'dcs-stats/header_custom.css' => DCS_ROOT_PATH . '/header_custom.css',
            'dcs-stats/.version_meta.json' => DCS_ROOT_PATH . '/.version_meta.json',
        ];
    }
}
