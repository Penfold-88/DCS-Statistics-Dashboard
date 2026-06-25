<?php

namespace DcsStats\Services\Admin;

final class DiscordSettingsPageService
{
    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::language();
        \DcsStats\Core\SupportBootstrap::siteFeatures();

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$message, $messageType] = $this->handlePost($demoRestricted);
        }

        return [
            'currentFeatures' => \loadSiteFeatures(),
            'demoRestricted' => $demoRestricted,
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.discord.title'),
        ];
    }

    private function handlePost(bool $demoRestricted): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [\dcs_t('admin.discord.csrf_invalid'), 'error'];
        }

        if ($demoRestricted) {
            return [\demoWriteLockMessage(), 'error'];
        }

        $currentFeatures = \loadSiteFeatures();
        $currentFeatures['discord_link_url'] = trim($_POST['discord_link_url'] ?? '');
        $currentFeatures['show_discord_link'] = isset($_POST['show_discord_link']);

        if (
            $currentFeatures['show_discord_link']
            && $currentFeatures['discord_link_url'] !== ''
            && !filter_var($currentFeatures['discord_link_url'], FILTER_VALIDATE_URL)
        ) {
            return [\dcs_t('admin.discord.invalid_url'), 'error'];
        }

        if (!\saveSiteFeatures($currentFeatures)) {
            return [\dcs_t('admin.discord.save_failed'), 'error'];
        }

        \logAdminActivity('SETTINGS_CHANGE', $_SESSION['admin_id'], 'settings', 'discord_link', $currentFeatures);

        return [\dcs_t('admin.discord.save_success'), 'success'];
    }
}
