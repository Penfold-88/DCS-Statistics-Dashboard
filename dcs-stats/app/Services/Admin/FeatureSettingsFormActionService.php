<?php

namespace DcsStats\Services\Admin;

final class FeatureSettingsFormActionService
{
    public function handle(array $dynamicServerFeatures, array $lockedFeatures, bool $demoRestricted): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [\dcs_t('admin.settings.csrf_invalid'), 'error'];
        }

        if ($demoRestricted) {
            return [\demoWriteLockMessage(), 'error'];
        }

        $allFeatures = \loadSiteFeatures();
        foreach ($this->featureGroupsForSave($dynamicServerFeatures) as $features) {
            foreach ($features as $key => $label) {
                $allFeatures[$key] = isset($_POST['features'][$key]);
            }
        }

        foreach ($lockedFeatures as $lockedKey => $reason) {
            $allFeatures[$lockedKey] = false;
        }

        foreach (\getFeatureDependencies() as $parent => $children) {
            if (!$allFeatures[$parent]) {
                foreach ($children as $child) {
                    $allFeatures[$child] = false;
                }
            }
        }

        if (!\saveSiteFeatures($allFeatures)) {
            return [\dcs_t('admin.settings.save_failed'), 'error'];
        }

        \logAdminActivity('SETTINGS_CHANGE', $_SESSION['admin_id'], 'settings', 'site_features', $allFeatures);

        return [\dcs_t('admin.settings.save_success'), 'success'];
    }

    private function featureGroupsForSave(array $dynamicServerFeatures): array
    {
        $featureGroups = \getFeatureGroups();
        if (!empty($dynamicServerFeatures)) {
            $featureGroups['Server Features'] = array_merge(
                $featureGroups['Server Features'],
                $dynamicServerFeatures
            );
        }

        return $featureGroups;
    }
}
