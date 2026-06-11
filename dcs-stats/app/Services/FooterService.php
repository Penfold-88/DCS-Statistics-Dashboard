<?php

namespace DcsStats\Services;

final class FooterService
{
    public function state(): array
    {
        require_once DCS_ROOT_PATH . '/site_metadata.php';
        require_once DCS_ROOT_PATH . '/language.php';
        require_once DCS_ROOT_PATH . '/site_features.php';
        require_once DCS_ROOT_PATH . '/api_cache.php';

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
