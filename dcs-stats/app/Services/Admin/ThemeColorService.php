<?php

namespace DcsStats\Services\Admin;

final class ThemeColorService
{
    public function defaultColors(): array
    {
        return [
            'primary_color' => '#1a1a1a',
            'secondary_color' => '#2a2a2a',
            'background_color' => '#121212',
            'background_gradient_color' => '#1f2b22',
            'surface_color' => '#2c2c2c',
            'surface_dark_color' => '#1e1e1e',
            'card_color' => '#2c2c2c',
            'card_alt_color' => '#1e1e1e',
            'card_heading_color' => '#4CAF50',
            'card_text_color' => '#ffffff',
            'card_muted_text_color' => '#cccccc',
            'text_color' => '#ffffff',
            'muted_text_color' => '#cccccc',
            'heading_color' => '#4CAF50',
            'link_color' => '#4a9eff',
            'accent_color' => '#4CAF50',
            'accent_hover_color' => '#7ad77d',
            'border_color' => '#556b2f',
            'nav_background_color' => '#1a1a1a',
            'nav_text_color' => '#ffffff',
            'nav_hover_color' => '#4CAF50',
            'header_text_color' => '#ffffff',
            'header_title_gradient_color' => '#4CAF50',
            'header_subtitle_color' => '#e0e0e0',
            'footer_background_color' => '#2a2a2a',
            'footer_text_color' => '#e0e0e0',
            'success_color' => '#4CAF50',
            'warning_color' => '#ff9800',
            'danger_color' => '#f44336',
            'info_color' => '#2196F3',
            'table_header_color' => '#1e1e1e',
            'table_header_text_color' => '#4CAF50',
            'table_row_color' => '#2c2c2c',
            'table_text_color' => '#ffffff',
            'table_player_name_color' => '#e0e0e0',
            'table_hover_color' => '#3a3a3a',
        ];
    }

    public function colorGroups(): array
    {
        return [
            \dcs_t('admin.themes.group_page_text') => [
                'background_color' => \dcs_t('admin.themes.page_background'),
                'background_gradient_color' => \dcs_t('admin.themes.page_gradient_end'),
                'text_color' => \dcs_t('admin.themes.main_text'),
                'muted_text_color' => \dcs_t('admin.themes.muted_text'),
                'heading_color' => \dcs_t('admin.themes.headings'),
                'link_color' => \dcs_t('admin.themes.links'),
            ],
            \dcs_t('admin.themes.group_brand') => [
                'accent_color' => \dcs_t('admin.themes.main_accent'),
                'accent_hover_color' => \dcs_t('admin.themes.accent_hover'),
                'border_color' => \dcs_t('admin.themes.borders'),
                'success_color' => \dcs_t('admin.themes.success'),
                'warning_color' => \dcs_t('admin.themes.warning'),
                'danger_color' => \dcs_t('admin.themes.danger'),
                'info_color' => \dcs_t('admin.themes.info'),
            ],
            \dcs_t('admin.themes.group_panels') => [
                'surface_color' => \dcs_t('admin.themes.panel_top'),
                'surface_dark_color' => \dcs_t('admin.themes.panel_bottom'),
                'card_color' => \dcs_t('admin.themes.card_top'),
                'card_alt_color' => \dcs_t('admin.themes.card_bottom'),
                'card_heading_color' => \dcs_t('admin.themes.card_headings'),
                'card_text_color' => \dcs_t('admin.themes.card_text'),
                'card_muted_text_color' => \dcs_t('admin.themes.card_muted_text'),
                'secondary_color' => \dcs_t('admin.themes.secondary_surface'),
            ],
            \dcs_t('admin.themes.group_header_nav') => [
                'primary_color' => \dcs_t('admin.themes.primary_background'),
                'nav_background_color' => \dcs_t('admin.themes.nav_background'),
                'nav_text_color' => \dcs_t('admin.themes.nav_text'),
                'nav_hover_color' => \dcs_t('admin.themes.nav_hover'),
                'header_text_color' => \dcs_t('admin.themes.header_title'),
                'header_title_gradient_color' => \dcs_t('admin.themes.header_gradient_end'),
                'header_subtitle_color' => \dcs_t('admin.themes.header_subtitle'),
            ],
            \dcs_t('admin.themes.group_footer') => [
                'footer_background_color' => \dcs_t('admin.themes.footer_background'),
                'footer_text_color' => \dcs_t('admin.themes.footer_text'),
            ],
            \dcs_t('admin.themes.group_tables') => [
                'table_header_color' => \dcs_t('admin.themes.table_header'),
                'table_header_text_color' => \dcs_t('admin.themes.table_header_text'),
                'table_row_color' => \dcs_t('admin.themes.table_row'),
                'table_text_color' => \dcs_t('admin.themes.table_text'),
                'table_player_name_color' => \dcs_t('admin.themes.table_player_names'),
                'table_hover_color' => \dcs_t('admin.themes.table_hover'),
            ],
        ];
    }

