<?php

namespace DcsStats\Services\Admin;

final class MetadataPageService
{
    public function state(array $currentAdmin): array
    {
        require_once DCS_ROOT_PATH . '/language.php';
        require_once DCS_ROOT_PATH . '/site_metadata.php';

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$message, $messageType] = $this->handlePost($demoRestricted);
        }

        return [
            'demoRestricted' => $demoRestricted,
            'message' => $message,
            'messageType' => $messageType,
            'metadata' => \loadSiteMetadata(),
            'pageTitle' => \dcs_t('admin.metadata.title'),
        ];
    }

    private function handlePost(bool $demoRestricted): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [\dcs_t('admin.metadata.csrf_invalid'), 'error'];
        }

        if ($demoRestricted) {
            return [\demoRestrictionMessage(), 'error'];
        }

        $metadata = [
            'description' => trim($_POST['description'] ?? ''),
            'keywords' => trim($_POST['keywords'] ?? ''),
            'block_search_engines' => isset($_POST['block_search_engines']),
            'show_privacy_link' => isset($_POST['show_privacy_link']),
            'privacy_notice' => trim($_POST['privacy_notice'] ?? ''),
        ];

        if (strlen($metadata['description']) > 320) {
            return [\dcs_t('admin.metadata.error_description_length'), 'error'];
        }
        if (strlen($metadata['keywords']) > 500) {
            return [\dcs_t('admin.metadata.error_keywords_length'), 'error'];
        }
        if (strlen($metadata['privacy_notice']) > 5000) {
            return [\dcs_t('admin.metadata.error_privacy_length'), 'error'];
        }

        if (!\saveSiteMetadata($metadata)) {
            return [\dcs_t('admin.metadata.save_failed'), 'error'];
        }

        \logAdminActivity('METADATA_UPDATE', $_SESSION['admin_id'], 'settings', 'metadata', [
            'description_length' => strlen($metadata['description']),
            'keywords_length' => strlen($metadata['keywords']),
            'block_search_engines' => $metadata['block_search_engines'],
            'show_privacy_link' => $metadata['show_privacy_link'],
            'privacy_notice_length' => strlen($metadata['privacy_notice']),
        ]);

        return [\dcs_t('admin.metadata.save_success'), 'success'];
    }
}
