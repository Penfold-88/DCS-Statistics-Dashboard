<?php

namespace DcsStats\Services;

final class FooterService
{
    public function state(): array
    {
        \DcsStats\Core\SupportBootstrap::siteMetadata();
        \DcsStats\Core\SupportBootstrap::language();
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\SupportBootstrap::apiCache();

        $metadata = loadSiteMetadata();
        $showLastUpdate = isFeatureEnabled('show_last_update');
        $lastUpdate = null;

        if ($showLastUpdate) {
            $apiLastRefresh = apiCacheLastRefreshTimestamp();
            if ($apiLastRefresh !== null) {
                $lastUpdate = date(dcs_public_date_format() . ' H:i', $apiLastRefresh);
            }
        }

        return [
            'metadata' => $metadata,
            'showLastUpdate' => $showLastUpdate,
            'lastUpdate' => $lastUpdate,
        ];
    }
}
