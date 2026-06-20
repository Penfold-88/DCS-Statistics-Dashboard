<?php

namespace DcsStats\Services\Admin;

final class SettingsBackupBuilder
{
    private SettingsBackupFileStore $fileStore;

    public function __construct(?SettingsBackupFileStore $fileStore = null)
    {
        $this->fileStore = $fileStore ?? new SettingsBackupFileStore();
    }

    public function build(): array
    {
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\SupportBootstrap::siteMetadata();

        return [
            'type' => 'dcs_statistics_dashboard_site_settings',
            'schema_version' => 1,
            'exported_at' => gmdate('c'),
            'app_version' => defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : null,
            'includes' => [
                'site_config',
                'site_features',
                'site_metadata',
                'menu_config',
                'chart_theme',
                'header_image_settings',
                'custom_theme_css',
                'header_custom_css',
                'cms_pages',
            ],
            'data' => [
                'site_config' => $this->fileStore->readJsonFile(DCS_ROOT_PATH . '/site_config.json', []),
                'site_features' => \loadSiteFeatures(),
                'site_metadata' => \loadSiteMetadata(),
                'menu_config' => $this->fileStore->readJsonFile($this->fileStore->dataPath('menu_config.json'), null),
                'chart_theme' => $this->fileStore->readJsonFile($this->fileStore->dataPath('chart_theme.json'), null),
                'header_image' => $this->fileStore->readJsonFile($this->fileStore->dataPath('header_image.json'), null),
                'custom_theme_css' => $this->fileStore->readTextFile(DCS_ROOT_PATH . '/custom_theme.css'),
                'header_custom_css' => $this->fileStore->readTextFile(DCS_ROOT_PATH . '/header_custom.css'),
                'cms_pages' => $this->fileStore->readJsonFile($this->fileStore->dataPath('pages.json'), []),
            ],
            'excluded' => [
                'api_config',
                'admin_users',
                'passwords',
                'sessions',
                'logs',
                'bans',
                'maintenance_whitelist',
                'uploads',
                'backups',
                'version_metadata',
            ],
        ];
    }
}
