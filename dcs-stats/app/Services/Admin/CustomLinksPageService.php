<?php

namespace DcsStats\Services\Admin;

final class CustomLinksPageService
{
    public function state(array $currentAdmin): array
    {
        require_once DCS_ROOT_PATH . '/language.php';
        require_once DCS_ROOT_PATH . '/site_features.php';

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$message, $messageType] = $this->handlePost($demoRestricted);
        }

        $currentFeatures = \loadSiteFeatures();
        $customLinks = $currentFeatures['custom_links'] ?? [];
        if (empty($customLinks)) {
            $customLinks = [['label' => '', 'url' => '', 'enabled' => true, 'new_tab' => true]];
        }

        return [
            'currentFeatures' => $currentFeatures,
            'customLinks' => $customLinks,
            'demoRestricted' => $demoRestricted,
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.custom_links.title'),
        ];
    }

    private function handlePost(bool $demoRestricted): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [\dcs_t('admin.custom_links.csrf_invalid'), 'error'];
        }

        if ($demoRestricted) {
            return [\demoWriteLockMessage(), 'error'];
        }

        $message = '';
        $messageType = '';
        $allFeatures = \loadSiteFeatures();
        $menuText = trim($_POST['custom_links_menu_text'] ?? 'Squadron Links');
        $allFeatures['custom_links_menu_text'] = $menuText !== '' ? $menuText : 'Squadron Links';
        $allFeatures['custom_links'] = $this->normalizeLinks($_POST['custom_links'] ?? [], $message, $messageType);

        if ($messageType === 'error') {
            return [$message, $messageType];
        }

        if (!\saveSiteFeatures($allFeatures)) {
            return [\dcs_t('admin.custom_links.save_failed'), 'error'];
        }

        \logAdminActivity('CUSTOM_LINKS_UPDATE', $_SESSION['admin_id'], 'settings', 'custom_links', [
            'menu_text' => $allFeatures['custom_links_menu_text'],
            'link_count' => count($allFeatures['custom_links']),
        ]);

        return [\dcs_t('admin.custom_links.save_success'), 'success'];
    }

    private function normalizeLinks($postedLinks, string &$message, string &$messageType): array
    {
        $customLinks = [];

        foreach ($postedLinks ?? [] as $link) {
            $label = trim($link['label'] ?? '');
            $url = trim($link['url'] ?? '');

            if ($label === '' && $url === '') {
                continue;
            }

            if ($label === '' || $url === '') {
                $message = \dcs_t('admin.custom_links.error_label_url_required');
                $messageType = 'error';
                break;
            }

            $isValidUrl = preg_match('#^https?://#i', $url) || strpos($url, '/') === 0;
            if (!$isValidUrl) {
                $message = \dcs_t('admin.custom_links.error_invalid_url');
                $messageType = 'error';
                break;
            }

            $customLinks[] = [
                'label' => $label,
                'url' => $url,
                'enabled' => isset($link['enabled']),
                'new_tab' => isset($link['new_tab']),
            ];
        }

        return $customLinks;
    }
}
