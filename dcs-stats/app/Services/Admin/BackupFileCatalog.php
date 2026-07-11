<?php

namespace DcsStats\Services\Admin;

final class BackupFileCatalog
{
    public static function configurationFiles(): array
    {
        return [
            'api_config.json',
            'site_config.json',
            '.version_meta.json',
            'custom_theme.css',
            'header_custom.css',
            'menu_config.json',
            'site-config/data/api_config.json',
            'site-config/data/.api_key.key',
            'site-config/data/users.json',
            'site-config/data/logs.json',
            'site-config/data/bans.json',
            'site-config/data/sessions.json',
            '.env',
            'docker-compose.yml',
            'docker-compose.override.yml',
            'Dockerfile',
            'Dockerfile.simple',
            '.dockerignore',
            'docker/docker-compose.yml',
            'docker/docker-compose.override.yml',
            'docker/Dockerfile',
            'docker/Dockerfile.dockerignore',
            'docker/Dockerfile.simple',
        ];
    }

    public static function packageConfigurationFiles(): array
    {
        return [
            'api_config.json',
            'site_config.json',
            '.version_meta.json',
            '.env',
            'docker-compose.yml',
            'docker-compose.override.yml',
            'Dockerfile',
            'Dockerfile.simple',
            '.dockerignore',
            'docker/docker-compose.yml',
            'docker/docker-compose.override.yml',
            'docker/Dockerfile',
            'docker/Dockerfile.dockerignore',
            'docker/Dockerfile.simple',
        ];
    }

    public static function projectBackupExcludes(): array
    {
        return [
            'backups',
            'UPGRADE',
            'RESTORE_TEMP',
            'RESTORE_ROLLBACK',
        ];
    }
}
