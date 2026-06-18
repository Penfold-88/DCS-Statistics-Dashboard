<?php

namespace DcsStats\Core;

final class SiteFeatureDefaultsProvider
{
    public function defaults(): array
    {
        $siteConfig = [];
        $siteConfigFile = DCS_ROOT_PATH . '/site_config.json';
        if (file_exists($siteConfigFile)) {
            $content = @file_get_contents($siteConfigFile);
            if ($content) {
                $siteConfig = json_decode($content, true) ?: [];
            }
        }

        return SiteFeatureCatalog::defaults($siteConfig);
    }
}