    public function defaultOptions(): array
    {
        return [
            'header_title_gradient_enabled' => false,
            'page_background_gradient_enabled' => false,
        ];
    }

    public function loadOptionsFromCss(string $content): array
    {
        $options = $this->defaultOptions();
        if (preg_match('/--header_title_gradient_enabled:\s*(0|1);/', $content, $match)) {
            $options['header_title_gradient_enabled'] = $match[1] === '1';
        }
        if (preg_match('/--page_background_gradient_enabled:\s*(0|1);/', $content, $match)) {
            $options['page_background_gradient_enabled'] = $match[1] === '1';
        }

        return $options;
    }

    public function loadColorsFromCss(string $customCssPath): array
    {
        $colors = $this->defaultColors();
        if (!file_exists($customCssPath)) {
            return $colors;
        }

        $content = file_get_contents($customCssPath);
        preg_match_all('/--([a-z_]+):\s*(#[0-9a-fA-F]{6});/', $content, $matches);
        if (!empty($matches[1]) && !empty($matches[2])) {
            foreach ($matches[1] as $i => $varName) {
                if (isset($colors[$varName])) {
                    $colors[$varName] = $matches[2][$i];
                }
            }
        }

        return $colors;
    }

    public function loadOptionsFile(string $customCssPath): array
    {
        if (!file_exists($customCssPath)) {
            return $this->defaultOptions();
        }

        return $this->loadOptionsFromCss((string)file_get_contents($customCssPath));
    }

    public function cleanColors($colors): array
    {
        $clean = [];
        foreach ($this->defaultColors() as $key => $defaultValue) {
            $value = $colors[$key] ?? $defaultValue;
            $clean[$key] = is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? $value : $defaultValue;
        }

        return $clean;
    }

    public function buildCustomCss(array $colors, array $options = []): string
    {
        $options = array_merge($this->defaultOptions(), $options);
        $cssVars = ":root {\n";
        foreach ($colors as $key => $value) {
            if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                $cssVars .= "    --{$key}: {$value};\n";
            }
        }
        $gradientEnabled = !empty($options['header_title_gradient_enabled']);
        $cssVars .= "    --header_title_gradient_enabled: " . ($gradientEnabled ? "1" : "0") . ";\n";
        $pageGradientEnabled = !empty($options['page_background_gradient_enabled']);
        $cssVars .= "    --page_background_gradient_enabled: " . ($pageGradientEnabled ? "1" : "0") . ";\n";
        if ($gradientEnabled) {
            $cssVars .= "    --header_title_background: linear-gradient(135deg, var(--header_text_color) 0%, var(--header_title_gradient_color) 100%);\n";
            $cssVars .= "    --header_title_fill: transparent;\n";
        } else {
            $cssVars .= "    --header_title_background: none;\n";
            $cssVars .= "    --header_title_fill: var(--header_text_color);\n";
        }
        if ($pageGradientEnabled) {
            $cssVars .= "    --page_background_css: radial-gradient(circle at top left, color-mix(in srgb, var(--background_gradient_color) 36%, transparent) 0%, transparent 34%), linear-gradient(135deg, var(--background_color) 0%, var(--background_gradient_color) 100%);\n";
        } else {
            $cssVars .= "    --page_background_css: var(--background_color);\n";
        }
        $cssVars .= "}\n\n";

