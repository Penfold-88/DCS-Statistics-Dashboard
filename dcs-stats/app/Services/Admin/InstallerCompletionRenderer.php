<?php

namespace DcsStats\Services\Admin;

final class InstallerCompletionRenderer
{
    public function render(bool $isCli, array $state, string $selfDeleteStatus): void
    {
        if ($isCli) {
            $this->renderCli($state['username'], $selfDeleteStatus);
            return;
        }

        $username = $state['username'];
        $email = $state['email'];
        $api_url = $state['api_url'];
        $site_name = $state['site_name'];
        $discord_url = $state['discord_url'];
        $installerSelfDeleteStatus = $selfDeleteStatus;

        require DCS_APP_PATH . '/Views/Admin/installer/complete.php';
    }

    private function renderCli(string $username, string $selfDeleteStatus): void
    {
        echo "\n";
        echo "========================================\n";
        echo "Installation completed successfully!\n";
        echo "========================================\n\n";
        echo "You can now access the admin panel at:\n";
        echo "https://yoursite.com/dcs-stats/site-config/\n\n";
        echo "Login with:\n";
        echo "Username: $username\n";
        echo "Password: [the password you entered]\n";
        echo "\nNext steps:\n";
        echo "1. Login to the admin panel\n";
        echo "2. Change your password immediately\n";
        echo "3. Create additional admin users as needed\n";
        echo "4. Configure your settings\n";

        if ($selfDeleteStatus === 'removed') {
            echo "\nSecurity cleanup: install.php was removed automatically.\n";
        } else {
            echo "\nFor security, delete or rename this install.php file.\n";
        }
    }
}
