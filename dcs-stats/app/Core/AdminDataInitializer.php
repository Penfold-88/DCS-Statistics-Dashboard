<?php

namespace DcsStats\Core;

final class AdminDataInitializer
{
    public static function initialize(): void
    {
        $isFirstTime = !is_dir(ADMIN_DATA_DIR);

        if ($isFirstTime && php_sapi_name() !== 'cli') {
            self::showSetupProgress();
        }

        if (!is_dir(ADMIN_DATA_DIR)) {
            $created = @mkdir(ADMIN_DATA_DIR, 0700, true);
            if (!$created) {
                $parent = dirname(ADMIN_DATA_DIR);
                if (!is_dir($parent)) {
                    @mkdir($parent, 0755, true);
                }
                @mkdir(ADMIN_DATA_DIR, 0700, true);
            }
        }
        @chmod(ADMIN_DATA_DIR, 0700);

        if (!is_dir(ADMIN_DATA_DIR) || !is_writable(ADMIN_DATA_DIR)) {
            self::useTempDataDirectoryFallback();
        }

        $usersFile = defined('ADMIN_USERS_FILE_OVERRIDE') ? ADMIN_USERS_FILE_OVERRIDE : ADMIN_USERS_FILE;
        if (file_exists($usersFile)) {
            @chmod($usersFile, 0600);
        }

        foreach (['logs', 'bans', 'sessions'] as $type) {
            $file = AdminEnvironment::dataFilePath($type);
            if (!file_exists($file)) {
                @file_put_contents($file, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
            }
            @chmod($file, 0600);
        }
    }

    public static function needsInitialization(): bool
    {
        if (!is_dir(ADMIN_DATA_DIR)) {
            return true;
        }

        foreach (['logs', 'bans', 'sessions'] as $type) {
            if (!file_exists(AdminEnvironment::dataFilePath($type))) {
                return true;
            }
        }

        return false;
    }

    private static function showSetupProgress(): void
    {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Setting Up Admin Panel</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: #1a1a1a;
                    color: #fff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                }
                .setup-container {
                    background: #2d2d2d;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.5);
                    text-align: center;
                    max-width: 500px;
                }
                h1 {
                    color: #4CAF50;
                    margin-bottom: 20px;
                }
                .spinner {
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid #4CAF50;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    animation: spin 1s linear infinite;
                    margin: 20px auto;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .status {
                    margin: 15px 0;
                    padding: 10px;
                    background: #1a1a1a;
                    border-radius: 5px;
                }
                .success { color: #4CAF50; }
                .error { color: #f44336; }
            </style>
            <meta http-equiv="refresh" content="3">
        </head>
        <body>
            <div class="setup-container">
                <h1>Setting Up Your Environment</h1>
                <div class="spinner"></div>
                <p>Please wait while we configure the admin panel...</p>
                <div class="status">Creating necessary directories and files...</div>
            </div>
        </body>
        </html>
        <?php
        flush();
        sleep(1);
    }

    private static function useTempDataDirectoryFallback(): void
    {
        $tempDir = sys_get_temp_dir() . '/dcs_admin_data';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0700, true);
        }
        @chmod($tempDir, 0700);

        if (is_dir($tempDir) && is_writable($tempDir) && !defined('ADMIN_DATA_DIR_OVERRIDE')) {
            define('ADMIN_DATA_DIR_OVERRIDE', $tempDir . '/');
            define('ADMIN_USERS_FILE_OVERRIDE', ADMIN_DATA_DIR_OVERRIDE . 'users.json');
            define('ADMIN_LOGS_FILE_OVERRIDE', ADMIN_DATA_DIR_OVERRIDE . 'logs.json');
            define('ADMIN_BANS_FILE_OVERRIDE', ADMIN_DATA_DIR_OVERRIDE . 'bans.json');
            define('ADMIN_SESSIONS_FILE_OVERRIDE', ADMIN_DATA_DIR_OVERRIDE . 'sessions.json');
        }
    }
}