        $cssVars .= <<<'CSS'
body {
    background: var(--page_background_css) !important;
    background-color: var(--background_color) !important;
    color: var(--text_color) !important;
}

a,
.player-name a,
.credits-link {
    color: var(--link_color) !important;
}

.main-header {
    border-bottom-color: var(--accent_color) !important;
}

.header-overlay {
    background: linear-gradient(135deg, color-mix(in srgb, var(--primary_color) 82%, transparent) 0%, color-mix(in srgb, var(--accent_color) 14%, transparent) 50%, color-mix(in srgb, var(--primary_color) 90%, transparent) 100%) !important;
}

.site-title,
.dashboard-header h1,
.section-heading h2,
.leaderboard-chart-header h2,
.stat-content h3,
.credits-box h2,
.squadron-title,
.squadron-card h3,
.squadron-member h4,
.pilot-header h1,
.pilot-stat-card h3 {
    color: var(--heading_color) !important;
    text-shadow: 0 0 10px color-mix(in srgb, var(--accent_color) 35%, transparent) !important;
}

.site-subtitle,
.dashboard-subtitle,
.section-heading p,
.server-detail-header p,
.detail-metrics span,
.detail-list-item span,
.detail-list-item small,
.muted,
.text-muted {
    color: var(--muted_text_color) !important;
}

.site-title {
    background: var(--header_title_background, none) !important;
    color: var(--header_text_color) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: var(--header_title_fill, var(--header_text_color)) !important;
    background-clip: text !important;
}

.site-subtitle {
    color: var(--header_subtitle_color) !important;
}

nav,
.main-nav,
.navbar {
    background-color: var(--nav_background_color) !important;
}

nav a,
.main-nav a,
.navbar a {
    color: var(--nav_text_color) !important;
}

nav a:hover,
.main-nav a:hover,
.navbar a:hover {
    color: var(--nav_hover_color) !important;
}

.nav-bar,
.mobile-menu-header {
    background: var(--nav_background_color) !important;
}

.mobile-menu-toggle {
    background: color-mix(in srgb, var(--nav_background_color) 84%, transparent) !important;
    border-color: color-mix(in srgb, var(--nav_hover_color) 35%, transparent) !important;
}

.hamburger-line {
    background-color: var(--nav_text_color) !important;
}

.mobile-menu-header {
    border-bottom-color: var(--nav_hover_color) !important;
}

.mobile-menu-title,
.mobile-menu-close,
.nav-link,
.nav-dropdown-button,
.nav-dropdown-caret {
    color: var(--nav_text_color) !important;
}

.mobile-menu-close:hover,
.nav-link:hover,
.nav-link:focus,
.nav-link:active,
.public-nav-dropdown.open > .nav-dropdown-button {
    background: color-mix(in srgb, var(--nav_hover_color) 18%, transparent) !important;
    color: var(--nav_hover_color) !important;
}

.nav-menu li {
    border-bottom-color: color-mix(in srgb, var(--nav_text_color) 12%, transparent) !important;
}

.public-nav-dropdown-menu {
    background: color-mix(in srgb, var(--nav_background_color) 88%, #000 12%) !important;
    border-color: color-mix(in srgb, var(--nav_hover_color) 24%, transparent) !important;
}

.mobile-menu-overlay {
    background: color-mix(in srgb, var(--background_color) 72%, #000 28%) !important;
}

.stat-card,
.stats-card,
.squadron-card,
.squadron-member,
.api-attendance .insight-card,
.api-insights .insight-card,
.api-insights .insight-panel,
.api-insights .top-api-card,
.leaderboard-chart-panel,
.server-detail-card,
.credits-box,
.trophy-box,
.chart-container,
.table-wrapper {
    background: linear-gradient(135deg, var(--card_color) 0%, var(--card_alt_color) 100%) !important;
    border-color: color-mix(in srgb, var(--accent_color) 36%, transparent) !important;
}

.stat-card:hover,
.stats-card:hover,
.squadron-card:hover,
.squadron-member:hover,
.api-attendance .insight-card:hover,
.api-insights .insight-card:hover,
.api-insights .insight-panel:hover,
.chart-container:hover,
.leaderboard-chart-panel:hover,
.server-detail-card:hover,
.trophy-box:hover {
    border-color: var(--accent_color) !important;
    box-shadow: 0 12px 40px color-mix(in srgb, var(--accent_color) 25%, transparent) !important;
}

.detail-metrics div,
.detail-split section,
.credits-list a,
.detail-list-item,
.server-detail-meta,
.chart-filter-group,
.chart-stat,
.api-insights .rank-row,
.api-insights .insight-list li {
    background: color-mix(in srgb, var(--surface_dark_color) 78%, transparent) !important;
    border-color: color-mix(in srgb, var(--border_color) 55%, transparent) !important;
}

.stat-card,
.api-attendance .insight-card,
.api-insights .insight-card,
.api-insights .insight-panel,
.api-insights .top-api-card,
.leaderboard-chart-panel,
.server-detail-card,
.credits-box,
.trophy-box,
.chart-container,
.table-wrapper {
    color: var(--card_text_color) !important;
}

.stat-content h3,
.stat-number,
.insight-card strong,
.api-attendance .insight-content span,
.api-insights .insight-card strong,
.api-insights .insight-panel h3,
.api-insights .rank-row strong,
.api-insights .rank-number,
.rank-number,
.api-insights h3,
.server-detail-header h3,
.detail-metrics span,
.detail-split h4,
.leaderboard-chart-header h2,
.chart-container h2,
.credits-box h2,
.trophy-box strong,
.chart-info:hover,
.loading-overlay p {
    color: var(--card_heading_color) !important;
    text-shadow: 0 0 10px color-mix(in srgb, var(--accent_color) 28%, transparent) !important;
}

.api-attendance .insight-content span {
    font-weight: 700 !important;
}

.stat-content p,
.api-attendance .insight-content strong,
.api-insights .insight-card span,
.api-insights .rank-row em,
.server-detail-header p,
.detail-list-item span,
.detail-list-item small,
.leaderboard-chart-panel p,
.chart-filter-group label,
.chart-stat span,
.credits-box p {
    color: var(--card_muted_text_color) !important;
}

.pilot-card,
.chart-wrapper,
.no-stats-message {
    background: linear-gradient(135deg, var(--card_color) 0%, var(--card_alt_color) 100%) !important;
    border: 1px solid color-mix(in srgb, var(--accent_color) 32%, transparent) !important;
    color: var(--card_text_color) !important;
}

.pilot-card h3,
.chart-wrapper h4,
.stat-group h4,
.no-stats-message p:first-child {
    color: var(--card_heading_color) !important;
    border-bottom-color: color-mix(in srgb, var(--border_color) 70%, transparent) !important;
    text-shadow: 0 0 10px color-mix(in srgb, var(--accent_color) 28%, transparent) !important;
}

.stat-item {
    background-color: color-mix(in srgb, var(--surface_dark_color) 88%, transparent) !important;
    border: 1px solid color-mix(in srgb, var(--border_color) 35%, transparent) !important;
    color: var(--card_text_color) !important;
}

.stat-label,
.no-stats-message,
.no-stats-message p,
.chart-info {
    color: var(--card_muted_text_color) !important;
}

.stat-value {
    color: var(--card_text_color) !important;
}

.chart-wrapper:hover {
    box-shadow: 0 0 15px color-mix(in srgb, var(--accent_color) 30%, transparent) !important;
}

.chart-wrapper:hover::after {
    background-color: var(--surface_dark_color) !important;
    color: var(--card_text_color) !important;
}

.chart-wrapper:hover::before {
    border-top-color: var(--surface_dark_color) !important;
}

.pagination-controls button,
.pagination-container button {
    background: linear-gradient(135deg, var(--surface_color) 0%, var(--surface_dark_color) 100%) !important;
    border-color: color-mix(in srgb, var(--accent_color) 35%, transparent) !important;
    color: var(--accent_color) !important;
}

.pagination-controls button:hover,
.pagination-container button:hover {
    background: linear-gradient(135deg, var(--accent_color) 0%, var(--accent_hover_color) 100%) !important;
    border-color: var(--accent_color) !important;
    color: var(--card_text_color) !important;
    box-shadow: 0 4px 12px color-mix(in srgb, var(--accent_color) 30%, transparent) !important;
}

.pagination-controls button:disabled,
.pagination-container button:disabled {
    background: var(--surface_dark_color) !important;
    border-color: color-mix(in srgb, var(--border_color) 40%, transparent) !important;
    color: var(--muted_text_color) !important;
}

.pagination-controls span,
.pagination-container {
    color: var(--muted_text_color) !important;
}

.loader {
    border-color: color-mix(in srgb, var(--accent_color) 30%, transparent) !important;
    border-top-color: var(--accent_color) !important;
}

.chart-info:hover,
.status-dot {
    background-color: var(--accent_color) !important;
}

.status-indicator {
    border-color: color-mix(in srgb, var(--accent_color) 60%, transparent) !important;
}

.status-text {
    color: var(--accent_color) !important;
}

input[type="text"]#searchInput,
input[type="text"]#playerSearchInput,
input[type="text"]#pilot-name,
.search-container input[type="text"],
.search-bar input[type="text"] {
    background-color: color-mix(in srgb, var(--surface_dark_color) 82%, transparent) !important;
    border-color: color-mix(in srgb, var(--border_color) 70%, transparent) !important;
    color: var(--text_color) !important;
}

input[type="text"]:focus {
    border-color: var(--accent_color) !important;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent_color) 22%, transparent) !important;
}

.search-container button,
.search-button,
button[onclick*="search"] {
    background: linear-gradient(135deg, var(--accent_color) 0%, var(--accent_hover_color) 100%) !important;
    box-shadow: 0 4px 15px color-mix(in srgb, var(--accent_color) 30%, transparent) !important;
}

.search-container button:hover,
.search-button:hover,
button[onclick*="search"]:hover {
    background: linear-gradient(135deg, var(--accent_hover_color) 0%, var(--accent_color) 100%) !important;
    box-shadow: 0 6px 20px color-mix(in srgb, var(--accent_color) 40%, transparent) !important;
}

#multiple-results h3,
#search-results h3 {
    color: var(--heading_color) !important;
}

