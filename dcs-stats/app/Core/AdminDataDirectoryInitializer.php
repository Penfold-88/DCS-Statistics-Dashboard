<?php

namespace DcsStats\Core;

final class AdminDataDirectoryInitializer
{
    public function initialize(): void
    {
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
            $this->useTempFallback();
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

    public function needsInitialization(): bool
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

    private function useTempFallback(): void
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
