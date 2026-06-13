<?php

namespace DcsStats\Core;

class AdminConfig
{
    public static function load(): void
    {
        self::defineScalarConstants();
        self::defineArrayConstants();
    }

    private static function defineScalarConstants(): void
    {
        self::define('ADMIN_PANEL_VERSION', 'V1.3');
        self::define('ADMIN_SESSION_NAME', 'dcs_admin_session');
        self::define('ADMIN_COOKIE_NAME', 'dcs_admin_remember');
        self::define('ADMIN_COOKIE_LIFETIME', 30 * 24 * 60 * 60);

        self::define('LOGIN_THROTTLE_ATTEMPTS', 5);
        self::define('LOGIN_THROTTLE_WINDOW', 900);
        self::define('CSRF_TOKEN_NAME', 'admin_csrf_token');
        self::define('SESSION_LIFETIME', 3600);
        self::define('ENFORCE_HTTPS', false);

        self::define('ROLE_AIR_BOSS', 2);
        self::define('ROLE_LSO', 1);

        self::define('USE_DATABASE', false);
        self::define('DB_HOST', 'localhost');
        self::define('DB_NAME', 'dcs_stats_admin');
        self::define('DB_USER', 'root');
        self::define('DB_PASS', '');
        self::define('DB_PREFIX', 'dcs_');

        self::define('ADMIN_DATA_DIR', DCS_ROOT_PATH . '/site-config/data/');
        self::define('ADMIN_USERS_FILE', ADMIN_DATA_DIR . 'users.json');
        self::define('ADMIN_LOGS_FILE', ADMIN_DATA_DIR . 'logs.json');
        self::define('ADMIN_BANS_FILE', ADMIN_DATA_DIR . 'bans.json');
        self::define('ADMIN_SESSIONS_FILE', ADMIN_DATA_DIR . 'sessions.json');

        self::define('LOG_ADMIN_ACTIONS', true);
        self::define('LOG_RETENTION_DAYS', 90);
        self::define('MAX_ADMIN_LOGS', 1000);

        self::define('EXPORT_MAX_RECORDS', 10000);
        self::define('RECORDS_PER_PAGE', 25);
        self::define('DATE_FORMAT', 'Y-m-d H:i:s');

        self::define('DEFAULT_ADMIN_USERNAME', 'admin');
        self::define('DEFAULT_ADMIN_EMAIL', 'admin@example.com');
        self::define('DEFAULT_ADMIN_PASSWORD', null);
    }

    private static function defineArrayConstants(): void
    {
        self::define('ROLE_NAMES', [
            ROLE_AIR_BOSS => 'Air Boss',
            ROLE_LSO => 'LSO',
        ]);

        self::define('ROLE_PERMISSIONS', [
            ROLE_AIR_BOSS => [
                'view_dashboard',
                'export_data',
                'view_logs',
                'manage_admins',
                'change_settings',
                'manage_maintenance',
                'manage_updates',
                'manage_api',
                'manage_themes',
                'manage_features',
                'manage_permissions',
                'manage_discord',
                'manage_squadrons',
            ],
            ROLE_LSO => [
                'view_dashboard',
                'export_data',
                'view_logs',
            ],
        ]);

        self::define('EXPORT_FORMATS', ['csv', 'json']);

        self::define('LOG_ACTIONS', [
            'LOGIN' => 'Logged In',
            'LOGOUT' => 'Logged Out',
            'LOGIN_FAILED' => 'Failed Login Attempt',
            'PLAYER_VIEW' => 'Viewed Player Record',
            'PLAYER_EDIT' => 'Updated Player Record',
            'PLAYER_BAN' => 'Banned Player',
            'PLAYER_UNBAN' => 'Unbanned Player',
            'DATA_EXPORT' => 'Exported Data',
            'DATA_EXPORT_DOWNLOAD' => 'Downloaded Data Export',
            'ADMIN_CREATE' => 'Created Admin User',
            'ADMIN_EDIT' => 'Updated Admin User',
            'ADMIN_DELETE' => 'Deleted Admin User',
            'SETTINGS_CHANGE' => 'Changed Settings',
            'BACKUP_CREATE' => 'Created Backup',
            'BACKUP_DELETE' => 'Deleted Backup',
            'BACKUP_RESTORE' => 'Restored Backup',
            'SYSTEM_UPDATE' => 'Updated System',
        ]);

        self::define('ERROR_MESSAGES', [
            'invalid_credentials' => 'Invalid username or password',
            'account_locked' => 'Account locked due to too many failed attempts',
            'session_expired' => 'Your session has expired. Please login again',
            'access_denied' => 'You do not have permission to access this resource',
            'csrf_invalid' => 'Security token invalid. Please refresh and try again',
        ]);

        self::define('SUCCESS_MESSAGES', [
            'login_success' => 'Successfully logged in',
            'logout_success' => 'Successfully logged out',
            'player_updated' => 'Player information updated successfully',
            'player_banned' => 'Player has been banned',
            'player_unbanned' => 'Player has been unbanned',
            'admin_created' => 'Admin user created successfully',
            'settings_saved' => 'Settings saved successfully',
        ]);
    }

    private static function define(string $name, $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}
