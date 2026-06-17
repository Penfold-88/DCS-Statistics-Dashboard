<?php

namespace DcsStats\Services\Admin;

final class MetadataActionService
{
    public function handle(bool $demoRestricted): array
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

        $error = $this->validate($metadata);
        if ($error !== '') {
            return [$error, 'error'];
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

    private function validate(array $metadata): string
    {
        if (strlen($metadata['description']) > 320) {
            return \dcs_t('admin.metadata.error_description_length');
        }
        if (strlen($metadata['keywords']) > 500) {
            return \dcs_t('admin.metadata.error_keywords_length');
        }
        if (strlen($metadata['privacy_notice']) > 5000) {
            return \dcs_t('admin.metadata.error_privacy_length');
        }

        return '';
    }
}
