<?php

namespace DcsStats\Core;

final class AdminRoleConfigProvider
{
    public function values(): array
    {
        return [
            'ROLE_NAMES' => [
                ROLE_AIR_BOSS => 'Air Boss',
                ROLE_LSO => 'LSO',
            ],
            'ROLE_PERMISSIONS' => [
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
            ],
            'EXPORT_FORMATS' => ['csv', 'json'],
        ];
    }
}
