<?php

namespace DcsStats\Services\Admin;

final class SettingsBackupSiteConfigSanitizer
{
    public function clean($config): array
    {
        if (!is_array($config)) {
            return [];
        }

        $allowedKeys = [
            'site_name',
            'default_language',
            'date_format',
            'discord_invite_url',
            'theme',
            'allow_player_search',
            'show_squadron_tab',
            'show_servers_tab',
        ];

        return array_intersect_key($config, array_flip($allowedKeys));
    }
}
