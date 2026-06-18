<?php

namespace DcsStats\Services\Admin;

final class InstallerCliInputService
{
    private InstallerSupportService $support;

    public function __construct(?InstallerSupportService $support = null)
    {
        $this->support = $support ?? new InstallerSupportService();
    }

    public function collect(bool $isDev): array
    {
        echo "\nSetting up admin account...\n";
        echo "Username [admin]: ";
        $username = trim((string)fgets(STDIN)) ?: 'admin';

        echo "Email: ";
        $email = trim((string)fgets(STDIN));
        while ($email === '') {
            echo "Please enter an email address: ";
            $email = trim((string)fgets(STDIN));
        }

        echo "Password: ";
        $password = $this->readPassword();
        while (strlen($password) < 8) {
            echo "Password must be at least 8 characters. Try again: ";
            $password = $this->readPassword();
        }

        echo "\nConfiguring API connection...\n";
        echo "DCSServerBot API URL (e.g., 192.168.1.100:9876): ";
        $apiUrl = trim((string)fgets(STDIN));
        while ($apiUrl === '') {
            echo "Please enter the API address (host:port): ";
            $apiUrl = trim((string)fgets(STDIN));
        }

        echo "DCSServerBot API Key (optional, press Enter to skip): ";
        $apiKey = trim((string)fgets(STDIN));
        if (!$this->support->isValidApiKey($apiKey)) {
            die("Error: API key contains invalid characters.\n");
        }

        $apiUrl = $this->resolveApiUrl($apiUrl, $apiKey, $isDev);

        echo "Site Name [DCS Statistics]: ";
        $siteName = trim((string)fgets(STDIN)) ?: 'DCS Statistics';

        echo "Discord Invite URL (optional, press Enter to skip): ";
        $discordUrl = trim((string)fgets(STDIN));

        return [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'api_url' => $apiUrl,
            'api_key' => $apiKey,
            'site_name' => $siteName,
            'discord_url' => $discordUrl,
            'default_language' => 'en',
            'update_branch' => 'main',
        ];
    }

    private function readPassword(): string
    {
        system('stty -echo');
        $password = trim((string)fgets(STDIN));
        system('stty echo');
        echo "\n";

        return $password;
    }

    private function resolveApiUrl(string $apiUrl, string $apiKey, bool $isDev): string
    {
        if (!$isDev) {
            echo "Testing connection...\n";
        }

        $result = $this->support->resolveApiUrl($apiUrl, $apiKey, $isDev);
        if (!$result['connected']) {
            die("Error: Could not connect to API. Please check the address and ensure DCSServerBot is running.\n");
        }

        if ($isDev) {
            echo "✓ Dev mode - skipping API connection test\n";
        } else {
            echo "✓ Connected successfully using " . parse_url($result['url'], PHP_URL_SCHEME) . "\n";
        }

        return $result['url'];
    }
}
