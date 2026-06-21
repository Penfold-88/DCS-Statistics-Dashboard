<?php

namespace DcsStats\Services;

use DcsStats\Services\Cms\CmsPageStore;

final class MenuItemRegistry
{
    public function candidates(): array
    {
        $items = [
            $this->page('stats-home', 'Home', 'index.php', 'nav.home', 'nav_home'),
            $this->page('stats-leaderboard', 'Leaderboard', 'leaderboard.php', 'nav.leaderboard', 'nav_leaderboard'),
            $this->page('stats-pilot-statistics', 'Pilot Statistics', 'pilot_statistics.php', 'nav.pilot_statistics', 'nav_pilot_statistics'),
            $this->page('stats-pilot-credits', 'Pilot Credits', 'pilot_credits.php', 'nav.pilot_credits', 'nav_pilot_credits', \isFeatureEnabled('credits_enabled')),
            $this->page('stats-squadrons', 'Squadrons', 'squadrons.php', 'nav.squadrons', 'nav_squadrons', \isFeatureEnabled('squadrons_enabled')),
            $this->page('stats-servers', 'Servers', 'servers.php', 'nav.servers', 'nav_servers'),
        ];

        $cmsLandingActive = (new \DcsStats\Services\Cms\CmsLandingPageService())->hasCmsLandingPage();
        $items[] = [
            'id' => 'stats-dashboard',
            'name' => 'Statistics Dashboard',
            'url' => 'index.php?view=statistics',
            'enabled' => true,
            'available' => $cmsLandingActive && \isFeatureEnabled('nav_home'),
            'type' => 'page',
            'label_key' => 'nav.statistics_dashboard',
            'new_tab' => false,
        ];

        $items[] = [
            'id' => 'integration-discord',
            'name' => 'Discord',
            'url' => (string)\getFeatureValue('discord_link_url', 'https://discord.gg/DNENf6pUNX'),
            'enabled' => true,
            'available' => \isFeatureEnabled('show_discord_link') && trim((string)\getFeatureValue('discord_link_url', '')) !== '',
            'type' => 'discord',
            'new_tab' => true,
        ];
        $items[] = [
            'id' => 'integration-squadron-homepage',
            'name' => (string)\getFeatureValue('squadron_homepage_text', 'Squadron'),
            'url' => (string)\getFeatureValue('squadron_homepage_url', ''),
            'enabled' => true,
            'available' => \isFeatureEnabled('show_squadron_homepage') && trim((string)\getFeatureValue('squadron_homepage_url', '')) !== '',
            'type' => 'squadron_homepage',
            'new_tab' => false,
        ];

        foreach ((new CmsPageStore())->all() as $page) {
            if (empty($page['id'])) {
                continue;
            }
            $items[] = [
                'id' => 'cms-' . $page['id'],
                'name' => (string)($page['title'] ?? $page['slug'] ?? 'Page'),
                'url' => 'page.php?slug=' . rawurlencode((string)($page['slug'] ?? '')),
                'enabled' => true,
                'available' => \isFeatureEnabled('cms_enabled') && !empty($page['published']) && !empty($page['show_in_navigation']) && !empty($page['slug']),
                'type' => 'cms_page',
                'new_tab' => false,
            ];
        }

        $customLinks = \getFeatureValue('custom_links', []);
        if (!is_array($customLinks)) {
            $customLinks = [];
        }
        $groupAvailable = \isFeatureEnabled('nav_custom_links') && count($customLinks) > 0;
        $items[] = [
            'id' => 'custom-links-group',
            'name' => trim((string)\getFeatureValue('custom_links_menu_text', 'Squadron Links')) ?: 'Squadron Links',
            'url' => '',
            'enabled' => true,
            'available' => $groupAvailable,
            'type' => 'group',
            'new_tab' => false,
        ];
        foreach ($customLinks as $index => $link) {
            if (!is_array($link) || trim((string)($link['url'] ?? '')) === '') {
                continue;
            }
            $items[] = [
                'id' => 'custom-link-' . substr(hash('sha256', (string)$link['url']), 0, 16),
                'name' => (string)($link['label'] ?? 'Link'),
                'url' => (string)$link['url'],
                'enabled' => (bool)($link['enabled'] ?? true),
                'available' => $groupAvailable && (bool)($link['enabled'] ?? true),
                'type' => 'external',
                'parent_id' => 'custom-links-group',
                'new_tab' => (bool)($link['new_tab'] ?? true),
            ];
        }

        return $items;
    }

    private function page(string $id, string $name, string $url, string $labelKey, string $feature, bool $dependency = true): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'url' => $url,
            'enabled' => true,
            'available' => $dependency && \isFeatureEnabled($feature),
            'type' => 'page',
            'label_key' => $labelKey,
            'new_tab' => false,
        ];
    }
}