.results-list {
    background: linear-gradient(135deg, var(--card_color) 0%, var(--card_alt_color) 100%) !important;
    border: 1px solid color-mix(in srgb, var(--border_color) 60%, transparent) !important;
    color: var(--card_text_color) !important;
}

.result-item {
    background-color: color-mix(in srgb, var(--surface_dark_color) 88%, transparent) !important;
    border: 1px solid color-mix(in srgb, var(--border_color) 35%, transparent) !important;
    color: var(--card_text_color) !important;
}

.result-item:hover,
.result-item:focus {
    background-color: color-mix(in srgb, var(--accent_color) 16%, var(--surface_dark_color)) !important;
    border-color: color-mix(in srgb, var(--accent_color) 55%, transparent) !important;
    color: var(--accent_hover_color) !important;
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--accent_color) 20%, transparent) !important;
}

.result-item:active {
    background-color: color-mix(in srgb, var(--accent_color) 24%, var(--surface_dark_color)) !important;
    color: var(--accent_hover_color) !important;
}

#squadronsTable,
#membersTable,
#leaderboardTable {
    border-color: color-mix(in srgb, var(--accent_color) 30%, transparent) !important;
}

main h2,
main h2::before,
.toggle-header td:last-child::after,
.toggle-header:hover em,
.member-count,
.leaderboard-card-rank,
.squadron-card-name,
.expand-indicator {
    color: var(--heading_color) !important;
}

