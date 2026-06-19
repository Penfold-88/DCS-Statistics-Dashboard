<?php

namespace DcsStats\Core;

final class SupportBootstrap
{
    public static function load(string ...$supports): void
    {
        $loaders = [
            'url',
            'language',
            'siteFeatures',
            'siteMetadata',
            'apiConfig',
            'apiCache',
            'apiClient',
            'security',
            'chartTheme',
            'devMode',
            'tableResponsive',
            'installCheckin',
            'updateChannel',
            'versionTracker',
        ];

        foreach ($supports as $support) {
            if (!in_array($support, $loaders, true)) {
                throw new \InvalidArgumentException('Unknown support bootstrap: ' . $support);
            }

            call_user_func([self::class, $support]);
        }
    }

    public static function url(): void
    {
        require_once DCS_APP_PATH . '/Support/url_functions.php';
    }

    public static function language(): void
    {
        require_once DCS_APP_PATH . '/Support/localization_functions.php';
    }

    public static function siteFeatures(): void
    {
        require_once DCS_APP_PATH . '/Support/site_feature_functions.php';
    }

    public static function siteMetadata(): void
    {
        require_once DCS_APP_PATH . '/Support/site_metadata_functions.php';
    }

    public static function apiConfig(): void
    {
        require_once DCS_APP_PATH . '/Support/api_config_functions.php';
    }

    public static function apiCache(): void
    {
        require_once DCS_APP_PATH . '/Support/api_cache_functions.php';
    }

    public static function apiClient(): void
    {
        require_once DCS_APP_PATH . '/Services/Api/DcsServerBotApiClient.php';
        require_once DCS_APP_PATH . '/Support/api_client_functions.php';
    }

    public static function security(): void
    {
        require_once DCS_APP_PATH . '/Support/security_functions.php';
    }

    public static function chartTheme(): void
    {
        require_once DCS_APP_PATH . '/Support/chart_theme_functions.php';
    }

    public static function devMode(): void
    {
        require_once DCS_APP_PATH . '/Support/dev_mode_functions.php';
    }

    public static function tableResponsive(): void
    {
        require_once DCS_APP_PATH . '/Support/table_responsive_functions.php';
    }

    public static function installCheckin(): void
    {
        require_once DCS_APP_PATH . '/Config/install_checkin.php';
        require_once DCS_APP_PATH . '/Support/install_checkin_functions.php';
    }

    public static function updateChannel(): void
    {
        require_once DCS_APP_PATH . '/Support/update_channel_functions.php';
    }

    public static function versionTracker(): void
    {
        self::updateChannel();
        require_once DCS_APP_PATH . '/Support/version_tracker_functions.php';
    }
}
