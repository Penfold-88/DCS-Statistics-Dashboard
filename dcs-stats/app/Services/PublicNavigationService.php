<?php

namespace DcsStats\Services;

final class PublicNavigationService
{
    public function state(): array
    {
        \DcsStats\Core\SupportBootstrap::siteFeatures();
        \DcsStats\Core\SupportBootstrap::language();

        $menuItems = $this->menuItems();
        $this->appendLegacyFeatureLinks($menuItems);

        $customLinks = getFeatureValue('custom_links', []);
        if (!is_array($customLinks)) {
            $customLinks = [];
        }
        $customLinks = array_values(array_filter($customLinks, function ($link) {
            return is_array($link)
                && ($link['enabled'] ?? true)
                && trim((string)($link['label'] ?? '')) !== ''
                && trim((string)($link['url'] ?? '')) !== '';
        }));

        $customLinksMenuText = trim((string)getFeatureValue('custom_links_menu_text', 'Squadron Links'));
        if ($customLinksMenuText === '') {
            $customLinksMenuText = dcs_t('nav.squadron_links');
        }

        $siteFeatureValues = loadSiteFeatures();
        $serverCardVisibility = [];
        foreach ($siteFeatureValues as $featureKey => $enabled) {
            if (strpos($featureKey, 'server_card_') === 0) {
                $serverCardVisibility[$featureKey] = (bool)$enabled;
            }
        }

        return [
            'menuItems' => $menuItems,
            'customLinks' => $customLinks,
            'customLinksMenuText' => $customLinksMenuText,
            'serverScopeEnabled' => isFeatureEnabled('server_scope_filter'),
            'serverCardVisibility' => $serverCardVisibility,
        ];
    }

    public function menuConfigPath(): string
    {
        $primaryPath = DCS_ROOT_PATH . '/site-config/data/menu_config.json';
        $primaryDir = dirname($primaryPath);

        if (is_dir($primaryDir) && is_writable($primaryDir)) {
            return $primaryPath;
        }

        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0700, true);
            @chmod($primaryDir, 0700);
            if (is_dir($primaryDir) && is_writable($primaryDir)) {
                return $primaryPath;
            }
        }

        $altPath = DCS_ROOT_PATH . '/menu_config.json';
        if (is_writable(dirname($altPath))) {
            return $altPath;
        }

        $tempDir = sys_get_temp_dir() . '/dcs_stats';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0700, true);
        }

        return $tempDir . '/menu_config.json';
    }

    public function label(array $item): string
    {
        $knownLabels = [
            'index.php' => ['Home', 'nav.home'],
            'leaderboard.php' => ['Leaderboard', 'nav.leaderboard'],
            'pilot_statistics.php' => ['Pilot Statistics', 'nav.pilot_statistics'],
            'pilot_credits.php' => ['Pilot Credits', 'nav.pilot_credits'],
            'squadrons.php' => ['Squadrons', 'nav.squadrons'],
            'servers.php' => ['Servers', 'nav.servers'],
        ];

        if (!empty($item['label_key'])) {
            return dcs_t($item['label_key']);
        }

        $url = $item['url'] ?? '';
        $name = $item['name'] ?? '';
        if (isset($knownLabels[$url]) && $name === $knownLabels[$url][0]) {
            return dcs_t($knownLabels[$url][1]);
        }

        return (string)$name;
    }

    public function shouldShow(array $item): bool
    {
        if (empty($item['enabled'])) {
            return false;
        }

        $itemType = $item['type'] ?? 'page';
        $url = $item['url'] ?? '';
        $pageFeatureMap = [
            'index.php' => 'nav_home',
            'leaderboard.php' => 'nav_leaderboard',
            'pilot_statistics.php' => 'nav_pilot_statistics',
            'pilot_credits.php' => 'nav_pilot_credits',
            'squadrons.php' => 'nav_squadrons',
            'servers.php' => 'nav_servers',
        ];

        if (isset($pageFeatureMap[$url]) && !isFeatureEnabled($pageFeatureMap[$url])) {
            return false;
        }

        if ($url === 'pilot_credits.php' && !isFeatureEnabled('credits_enabled')) {
            return false;
        }

        if ($url === 'squadrons.php' && !isFeatureEnabled('squadrons_enabled')) {
            return false;
        }

        if ($itemType === 'discord' && !isFeatureEnabled('show_discord_link')) {
            return false;
        }

        if ($itemType === 'squadron_homepage' && (!isFeatureEnabled('show_squadron_homepage') || empty(getFeatureValue('squadron_homepage_url')))) {
            return false;
        }

        return true;
    }

    private function menuItems(): array
    {
        $menuConfigFile = $this->menuConfigPath();
        $defaultMenuItems = [
            ['name' => 'Home', 'url' => 'index.php', 'enabled' => true, 'label_key' => 'nav.home'],
            ['name' => 'Leaderboard', 'url' => 'leaderboard.php', 'enabled' => true, 'label_key' => 'nav.leaderboard'],
            ['name' => 'Pilot Statistics', 'url' => 'pilot_statistics.php', 'enabled' => true, 'label_key' => 'nav.pilot_statistics'],
            ['name' => 'Pilot Credits', 'url' => 'pilot_credits.php', 'enabled' => true, 'label_key' => 'nav.pilot_credits'],
            ['name' => 'Squadrons', 'url' => 'squadrons.php', 'enabled' => true, 'label_key' => 'nav.squadrons'],
            ['name' => 'Servers', 'url' => 'servers.php', 'enabled' => true, 'label_key' => 'nav.servers'],
        ];

        if (!file_exists($menuConfigFile)) {
            return $defaultMenuItems;
        }

        $savedMenu = json_decode((string)file_get_contents($menuConfigFile), true);

        return $savedMenu && is_array($savedMenu) ? $savedMenu : $defaultMenuItems;
    }

    private function appendLegacyFeatureLinks(array &$menuItems): void
    {
        $hasDiscordInMenu = false;
        $hasSquadronInMenu = false;
        foreach ($menuItems as $item) {
            if (($item['type'] ?? '') === 'discord') {
                $hasDiscordInMenu = true;
            }
            if (($item['type'] ?? '') === 'squadron_homepage') {
                $hasSquadronInMenu = true;
            }
        }

        if (!$hasDiscordInMenu && isFeatureEnabled('show_discord_link')) {
            $menuItems[] = [
                'name' => 'Discord',
                'url' => getFeatureValue('discord_link_url', 'https://discord.gg/DNENf6pUNX'),
                'enabled' => true,
                'type' => 'discord',
            ];
        }

        if (!$hasSquadronInMenu && isFeatureEnabled('show_squadron_homepage') && !empty(getFeatureValue('squadron_homepage_url'))) {
            $menuItems[] = [
                'name' => getFeatureValue('squadron_homepage_text', 'Squadron'),
                'url' => getFeatureValue('squadron_homepage_url'),
                'enabled' => true,
                'type' => 'squadron_homepage',
            ];
        }
    }
}