.toggle-header {
    background: linear-gradient(135deg, color-mix(in srgb, var(--accent_color) 10%, transparent) 0%, color-mix(in srgb, var(--surface_dark_color) 60%, transparent) 100%) !important;
    border-bottom-color: color-mix(in srgb, var(--accent_color) 30%, transparent) !important;
}

.toggle-header:hover {
    background: linear-gradient(135deg, color-mix(in srgb, var(--accent_color) 18%, transparent) 0%, color-mix(in srgb, var(--surface_dark_color) 70%, transparent) 100%) !important;
    box-shadow: 0 2px 8px color-mix(in srgb, var(--accent_color) 20%, transparent) !important;
}

#membersTable tbody tr:not(.toggle-header):hover,
#leaderboardTable tbody tr:hover,
.member-item:active {
    background: color-mix(in srgb, var(--accent_color) 8%, transparent) !important;
}

#membersTable tbody tr:not(.toggle-header):hover {
    border-left-color: var(--accent_color) !important;
}

.member-name a:hover {
    color: var(--accent_hover_color) !important;
}

#squadronsTable img,
#membersTable img,
#leaderboardTable img,
.squadron-card-logo {
    border-color: color-mix(in srgb, var(--accent_color) 30%, transparent) !important;
}

#squadronsTable tr:hover img,
#membersTable tr:hover img,
#leaderboardTable tr:hover img {
    border-color: var(--accent_color) !important;
    box-shadow: 0 0 10px color-mix(in srgb, var(--accent_color) 30%, transparent) !important;
}

