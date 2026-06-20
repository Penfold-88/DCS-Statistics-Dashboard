<?php

namespace DcsStats\Services\Admin;

final class CmsSettingsPageService
{
    public function state(array $currentAdmin): array
    {
        $message = '';
        $messageType = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
                $message = \dcs_t('admin.cms.csrf_invalid');
                $messageType = 'error';
            } elseif (\isDemoRestricted($currentAdmin)) {
                $message = \demoWriteLockMessage();
                $messageType = 'error';
            } else {
                $features = \loadSiteFeatures();
                $features['cms_enabled'] = isset($_POST['cms_enabled']);
                if (\saveSiteFeatures($features)) {
                    \logAdminActivity('CMS_TOGGLE', $_SESSION['admin_id'], 'cms', 'cms_enabled', ['enabled' => $features['cms_enabled']]);
                    $message = \dcs_t('admin.cms.settings_saved');
                    $messageType = 'success';
                } else {
                    $message = \dcs_t('admin.cms.save_failed');
                    $messageType = 'error';
                }
            }
        }
        return [
            'cmsEnabled' => \isFeatureEnabled('cms_enabled'),
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.cms.settings'),
        ];
    }
}
