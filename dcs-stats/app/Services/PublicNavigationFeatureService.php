<?php

namespace DcsStats\Services;

final class PublicNavigationFeatureService
{
    public function customLinks(): array
    {
        $customLinks = getFeatureValue('custom_links', []);
        if (!is_array($customLinks)) {
            $customLinks = [];
        }

        return array_values(array_filter($customLinks, function ($link) {
            return is_array($link)
                && ($link['enabled'] ?? true)
                && trim((string)($link['label'] ?? '')) !== ''
                && trim((string)($link['url'] ?? '')) !== '';
        }));
    }

    public function customLinksMenuText(): string
    {
        $customLinksMenuText = trim((string)getFeatureValue('custom_links_menu_text', 'Squadron Links'));
        if ($customLinksMenuText === '') {
            return dcs_t('nav.squadron_links');
        }

        return $customLinksMenuText;
    }

    public function serverCardVisibility(): array
    {
        $siteFeatureValues = loadSiteFeatures();
        $serverCardVisibility = [];
        foreach ($siteFeatureValues as $featureKey => $enabled) {
            if (strpos($featureKey, 'server_card_') === 0) {
                $serverCardVisibility[$featureKey] = (bool)$enabled;
            }
        }

        return $serverCardVisibility;
    }
}