.member-count,
.squadron-placeholder {
    background: color-mix(in srgb, var(--accent_color) 12%, transparent) !important;
}

table,
#leaderboardTable,
#serversTable {
    background-color: var(--table_row_color) !important;
    color: var(--table_text_color) !important;
    border-color: var(--border_color) !important;
}

.mobile-card {
    background: linear-gradient(135deg, var(--card_color) 0%, var(--card_alt_color) 100%) !important;
    border-color: color-mix(in srgb, var(--accent_color) 32%, transparent) !important;
    color: var(--card_text_color) !important;
}

.mobile-card:active {
    background: color-mix(in srgb, var(--accent_color) 12%, var(--card_alt_color)) !important;
}

.leaderboard-card-name,
.leaderboard-card-stat {
    color: var(--card_text_color) !important;
}

.leaderboard-card-stat span {
    color: var(--card_muted_text_color) !important;
}

th,
#leaderboardTable th,
#serversTable th {
    background-color: var(--table_header_color) !important;
    color: var(--table_header_text_color) !important;
    border-color: var(--accent_color) !important;
}

td,
#leaderboardTable td,
#serversTable td {
    color: var(--table_text_color) !important;
    border-color: color-mix(in srgb, var(--border_color) 65%, transparent) !important;
}

.player-name,
.player-name a,
#leaderboardTable .player-name,
#leaderboardTable .player-name a {
    color: var(--table_player_name_color) !important;
}

tr:hover,
#leaderboardTable tbody tr:hover,
#serversTable tr:hover {
    background-color: var(--table_hover_color) !important;
}

.status-dot,
.detail-status.status-online,
.detail-status.status-running {
    background-color: var(--success_color) !important;
    border-color: color-mix(in srgb, var(--success_color) 50%, transparent) !important;
    color: var(--success_color) !important;
}

.detail-status.status-paused,
.detail-status.status-starting,
.admin-button,
.warning-box {
    border-color: color-mix(in srgb, var(--warning_color) 50%, transparent) !important;
    color: var(--warning_color) !important;
}

.detail-status.status-offline,
.detail-status.status-shutdown,
.alert-error {
    border-color: color-mix(in srgb, var(--danger_color) 50%, transparent) !important;
    color: var(--danger_color) !important;
}

button,
.btn,
input[type="submit"] {
    border-color: var(--border_color);
}

.btn-primary {
    background-color: var(--accent_color) !important;
    border-color: var(--accent_color) !important;
}

.btn-primary:hover,
.credits-link:hover,
.credits-list a:hover {
    color: var(--accent_hover_color) !important;
    border-color: var(--accent_hover_color) !important;
}

footer {
    background-color: var(--footer_background_color) !important;
    color: var(--footer_text_color) !important;
    border-color: var(--border_color) !important;
}
CSS;

        return $cssVars;
    }

    public function cleanOptions($options): array
    {
        return [
            'header_title_gradient_enabled' => !empty($options['header_title_gradient_enabled']),
            'page_background_gradient_enabled' => !empty($options['page_background_gradient_enabled']),
        ];
    }
}
