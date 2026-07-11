<?php

namespace DcsStats\Services\Admin;

final class CmsSettingsPageService
{
    public function state(array $currentAdmin): array
    {
        $store = new \DcsStats\Services\Cms\CmsPageStore();
        $publishedPages = array_values(array_filter($store->all(), static fn(array $page): bool => !empty($page['published'])));
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
                $homepageId = preg_replace('/[^a-f0-9]/', '', (string)($_POST['cms_homepage_page_id'] ?? ''));
                if ($homepageId !== '' && !$this->isPublishedPage($homepageId, $publishedPages)) {
                    return $this->viewState($publishedPages, \dcs_t('admin.cms.homepage_invalid'), 'error');
                }
                $features['cms_homepage_page_id'] = $homepageId;
                if (\saveSiteFeatures($features)) {
                    \logAdminActivity('CMS_SETTINGS_UPDATE', $_SESSION['admin_id'], 'cms', 'settings', ['enabled' => $features['cms_enabled'], 'homepage_page_id' => $homepageId]);
                    $message = \dcs_t('admin.cms.settings_saved');
                    $messageType = 'success';
                } else {
                    $message = \dcs_t('admin.cms.save_failed');
                    $messageType = 'error';
                }
            }
        }
        return $this->viewState($publishedPages, $message, $messageType);
    }

    private function isPublishedPage(string $id, array $pages): bool
    {
        foreach ($pages as $page) {
            if (($page['id'] ?? '') === $id) {
                return true;
            }
        }
        return false;
    }

    private function viewState(array $publishedPages, string $message, string $messageType): array
    {
        $selected = (string)\getFeatureValue('cms_homepage_page_id', '');
        return [
            'cmsEnabled' => \isFeatureEnabled('cms_enabled'),
            'homepagePageId' => $selected,
            'homepageFallback' => $selected !== '' && !$this->isPublishedPage($selected, $publishedPages),
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.cms.settings'),
            'publishedPages' => $publishedPages,
        ];
    }
}
