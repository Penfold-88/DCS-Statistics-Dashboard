<?php

namespace DcsStats\Core;

final class AdminScalarConfigProvider
{
    public function values(): array
    {
        $dataDir = DCS_ROOT_PATH . '/site-config/data/';

        return [
            'ADMIN_SESSION_NAME' => 'dcs_admin_session',
            'ADMIN_COOKIE_NAME' => 'dcs_admin_remember',
            'ADMIN_COOKIE_LIFETIME' => 30 * 24 * 60 * 60,
            'LOGIN_THROTTLE_ATTEMPTS' => 5,
            'LOGIN_THROTTLE_WINDOW' => 900,
            'CSRF_TOKEN_NAME' => 'admin_csrf_token',
            'SESSION_LIFETIME' => 3600,
            'ENFORCE_HTTPS' => false,
            'ROLE_AIR_BOSS' => 2,
            'ROLE_LSO' => 1,
            'USE_DATABASE' => false,
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'dcs_stats_admin',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'DB_PREFIX' => 'dcs_',
            'ADMIN_DATA_DIR' => $dataDir,
            'ADMIN_USERS_FILE' => $dataDir . 'users.json',
            'ADMIN_LOGS_FILE' => $dataDir . 'logs.json',
            'ADMIN_BANS_FILE' => $dataDir . 'bans.json',
            'ADMIN_SESSIONS_FILE' => $dataDir . 'sessions.json',
            'LOG_ADMIN_ACTIONS' => true,
            'LOG_RETENTION_DAYS' => 90,
            'MAX_ADMIN_LOGS' => 1000,
            'EXPORT_MAX_RECORDS' => 10000,
            'RECORDS_PER_PAGE' => 25,
            'DATE_FORMAT' => 'Y-m-d H:i:s',
            'DEFAULT_ADMIN_USERNAME' => 'admin',
            'DEFAULT_ADMIN_EMAIL' => 'admin@example.com',
            'DEFAULT_ADMIN_PASSWORD' => null,
        ];
    }
}
