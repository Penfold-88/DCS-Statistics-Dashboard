<?php

namespace DcsStats\Core;

final class AdminMessageConfigProvider
{
    public function values(): array
    {
        return [
            'LOG_ACTIONS' => [
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
            ],
            'ERROR_MESSAGES' => [
                'invalid_credentials' => 'Invalid username or password',
                'account_locked' => 'Account locked due to too many failed attempts',
                'session_expired' => 'Your session has expired. Please login again',
                'access_denied' => 'You do not have permission to access this resource',
                'csrf_invalid' => 'Security token invalid. Please refresh and try again',
            ],
            'SUCCESS_MESSAGES' => [
                'login_success' => 'Successfully logged in',
                'logout_success' => 'Successfully logged out',
                'player_updated' => 'Player information updated successfully',
                'player_banned' => 'Player has been banned',
                'player_unbanned' => 'Player has been unbanned',
                'admin_created' => 'Admin user created successfully',
                'settings_saved' => 'Settings saved successfully',
            ],
        ];
    }
}
