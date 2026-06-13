<?php

namespace DcsStats\Services\Admin;

final class SquadronSettingsPageService
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
            'pageTitle' => \dcs_t('admin.squadron_settings.title'),
        ];
    }

    private function handlePost(bool $demoRestricted): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [\dcs_t('admin.squadron_settings.csrf_invalid'), 'error'];
        }

        if ($demoRestricted) {
            return [\demoWriteLockMessage(), 'error'];
        }

        $currentFeatures = \loadSiteFeatures();
        $currentFeatures['show_squadron_homepage'] = isset($_POST['show_squadron_homepage']);
        $currentFeatures['squadron_homepage_url'] = trim($_POST['squadron_homepage_url'] ?? '');
        $currentFeatures['squadron_homepage_text'] = trim($_POST['squadron_homepage_text'] ?? 'Squadron');

        if ($currentFeatures['show_squadron_homepage']) {
            if ($currentFeatures['squadron_homepage_url'] === '') {
                return [\dcs_t('admin.squadron_settings.url_required'), 'error'];
            }

            if (!filter_var($currentFeatures['squadron_homepage_url'], FILTER_VALIDATE_URL)) {
                return [\dcs_t('admin.squadron_settings.invalid_url'), 'error'];
            }

            if ($currentFeatures['squadron_homepage_text'] === '') {
                $currentFeatures['squadron_homepage_text'] = 'Squadron';
            } elseif (strlen($currentFeatures['squadron_homepage_text']) > 50) {
                return [\dcs_t('admin.squadron_settings.link_text_too_long'), 'error'];
            }
        }

        if (!\saveSiteFeatures($currentFeatures)) {
            return [\dcs_t('admin.squadron_settings.save_failed'), 'error'];
        }

        \logAdminActivity('SETTINGS_CHANGE', $_SESSION['admin_id'], 'settings', 'squadron_homepage', $currentFeatures);

        return [\dcs_t('admin.squadron_settings.save_success'), 'success'];
    }
}
