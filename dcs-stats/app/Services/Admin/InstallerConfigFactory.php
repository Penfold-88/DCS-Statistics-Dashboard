<?php

namespace DcsStats\Services\Admin;

final class InstallerConfigFactory
{
    public function adminUser(string $username, string $email, string $password): array
    {
        return [
            'id' => 1,
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 2,
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => null,
            'is_active' => true,
            'failed_attempts' => 0,
            'locked_until' => null,
        ];
    }

    public function adminDataFiles(array $admin): array
    {
        return [
            'users.json' => [$admin],
            'logs.json' => [],
            'bans.json' => [],
            'sessions.json' => [],
        ];
    }

    public function apiConfig(string $apiUrl, string $apiKey): array
    {
        $apiConfig = \DcsStats\Core\ApiConfig::defaults();
        $apiConfig['api_base_url'] = rtrim($apiUrl, '/');
        $apiConfig['api_host'] = preg_replace('#^https?://#', '', $apiConfig['api_base_url']);
        $apiConfig['api_key'] = $apiKey !== '' ? $apiKey : null;

        return $apiConfig;
    }

    public function siteConfig(string $siteName, string $defaultLanguage, string $discordUrl): array
    {
        return [
            'site_name' => $siteName,
            'default_language' => $defaultLanguage,
            'date_format' => 'd/m/Y',
            'discord_invite_url' => $discordUrl,
            'theme' => 'dark',
            'maintenance_mode' => false,
            'allow_player_search' => true,
            'show_squadron_tab' => true,
            'show_servers_tab' => true,
        ];
    }
}
