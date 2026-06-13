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

    public static function includeCompat(string $include): void
    {
        $include = ltrim($include, '/');

        if ($include === 'site-config/auth.php') {
            self::auth();
            return;
        }

        if ($include === 'site-config/admin_functions.php') {
            self::panel();
            return;
        }

        if ($include === 'site-config/demo_helpers.php') {
            self::demo();
            return;
        }

        $supportLoaders = [
            'language.php' => [SupportBootstrap::class, 'language'],
            'site_features.php' => [SupportBootstrap::class, 'siteFeatures'],
            'site_metadata.php' => [SupportBootstrap::class, 'siteMetadata'],
            'api_config_helper.php' => [SupportBootstrap::class, 'apiConfig'],
            'api_cache.php' => [SupportBootstrap::class, 'apiCache'],
            'api_client_enhanced.php' => [SupportBootstrap::class, 'apiClient'],
            'security_functions.php' => [SupportBootstrap::class, 'security'],
            'chart_theme.php' => [SupportBootstrap::class, 'chartTheme'],
            'dev_mode.php' => [SupportBootstrap::class, 'devMode'],
            'table-responsive.php' => [SupportBootstrap::class, 'tableResponsive'],
            'install_checkin.php' => [SupportBootstrap::class, 'installCheckin'],
            'site-config/update_channel.php' => [SupportBootstrap::class, 'updateChannel'],
            'site-config/version_tracker.php' => [SupportBootstrap::class, 'versionTracker'],
            'site-config/config.php' => [AdminConfig::class, 'load'],
            'config_path.php' => [SupportBootstrap::class, 'url'],
        ];

        if (array_key_exists($include, $supportLoaders)) {
            $loader = $supportLoaders[$include];
            if (is_callable($loader)) {
                $loader();
            }
            return;
        }

        require_once DCS_ROOT_PATH . '/' . $include;
    }
}
