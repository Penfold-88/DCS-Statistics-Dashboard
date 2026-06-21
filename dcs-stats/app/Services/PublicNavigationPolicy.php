<?php

namespace DcsStats\Services;

final class PublicNavigationPolicy
{
    private const LABELS = [
        'index.php' => ['Home', 'nav.home'],
        'index.php?view=statistics' => ['Statistics Dashboard', 'nav.statistics_dashboard'],
        'leaderboard.php' => ['Leaderboard', 'nav.leaderboard'],
        'pilot_statistics.php' => ['Pilot Statistics', 'nav.pilot_statistics'],
        'pilot_credits.php' => ['Pilot Credits', 'nav.pilot_credits'],
        'squadrons.php' => ['Squadrons', 'nav.squadrons'],
        'servers.php' => ['Servers', 'nav.servers'],
    ];

    private const PAGE_FEATURES = [
        'index.php' => 'nav_home',
        'index.php?view=statistics' => 'nav_home',
        'leaderboard.php' => 'nav_leaderboard',
        'pilot_statistics.php' => 'nav_pilot_statistics',
        'pilot_credits.php' => 'nav_pilot_credits',
        'squadrons.php' => 'nav_squadrons',
        'servers.php' => 'nav_servers',
    ];

    public function label(array $item): string
    {
        if (!empty($item['label_key'])) {
            return \dcs_t($item['label_key']);
        }

        $url = $item['url'] ?? '';
        $name = $item['name'] ?? '';
        if (isset(self::LABELS[$url]) && $name === self::LABELS[$url][0]) {
            return \dcs_t(self::LABELS[$url][1]);
        }

        return (string)$name;
    }

    public function shouldShow(array $item): bool
    {
        if (empty($item['enabled']) || empty($item['available'])) {
            return false;
        }

        $itemType = $item['type'] ?? 'page';
        $url = $item['url'] ?? '';
        if (isset(self::PAGE_FEATURES[$url]) && !\isFeatureEnabled(self::PAGE_FEATURES[$url])) {
            return false;
        }

        if ($url === 'pilot_credits.php' && !\isFeatureEnabled('credits_enabled')) {
            return false;
        }

        if ($url === 'squadrons.php' && !\isFeatureEnabled('squadrons_enabled')) {
            return false;
        }

        if ($itemType === 'discord' && !\isFeatureEnabled('show_discord_link')) {
            return false;
        }

        if (
            $itemType === 'squadron_homepage'
            && (!\isFeatureEnabled('show_squadron_homepage') || empty(\getFeatureValue('squadron_homepage_url')))
        ) {
            return false;
        }

        return true;
    }
}
