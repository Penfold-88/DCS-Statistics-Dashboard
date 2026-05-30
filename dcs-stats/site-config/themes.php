<?php
/**
 * Themes Management Page
 * Allows super admins to customize site appearance
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once __DIR__ . '/../config_path.php';
require_once __DIR__ . '/../chart_theme.php';
require_once __DIR__ . '/../language.php';

// Require admin login and permission
requireAdmin();
requirePermission('manage_themes');

// Get current admin to check specific permissions
$currentAdmin = getCurrentAdmin();
$demoRestricted = isDemoRestricted($currentAdmin);
$isAirBoss = ($currentAdmin['role'] === ROLE_AIR_BOSS);

$message = '';
$error = '';

function getDefaultHeaderImageSettings() {
    return [
        'image' => 'dcs-header-image.jpg',
        'position_x' => 50,
        'position_y' => 50,
        'branding_mode' => 'text',
        'logo' => '',
        'logo_height' => 72
    ];
}

function getHeaderImageSettingsPath() {
    return __DIR__ . '/data/header_image.json';
}

function loadHeaderImageSettings() {
    $settings = getDefaultHeaderImageSettings();
    $path = getHeaderImageSettingsPath();
    if (file_exists($path)) {
        $saved = json_decode(file_get_contents($path), true);
        if (is_array($saved)) {
            $settings = array_merge($settings, array_intersect_key($saved, $settings));
        }
    }

    $imagePath = __DIR__ . '/../' . ltrim($settings['image'], '/');
    if (empty($settings['image']) || !file_exists($imagePath)) {
        $settings['image'] = getDefaultHeaderImageSettings()['image'];
    }

    $settings['position_x'] = max(0, min(100, (int)$settings['position_x']));
    $settings['position_y'] = max(0, min(100, (int)$settings['position_y']));
    if (!in_array($settings['branding_mode'], ['text', 'logo', 'both'], true)) {
        $settings['branding_mode'] = 'text';
    }
    $settings['logo_height'] = max(32, min(96, (int)$settings['logo_height']));
    if (!empty($settings['logo']) && !file_exists(__DIR__ . '/../' . ltrim($settings['logo'], '/'))) {
        $settings['logo'] = '';
        if ($settings['branding_mode'] === 'logo') {
            $settings['branding_mode'] = 'text';
        }
    }
    return $settings;
}

function saveHeaderImageSettings($settings) {
    $settings = array_merge(getDefaultHeaderImageSettings(), $settings);
    $settings['position_x'] = max(0, min(100, (int)$settings['position_x']));
    $settings['position_y'] = max(0, min(100, (int)$settings['position_y']));
    if (!in_array($settings['branding_mode'], ['text', 'logo', 'both'], true)) {
        $settings['branding_mode'] = 'text';
    }
    $settings['logo_height'] = max(32, min(96, (int)$settings['logo_height']));

    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }

    $imagePath = __DIR__ . '/../' . ltrim($settings['image'], '/');
    if (empty($settings['image']) || !file_exists($imagePath)) {
        $settings['image'] = getDefaultHeaderImageSettings()['image'];
    }
    if (!empty($settings['logo']) && !file_exists(__DIR__ . '/../' . ltrim($settings['logo'], '/'))) {
        $settings['logo'] = '';
    }

    $imageUrl = str_replace(["\\", "'"], ['/', "\\'"], $settings['image']);
    $css = ".header-background {\n";
    $css .= "    background-image: url('{$imageUrl}') !important;\n";
    $css .= "    background-size: cover !important;\n";
    $css .= "    background-position: {$settings['position_x']}% {$settings['position_y']}% !important;\n";
    $css .= "    background-repeat: no-repeat !important;\n";
    $css .= "}\n";

    return file_put_contents(getHeaderImageSettingsPath(), json_encode($settings, JSON_PRETTY_PRINT)) !== false
        && file_put_contents(__DIR__ . '/../header_custom.css', $css) !== false;
}

function getDefaultThemeColors() {
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
        'table_hover_color' => '#3a3a3a'
    ];
}

function getThemeColorGroups() {
    return [
        dcs_t('admin.themes.group_page_text') => [
            'background_color' => dcs_t('admin.themes.page_background'),
            'background_gradient_color' => dcs_t('admin.themes.page_gradient_end'),
            'text_color' => dcs_t('admin.themes.main_text'),
            'muted_text_color' => dcs_t('admin.themes.muted_text'),
            'heading_color' => dcs_t('admin.themes.headings'),
            'link_color' => dcs_t('admin.themes.links')
        ],
        dcs_t('admin.themes.group_brand') => [
            'accent_color' => dcs_t('admin.themes.main_accent'),
            'accent_hover_color' => dcs_t('admin.themes.accent_hover'),
            'border_color' => dcs_t('admin.themes.borders'),
            'success_color' => dcs_t('admin.themes.success'),
            'warning_color' => dcs_t('admin.themes.warning'),
            'danger_color' => dcs_t('admin.themes.danger'),
            'info_color' => dcs_t('admin.themes.info')
        ],
        dcs_t('admin.themes.group_panels') => [
            'surface_color' => dcs_t('admin.themes.panel_top'),
            'surface_dark_color' => dcs_t('admin.themes.panel_bottom'),
            'card_color' => dcs_t('admin.themes.card_top'),
            'card_alt_color' => dcs_t('admin.themes.card_bottom'),
            'card_heading_color' => dcs_t('admin.themes.card_headings'),
            'card_text_color' => dcs_t('admin.themes.card_text'),
            'card_muted_text_color' => dcs_t('admin.themes.card_muted_text'),
            'secondary_color' => dcs_t('admin.themes.secondary_surface')
        ],
        dcs_t('admin.themes.group_header_nav') => [
            'primary_color' => dcs_t('admin.themes.primary_background'),
            'nav_background_color' => dcs_t('admin.themes.nav_background'),
            'nav_text_color' => dcs_t('admin.themes.nav_text'),
            'nav_hover_color' => dcs_t('admin.themes.nav_hover'),
            'header_text_color' => dcs_t('admin.themes.header_title'),
            'header_title_gradient_color' => dcs_t('admin.themes.header_gradient_end'),
            'header_subtitle_color' => dcs_t('admin.themes.header_subtitle')
        ],
        dcs_t('admin.themes.group_footer') => [
            'footer_background_color' => dcs_t('admin.themes.footer_background'),
            'footer_text_color' => dcs_t('admin.themes.footer_text')
        ],
        dcs_t('admin.themes.group_tables') => [
            'table_header_color' => dcs_t('admin.themes.table_header'),
            'table_header_text_color' => dcs_t('admin.themes.table_header_text'),
            'table_row_color' => dcs_t('admin.themes.table_row'),
            'table_text_color' => dcs_t('admin.themes.table_text'),
            'table_player_name_color' => dcs_t('admin.themes.table_player_names'),
            'table_hover_color' => dcs_t('admin.themes.table_hover')
        ]
    ];
}

function getDefaultThemeOptions() {
    return [
        'header_title_gradient_enabled' => false,
        'page_background_gradient_enabled' => false
    ];
}

function loadThemeOptionsFromCss($content) {
    $options = getDefaultThemeOptions();
    if (preg_match('/--header_title_gradient_enabled:\s*(0|1);/', $content, $match)) {
        $options['header_title_gradient_enabled'] = $match[1] === '1';
    }
    if (preg_match('/--page_background_gradient_enabled:\s*(0|1);/', $content, $match)) {
        $options['page_background_gradient_enabled'] = $match[1] === '1';
    }
    return $options;
}

function loadThemeColorsFromCss($customCSS) {
    $colors = getDefaultThemeColors();
    if (!file_exists($customCSS)) {
        return $colors;
    }

    $content = file_get_contents($customCSS);
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

function loadThemeOptionsFile($customCSS) {
    if (!file_exists($customCSS)) {
        return getDefaultThemeOptions();
    }

    return loadThemeOptionsFromCss(file_get_contents($customCSS));
}

function cleanThemeColors($colors) {
    $clean = [];
    foreach (getDefaultThemeColors() as $key => $defaultValue) {
        $value = $colors[$key] ?? $defaultValue;
        $clean[$key] = is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? $value : $defaultValue;
    }
    return $clean;
}

function cleanThemeOptions($options) {
    return [
        'header_title_gradient_enabled' => !empty($options['header_title_gradient_enabled']),
        'page_background_gradient_enabled' => !empty($options['page_background_gradient_enabled'])
    ];
}

function getThemePresetPath() {
    return __DIR__ . '/data/theme_presets.json';
}

function getBuiltInThemePresets() {
    $defaults = getDefaultThemeColors();

    return [
        'carrier_night' => [
            'name' => dcs_t('admin.themes.preset_carrier_night'),
            'description' => dcs_t('admin.themes.preset_carrier_night_desc'),
            'colors' => $defaults,
            'options' => ['header_title_gradient_enabled' => false],
            'chart_colors' => getDefaultChartTheme()
        ],
        'blue_angels' => [
            'name' => dcs_t('admin.themes.preset_blue_angels'),
            'description' => dcs_t('admin.themes.preset_blue_angels_desc'),
            'colors' => array_merge($defaults, [
                'primary_color' => '#071426',
                'secondary_color' => '#102b4e',
                'background_color' => '#06111f',
                'surface_color' => '#12355d',
                'surface_dark_color' => '#081b31',
                'card_color' => '#12355d',
                'card_alt_color' => '#081b31',
                'card_heading_color' => '#f4c542',
                'card_text_color' => '#ffffff',
                'card_muted_text_color' => '#c9d8ee',
                'text_color' => '#f4f8ff',
                'muted_text_color' => '#b8c7da',
                'heading_color' => '#f4c542',
                'link_color' => '#73b7ff',
                'accent_color' => '#f4c542',
                'accent_hover_color' => '#ffe27a',
                'border_color' => '#315b89',
                'nav_background_color' => '#071426',
                'nav_text_color' => '#f4f8ff',
                'nav_hover_color' => '#f4c542',
                'header_text_color' => '#ffffff',
                'header_title_gradient_color' => '#f4c542',
                'header_subtitle_color' => '#c9d8ee',
                'footer_background_color' => '#081b31',
                'footer_text_color' => '#dce8f7',
                'table_header_color' => '#081b31',
                'table_header_text_color' => '#f4c542',
                'table_row_color' => '#102b4e',
                'table_text_color' => '#f4f8ff',
                'table_player_name_color' => '#ffe27a',
                'table_hover_color' => '#173e6d'
            ]),
            'options' => ['header_title_gradient_enabled' => true],
            'chart_colors' => array_merge(getDefaultChartTheme(), [
                'chart_primary_color' => '#f4c542',
                'chart_secondary_color' => '#73b7ff',
                'chart_grid_color' => '#315b89',
                'chart_text_color' => '#f4f8ff',
                'home_top_pilots_color' => '#f4c542',
                'home_top_pilots_grid_color' => '#315b89',
                'home_top_pilots_text_color' => '#f4f8ff',
                'home_activity_color' => '#73b7ff',
                'home_activity_grid_color' => '#315b89',
                'home_activity_text_color' => '#f4f8ff'
            ])
        ],
        'red_flag' => [
            'name' => dcs_t('admin.themes.preset_red_flag'),
            'description' => dcs_t('admin.themes.preset_red_flag_desc'),
            'colors' => array_merge($defaults, [
                'primary_color' => '#171717',
                'secondary_color' => '#292323',
                'background_color' => '#101010',
                'surface_color' => '#342525',
                'surface_dark_color' => '#1d1818',
                'card_color' => '#342525',
                'card_alt_color' => '#1d1818',
                'card_heading_color' => '#ff4d4d',
                'card_text_color' => '#fff3ef',
                'card_muted_text_color' => '#d8c8c0',
                'text_color' => '#fff3ef',
                'muted_text_color' => '#c7b8b0',
                'heading_color' => '#ff4d4d',
                'link_color' => '#ffb454',
                'accent_color' => '#d92828',
                'accent_hover_color' => '#ff6b6b',
                'border_color' => '#744040',
                'nav_background_color' => '#171717',
                'nav_text_color' => '#fff3ef',
                'nav_hover_color' => '#ff4d4d',
                'header_text_color' => '#fff3ef',
                'header_title_gradient_color' => '#ffb454',
                'header_subtitle_color' => '#d8c8c0',
                'footer_background_color' => '#1d1818',
                'footer_text_color' => '#d8c8c0',
                'table_header_color' => '#1d1818',
                'table_header_text_color' => '#ff4d4d',
                'table_row_color' => '#292323',
                'table_text_color' => '#fff3ef',
                'table_player_name_color' => '#ffb454',
                'table_hover_color' => '#3f2d2d'
            ]),
            'options' => ['header_title_gradient_enabled' => true],
            'chart_colors' => array_merge(getDefaultChartTheme(), [
                'chart_primary_color' => '#d92828',
                'chart_secondary_color' => '#ffb454',
                'chart_grid_color' => '#744040',
                'chart_text_color' => '#fff3ef',
                'home_combat_kills_color' => '#d92828',
                'home_combat_deaths_color' => '#ffb454',
                'home_combat_text_color' => '#fff3ef',
                'home_activity_color' => '#ff4d4d',
                'home_activity_grid_color' => '#744040',
                'home_activity_text_color' => '#fff3ef'
            ])
        ],
        'arctic_ops' => [
            'name' => dcs_t('admin.themes.preset_arctic_ops'),
            'description' => dcs_t('admin.themes.preset_arctic_ops_desc'),
            'colors' => array_merge($defaults, [
                'primary_color' => '#17202a',
                'secondary_color' => '#243241',
                'background_color' => '#101820',
                'surface_color' => '#2d3d4f',
                'surface_dark_color' => '#1a2632',
                'card_color' => '#2d3d4f',
                'card_alt_color' => '#1a2632',
                'card_heading_color' => '#79d7ff',
                'card_text_color' => '#f4fbff',
                'card_muted_text_color' => '#c3d5e0',
                'text_color' => '#f4fbff',
                'muted_text_color' => '#b7c8d4',
                'heading_color' => '#79d7ff',
                'link_color' => '#93c5fd',
                'accent_color' => '#38bdf8',
                'accent_hover_color' => '#8bdfff',
                'border_color' => '#466075',
                'nav_background_color' => '#17202a',
                'nav_text_color' => '#f4fbff',
                'nav_hover_color' => '#79d7ff',
                'header_text_color' => '#ffffff',
                'header_title_gradient_color' => '#79d7ff',
                'header_subtitle_color' => '#c3d5e0',
                'footer_background_color' => '#1a2632',
                'footer_text_color' => '#c3d5e0',
                'table_header_color' => '#1a2632',
                'table_header_text_color' => '#79d7ff',
                'table_row_color' => '#243241',
                'table_text_color' => '#f4fbff',
                'table_player_name_color' => '#93c5fd',
                'table_hover_color' => '#33475c'
            ]),
            'options' => ['header_title_gradient_enabled' => true],
            'chart_colors' => array_merge(getDefaultChartTheme(), [
                'chart_primary_color' => '#38bdf8',
                'chart_secondary_color' => '#93c5fd',
                'chart_grid_color' => '#466075',
                'chart_text_color' => '#f4fbff',
                'home_top_pilots_color' => '#38bdf8',
                'home_squadrons_color' => '#93c5fd',
                'home_activity_color' => '#79d7ff',
                'home_activity_grid_color' => '#466075',
                'home_activity_text_color' => '#f4fbff'
            ])
        ]
    ];
}

function loadCustomThemePresets() {
    $path = getThemePresetPath();
    if (!file_exists($path)) {
        return [];
    }

    $presets = json_decode(file_get_contents($path), true);
    return is_array($presets) ? $presets : [];
}

function saveCustomThemePresets($presets) {
    $path = getThemePresetPath();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }

    return @file_put_contents($path, json_encode(array_values($presets), JSON_PRETTY_PRINT)) !== false;
}

function getThemePresetById($presetId, $presetType) {
    if ($presetType === 'custom') {
        foreach (loadCustomThemePresets() as $index => $preset) {
            if ((string)$index === (string)$presetId) {
                return $preset;
            }
        }
        return null;
    }

    $builtIns = getBuiltInThemePresets();
    return $builtIns[$presetId] ?? null;
}

function applyThemePreset($preset) {
    if (!is_array($preset)) {
        return false;
    }

    $colors = cleanThemeColors(array_merge(getDefaultThemeColors(), $preset['colors'] ?? []));
    $options = cleanThemeOptions($preset['options'] ?? []);

    if (file_put_contents(__DIR__ . '/../custom_theme.css', buildCustomThemeCss($colors, $options)) === false) {
        return false;
    }

    if (isset($preset['chart_colors']) && is_array($preset['chart_colors'])) {
        saveChartTheme($preset['chart_colors']);
    }

    return true;
}

function buildThemeSettingsBackup($menuConfigFile) {
    $customCSS = __DIR__ . '/../custom_theme.css';

    return [
        'type' => 'dcs_statistics_dashboard_theme_settings',
        'version' => 1,
        'created_at' => date('c'),
        'theme_colors' => loadThemeColorsFromCss($customCSS),
        'theme_options' => loadThemeOptionsFile($customCSS),
        'chart_colors' => loadChartTheme(),
        'header_image' => loadHeaderImageSettings(),
        'menu' => file_exists($menuConfigFile) ? json_decode(file_get_contents($menuConfigFile), true) : null
    ];
}

function importThemeSettingsBackup($backup, $menuConfigFile) {
    if (!is_array($backup) || ($backup['type'] ?? '') !== 'dcs_statistics_dashboard_theme_settings') {
        return false;
    }

    $colors = cleanThemeColors($backup['theme_colors'] ?? []);
    $options = cleanThemeOptions($backup['theme_options'] ?? []);
    if (file_put_contents(__DIR__ . '/../custom_theme.css', buildCustomThemeCss($colors, $options)) === false) {
        return false;
    }

    if (isset($backup['chart_colors']) && is_array($backup['chart_colors'])) {
        saveChartTheme($backup['chart_colors']);
    }

    if (isset($backup['header_image']) && is_array($backup['header_image'])) {
        saveHeaderImageSettings($backup['header_image']);
    }

    if (isset($backup['menu']) && is_array($backup['menu'])) {
        file_put_contents($menuConfigFile, json_encode($backup['menu'], JSON_PRETTY_PRINT));
    }

    return true;
}

function buildCustomThemeCss($colors, $options = []) {
    $options = array_merge(getDefaultThemeOptions(), $options);
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

// Get writable path for menu configuration
function getMenuConfigPath() {
    // Try primary location with data directory
    $primaryPath = __DIR__ . '/data/menu_config.json';
    $primaryDir = dirname($primaryPath);
    
    // Check if directory exists and is writable
    if (is_dir($primaryDir) && is_writable($primaryDir)) {
        return $primaryPath;
    }
    
    // Try to create directory with proper permissions
    if (!is_dir($primaryDir)) {
        @mkdir($primaryDir, 0700, true);
        @chmod($primaryDir, 0700);
        if (is_dir($primaryDir) && is_writable($primaryDir)) {
            return $primaryPath;
        }
    }
    
    // Try alternative location in parent directory
    $altPath = __DIR__ . '/../menu_config.json';
    $altDir = dirname($altPath);
    if (is_writable($altDir)) {
        return $altPath;
    }
    
    // Fall back to temp directory
    $tempDir = sys_get_temp_dir() . '/dcs_stats';
    if (!is_dir($tempDir)) {
        @mkdir($tempDir, 0700, true);
    }
    
    return $tempDir . '/menu_config.json';
}

// Load site features to check if Discord/Squadron links are enabled
require_once __DIR__ . '/../site_features.php';

// Load menu configuration
$menuConfigFile = getMenuConfigPath();
$defaultMenuItems = [
    ['name' => 'Home', 'url' => 'index.php', 'enabled' => true, 'type' => 'page'],
    ['name' => 'Leaderboard', 'url' => 'leaderboard.php', 'enabled' => true, 'type' => 'page'],
    ['name' => 'Pilot Statistics', 'url' => 'pilot_statistics.php', 'enabled' => true, 'type' => 'page'],
    ['name' => 'Pilot Credits', 'url' => 'pilot_credits.php', 'enabled' => true, 'type' => 'page'],
    ['name' => 'Squadrons', 'url' => 'squadrons.php', 'enabled' => true, 'type' => 'page'],
    ['name' => 'Servers', 'url' => 'servers.php', 'enabled' => true, 'type' => 'page']
];

// Add Discord if enabled
if (isFeatureEnabled('show_discord_link')) {
    $defaultMenuItems[] = [
        'name' => 'Discord',
        'url' => getFeatureValue('discord_link_url', 'https://discord.gg/DNENf6pUNX'),
        'enabled' => true,
        'type' => 'discord'
    ];
}

// Add Squadron Homepage if enabled
if (isFeatureEnabled('show_squadron_homepage') && !empty(getFeatureValue('squadron_homepage_url'))) {
    $defaultMenuItems[] = [
        'name' => getFeatureValue('squadron_homepage_text', 'Squadron'),
        'url' => getFeatureValue('squadron_homepage_url'),
        'enabled' => true,
        'type' => 'squadron_homepage'
    ];
}

$menuItems = $defaultMenuItems;
if (file_exists($menuConfigFile)) {
    $savedMenu = json_decode(file_get_contents($menuConfigFile), true);
    if ($savedMenu && is_array($savedMenu)) {
        // Merge with default items to pick up any new Discord/Squadron links
        $savedUrls = array_column($savedMenu, 'url');
        foreach ($defaultMenuItems as $defaultItem) {
            $found = false;
            foreach ($savedMenu as &$savedItem) {
                if ($savedItem['url'] === $defaultItem['url'] || 
                    (isset($savedItem['type']) && isset($defaultItem['type']) && $savedItem['type'] === $defaultItem['type'])) {
                    $found = true;
                    // Update URL for Discord/Squadron links in case they changed
                    if (in_array($defaultItem['type'] ?? '', ['discord', 'squadron_homepage'])) {
                        $savedItem['url'] = $defaultItem['url'];
                        $savedItem['name'] = $defaultItem['name']; // Update name too for squadron
                    }
                    break;
                }
            }
            if (!$found && in_array($defaultItem['type'] ?? '', ['discord', 'squadron_homepage'])) {
                // Add new Discord/Squadron link that wasn't in saved config
                $savedMenu[] = $defaultItem;
            }
        }
        $menuItems = $savedMenu;
    }
}

// Handle theme actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!verifyCSRFToken(getRequestCSRFToken())) {
        $error = 'Invalid request token';
    } elseif ($demoRestricted) {
        $error = demoWriteLockMessage();
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'apply_theme_preset':
                $presetId = $_POST['preset_id'] ?? '';
                $presetType = $_POST['preset_type'] ?? 'built_in';
                $preset = getThemePresetById($presetId, $presetType);

                if ($preset && applyThemePreset($preset)) {
                    $message = 'Theme preset applied successfully';
                    if (function_exists('logActivity')) {
                        logActivity('THEME_PRESET_APPLY', 'Applied theme preset: ' . ($preset['name'] ?? $presetId));
                    }
                } else {
                    $error = 'Theme preset could not be applied';
                }
                break;

            case 'save_theme_preset':
                $presetName = trim($_POST['preset_name'] ?? '');
                if ($presetName === '') {
                    $error = 'Please enter a preset name';
                    break;
                }

                if (strlen($presetName) > 60) {
                    $error = 'Preset name must be 60 characters or fewer';
                    break;
                }

                $customCSS = __DIR__ . '/../custom_theme.css';
                $presets = loadCustomThemePresets();
                $presets[] = [
                    'name' => $presetName,
                    'description' => 'Saved custom squadron theme',
                    'created_at' => date('c'),
                    'colors' => loadThemeColorsFromCss($customCSS),
                    'options' => loadThemeOptionsFile($customCSS),
                    'chart_colors' => loadChartTheme()
                ];

                if (saveCustomThemePresets($presets)) {
                    $message = 'Custom theme preset saved successfully';
                    if (function_exists('logActivity')) {
                        logActivity('THEME_PRESET_SAVE', 'Saved custom theme preset: ' . $presetName);
                    }
                } else {
                    $error = 'Failed to save custom theme preset';
                }
                break;

            case 'delete_theme_preset':
                $presetIndex = (int)($_POST['preset_id'] ?? -1);
                $presets = loadCustomThemePresets();
                if (!isset($presets[$presetIndex])) {
                    $error = 'Custom preset not found';
                    break;
                }

                $deletedName = $presets[$presetIndex]['name'] ?? 'Custom preset';
                array_splice($presets, $presetIndex, 1);

                if (saveCustomThemePresets($presets)) {
                    $message = 'Custom theme preset deleted successfully';
                    if (function_exists('logActivity')) {
                        logActivity('THEME_PRESET_DELETE', 'Deleted custom theme preset: ' . $deletedName);
                    }
                } else {
                    $error = 'Failed to delete custom theme preset';
                }
                break;

            case 'export_theme_settings':
                $backup = buildThemeSettingsBackup($menuConfigFile);
                $fileName = 'dcs-theme-settings-' . date('Y-m-d-H-i-s') . '.json';
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                header('Cache-Control: no-store');
                echo json_encode($backup, JSON_PRETTY_PRINT);
                exit;

            case 'import_theme_settings':
                if (!isset($_FILES['theme_settings_file']) || $_FILES['theme_settings_file']['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Please select a theme settings backup file';
                    break;
                }

                $uploadedFile = $_FILES['theme_settings_file'];
                if ($uploadedFile['size'] > 1048576) {
                    $error = 'Theme settings backup must be less than 1MB';
                    break;
                }

                $backup = json_decode(file_get_contents($uploadedFile['tmp_name']), true);
                if (importThemeSettingsBackup($backup, $menuConfigFile)) {
                    $message = 'Theme settings restored successfully';
                    if (function_exists('logActivity')) {
                        logActivity('THEME_SETTINGS_IMPORT', 'Imported theme settings backup');
                    }
                } else {
                    $error = 'Invalid theme settings backup file';
                }
                break;

            case 'update_menu':
                // Handle menu updates
                $newMenuItems = [];
                $menuNames = $_POST['menu_names'] ?? [];
                $menuUrls = $_POST['menu_urls'] ?? [];
                $menuEnabled = $_POST['menu_enabled'] ?? [];
                $menuOrder = $_POST['menu_order'] ?? [];
                $menuTypes = $_POST['menu_types'] ?? [];
                
                // Rebuild menu items based on posted data
                foreach ($menuOrder as $index) {
                    if (isset($menuNames[$index]) && isset($menuUrls[$index])) {
                        $newMenuItems[] = [
                            'name' => $menuNames[$index],
                            'url' => $menuUrls[$index],
                            'enabled' => isset($menuEnabled[$index]),
                            'type' => $menuTypes[$index] ?? 'page'
                        ];
                    }
                }
                
                // Save to file
                $result = @file_put_contents($menuConfigFile, json_encode($newMenuItems, JSON_PRETTY_PRINT));
                if ($result === false) {
                    $error = 'Failed to save menu configuration. Please check file permissions.';
                } else {
                    $menuItems = $newMenuItems;
                    $message = 'Menu configuration updated successfully';
                    if (function_exists('logActivity')) {
                        logActivity('MENU_UPDATE', 'Updated navigation menu configuration');
                    }
                }
                break;
                
            case 'upload_css':
                // Only Air Boss can upload CSS
                if (!$isAirBoss) {
                    $error = 'Only Air Boss can upload custom CSS files';
                    break;
                }
                // Handle CSS file upload
                if (isset($_FILES['css_file']) && $_FILES['css_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadedFile = $_FILES['css_file'];
                    $fileName = $uploadedFile['name'];
                    $fileTmp = $uploadedFile['tmp_name'];
                    $fileSize = $uploadedFile['size'];
                    
                    // Validate file type
                    $allowedTypes = ['text/css', 'text/plain'];
                    $fileType = mime_content_type($fileTmp);
                    
                    if (!in_array($fileType, $allowedTypes) || !preg_match('/\.css$/i', $fileName)) {
                        $error = 'Please upload a valid CSS file';
                    } elseif ($fileSize > 1048576) { // 1MB limit
                        $error = 'CSS file size must be less than 1MB';
                    } else {
                        // Backup current CSS
                        $currentCSS = __DIR__ . '/../styles.css';
                        $backupDir = __DIR__ . '/theme_backups';
                        
                        if (!is_dir($backupDir)) {
                            mkdir($backupDir, 0755, true);
                        }
                        
                        $backupFile = $backupDir . '/styles_' . date('Y-m-d_H-i-s') . '.css';
                        copy($currentCSS, $backupFile);
                        
                        // Upload new CSS
                        if (move_uploaded_file($fileTmp, $currentCSS)) {
                            $message = 'CSS file uploaded successfully. Previous version backed up.';
                            if (function_exists('logActivity')) {
                                logActivity('THEME_UPLOAD', 'Uploaded new CSS file: ' . $fileName);
                            }
                        } else {
                            $error = 'Failed to upload CSS file';
                        }
                    }
                } else {
                    $error = 'Please select a CSS file to upload';
                }
                break;
                
            case 'update_colors':
                // Handle color updates
                $colors = [];
                foreach (getDefaultThemeColors() as $key => $defaultValue) {
                    $colors[$key] = $_POST[$key] ?? $defaultValue;
                }
                $themeOptions = [
                    'header_title_gradient_enabled' => isset($_POST['header_title_gradient_enabled']),
                    'page_background_gradient_enabled' => isset($_POST['page_background_gradient_enabled'])
                ];
                
                // Save to custom theme file
                $customCSS = __DIR__ . '/../custom_theme.css';
                file_put_contents($customCSS, buildCustomThemeCss($colors, $themeOptions));
                
                $message = 'Color theme updated successfully';
                if (function_exists('logActivity')) {
                    logActivity('THEME_COLORS', 'Updated theme colors');
                }
                break;

            case 'update_header_image':
                $headerSettings = loadHeaderImageSettings();
                $headerSettings['position_x'] = (int)($_POST['position_x'] ?? 50);
                $headerSettings['position_y'] = (int)($_POST['position_y'] ?? 50);
                if (isset($_POST['use_default_header_image'])) {
                    $headerSettings['image'] = getDefaultHeaderImageSettings()['image'];
                }
                $headerSettings['branding_mode'] = $_POST['branding_mode'] ?? 'text';
                $headerSettings['logo_height'] = (int)($_POST['logo_height'] ?? 72);
                if (isset($_POST['remove_header_logo'])) {
                    $headerSettings['logo'] = '';
                    if ($headerSettings['branding_mode'] === 'logo') {
                        $headerSettings['branding_mode'] = 'text';
                    }
                }

                if (!isset($_POST['use_default_header_image']) && isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadedFile = $_FILES['header_image'];
                    $fileTmp = $uploadedFile['tmp_name'];
                    $fileSize = $uploadedFile['size'];
                    $fileType = mime_content_type($fileTmp);
                    $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
                    $allowedTypes = [
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'webp' => 'image/webp'
                    ];

                    if (!isset($allowedTypes[$extension]) || $allowedTypes[$extension] !== $fileType) {
                        $error = 'Please upload a JPG, PNG, or WebP header image';
                        break;
                    }

                    if ($fileSize > 5242880) {
                        $error = 'Header image must be less than 5MB';
                        break;
                    }

                    $uploadDir = __DIR__ . '/../uploads';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $targetName = 'header-image.' . ($extension === 'jpeg' ? 'jpg' : $extension);
                    $targetPath = $uploadDir . '/' . $targetName;
                    if (!move_uploaded_file($fileTmp, $targetPath)) {
                        $error = 'Failed to upload header image';
                        break;
                    }

                    $headerSettings['image'] = 'uploads/' . $targetName;
                }

                if (!isset($_POST['remove_header_logo']) && isset($_FILES['header_logo']) && $_FILES['header_logo']['error'] === UPLOAD_ERR_OK) {
                    $uploadedLogo = $_FILES['header_logo'];
                    $logoTmp = $uploadedLogo['tmp_name'];
                    $logoSize = $uploadedLogo['size'];
                    $logoType = mime_content_type($logoTmp);
                    $logoExtension = strtolower(pathinfo($uploadedLogo['name'], PATHINFO_EXTENSION));
                    $allowedLogoTypes = [
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'webp' => 'image/webp'
                    ];

                    if (!isset($allowedLogoTypes[$logoExtension]) || $allowedLogoTypes[$logoExtension] !== $logoType) {
                        $error = 'Please upload a JPG, PNG, or WebP logo';
                        break;
                    }

                    if ($logoSize > 2097152) {
                        $error = 'Header logo must be less than 2MB';
                        break;
                    }

                    $uploadDir = __DIR__ . '/../uploads';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $logoTargetName = 'header-logo.' . ($logoExtension === 'jpeg' ? 'jpg' : $logoExtension);
                    $logoTargetPath = $uploadDir . '/' . $logoTargetName;
                    if (!move_uploaded_file($logoTmp, $logoTargetPath)) {
                        $error = 'Failed to upload header logo';
                        break;
                    }

                    $headerSettings['logo'] = 'uploads/' . $logoTargetName;
                }

                if (saveHeaderImageSettings($headerSettings)) {
                    $message = 'Header image settings updated successfully';
                    if (function_exists('logActivity')) {
                        logActivity('HEADER_IMAGE_UPDATE', 'Updated header image settings');
                    }
                } else {
                    $error = 'Failed to save header image settings';
                }
                break;

            case 'update_chart_colors':
                $chartTheme = [];
                foreach (getDefaultChartTheme() as $key => $defaultValue) {
                    $chartTheme[$key] = $_POST[$key] ?? $defaultValue;
                }

                if (saveChartTheme($chartTheme)) {
                    $message = 'Leaderboard chart colours updated successfully';
                    if (function_exists('logActivity')) {
                        logActivity('CHART_THEME_COLORS', 'Updated leaderboard chart colours');
                    }
                } else {
                    $error = 'Failed to save leaderboard chart colours';
                }
                break;
                
            case 'restore_backup':
                // Only Air Boss can restore backups
                if (!$isAirBoss) {
                    $error = 'Only Air Boss can restore theme backups';
                    break;
                }
                // Restore from backup
                $backupFile = $_POST['backup_file'] ?? '';
                $backupPath = __DIR__ . '/theme_backups/' . basename($backupFile);
                
                if (file_exists($backupPath)) {
                    $currentCSS = __DIR__ . '/../styles.css';
                    copy($backupPath, $currentCSS);
                    $message = 'Theme restored from backup';
                    if (function_exists('logActivity')) {
                        logActivity('THEME_RESTORE', 'Restored theme from: ' . $backupFile);
                    }
                } else {
                    $error = 'Backup file not found';
                }
                break;
        }
    }
}

$csrfToken = getCSRFToken();

// Get list of backup files
$backupDir = __DIR__ . '/theme_backups';
$backups = [];
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if (preg_match('/^styles_.*\.css$/', $file)) {
            $backups[] = [
                'filename' => $file,
                'date' => filemtime($backupDir . '/' . $file),
                'size' => filesize($backupDir . '/' . $file)
            ];
        }
    }
    // Sort by date descending
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Load current custom colors if they exist
$customCSS = __DIR__ . '/../custom_theme.css';
$customColors = loadThemeColorsFromCss($customCSS);
$themeOptions = loadThemeOptionsFile($customCSS);
$builtInThemePresets = getBuiltInThemePresets();
$customThemePresets = loadCustomThemePresets();

$chartColors = loadChartTheme();
$headerImageSettings = loadHeaderImageSettings();
$headerPreviewImage = '../' . ltrim($headerImageSettings['image'], '/');
$headerLogoPreview = !empty($headerImageSettings['logo']) ? '../' . ltrim($headerImageSettings['logo'], '/') : '';

// Page title
$pageTitle = dcs_t('admin.themes.title');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Critical inline CSS for layout */
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 250px; flex-shrink: 0; background: #2a2a2a; }
        .admin-main { flex: 1; min-width: 0; overflow-x: hidden; }
        .admin-content { padding: 30px; max-width: 100%; overflow-x: hidden; }
        .card { max-width: 100%; overflow-x: auto; }
        
        .theme-section {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .preset-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin-top: 18px;
        }

        .preset-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            justify-content: space-between;
            padding: 16px;
        }

        .preset-card h3 {
            color: var(--text-primary);
            font-size: 18px;
            margin: 0 0 6px;
        }

        .preset-card p {
            color: var(--text-muted);
            font-size: 13px;
            margin: 0;
        }

        .preset-swatches {
            display: flex;
            gap: 6px;
            margin-top: 12px;
        }

        .preset-swatch {
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            height: 24px;
            width: 24px;
        }

        .preset-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .save-preset-row {
            align-items: flex-end;
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(220px, 420px) auto;
            margin-top: 16px;
        }

        .save-preset-row input[type="text"] {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-primary);
            padding: 10px 12px;
            width: 100%;
        }
        
        .color-inputs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
            max-width: 900px;
        }

        .color-fieldset {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin: 22px 0;
            padding: 16px;
        }

        .color-fieldset legend {
            color: var(--accent-primary);
            font-weight: 700;
            padding: 0 8px;
        }
        
        .color-input-group {
            display: flex;
            align-items: center;
            background: var(--bg-tertiary);
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }
        
        .color-input-group:hover {
            border-color: var(--accent-primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .color-input-group label {
            flex: 1;
            font-size: 0.95em;
            font-weight: 500;
            cursor: pointer;
        }
        
        .color-input-group input[type="color"] {
            width: 60px;
            height: 40px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            cursor: pointer;
            padding: 2px;
            background: var(--bg-secondary);
            transition: all 0.2s ease;
        }
        
        .color-input-group input[type="color"]:hover {
            border-color: var(--accent-primary);
            transform: scale(1.05);
        }
        
        .color-input-group input[type="color"]::-webkit-color-swatch {
            border-radius: 4px;
            border: none;
        }
        
        .color-input-group input[type="color"]::-moz-color-swatch {
            border-radius: 4px;
            border: none;
        }
        
        @media (max-width: 600px) {
            .color-inputs {
                grid-template-columns: 1fr;
            }
            .save-preset-row {
                grid-template-columns: 1fr;
            }
        }
        
        .upload-section {
            margin-top: 20px;
        }

        .header-image-preview {
            align-items: flex-end;
            aspect-ratio: 24 / 5;
            background-color: #111;
            background-repeat: no-repeat;
            background-size: cover;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            margin: 20px 0;
            max-width: 960px;
            min-height: 150px;
            overflow: hidden;
            padding: 16px;
        }

        .header-image-preview span {
            background: rgba(0, 0, 0, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 6px;
            color: #fff;
            font-weight: 700;
            padding: 8px 12px;
        }

        .header-logo-preview {
            align-items: center;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            gap: 16px;
            margin: 20px 0;
            max-width: 720px;
            min-height: 108px;
            padding: 16px;
        }

        .header-logo-preview img {
            height: var(--preview_logo_height, 72px);
            max-height: 96px;
            max-width: 320px;
            object-fit: contain;
            width: auto;
        }

        .header-logo-placeholder {
            color: var(--text-muted);
            font-weight: 600;
        }

        .header-position-controls {
            display: grid;
            gap: 16px;
            margin-top: 20px;
            max-width: 720px;
        }

        .header-position-controls label {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: grid;
            font-weight: 600;
            gap: 8px;
            padding: 14px 16px;
        }

        .header-position-controls input[type="range"] {
            width: 100%;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-button {
            display: inline-block;
            padding: 10px 20px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-input-button:hover {
            background: var(--bg-primary);
        }
        
        .backup-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .backup-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin-bottom: 5px;
            background: var(--bg-tertiary);
            border-radius: 4px;
        }
        
        .backup-info {
            flex: 1;
        }
        
        .preview-frame {
            width: 100%;
            height: 500px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: #fff;
        }
        
        .theme-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .theme-tab {
            padding: 10px 20px;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .theme-tab.active {
            color: var(--text-primary);
            border-bottom-color: var(--accent-primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Menu Configuration Styles */
        .menu-items {
            margin-top: 20px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            margin-bottom: 10px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .menu-item.dragging {
            opacity: 0.5;
        }
        
        .menu-item.drag-over {
            border-color: var(--accent-primary);
            border-style: dashed;
        }
        
        .menu-item-handle {
            cursor: grab;
            font-size: 20px;
            color: var(--text-muted);
            user-select: none;
        }
        
        .menu-item-handle:active {
            cursor: grabbing;
        }
        
        .menu-item-fields {
            display: flex;
            gap: 15px;
            flex: 1;
            align-items: center;
        }
        
        .menu-item-fields input[type="text"] {
            flex: 1;
            padding: 8px 12px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-primary);
        }
        
        .menu-item-fields input[type="text"]:focus {
            border-color: var(--accent-primary);
            outline: none;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php include __DIR__ . '/nav.php'; ?>
        
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <h1><?= $pageTitle ?></h1>
                <div class="admin-user-menu">
                    <div class="admin-user-info">
                        <div class="admin-username"><?= e($currentAdmin['username']) ?></div>
                        <div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div>
                    </div>
                    <a href="logout.php" class="btn btn-secondary btn-small"><?= e(dcs_t('admin.common.logout')) ?></a>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content">
                <div class="card">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <!-- Theme Preview Section -->
                <div class="theme-section">
                    <h2><?= e(dcs_t('admin.themes.preview')) ?></h2>
                    <p><?= e(dcs_t('admin.themes.preview_help')) ?></p>
                    
                    <?php
                    // Build initial preview URL with current colors
                    $previewParams = [
                        'preview' => '1',
                    ];
                    foreach ($customColors as $colorKey => $colorValue) {
                        $previewParams[$colorKey] = substr($colorValue, 1);
                    }
                    $previewParams['header_title_gradient_enabled'] = !empty($themeOptions['header_title_gradient_enabled']) ? '1' : '0';
                    $previewParams['page_background_gradient_enabled'] = !empty($themeOptions['page_background_gradient_enabled']) ? '1' : '0';
                    // Build URL to parent directory
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                    $host = $_SERVER['HTTP_HOST'];
                    $currentPath = dirname($_SERVER['SCRIPT_NAME']);
                    $parentPath = dirname($currentPath);
                    $previewUrl = $protocol . $host . ($parentPath === '/' ? '' : $parentPath) . '/index.php?' . http_build_query($previewParams);
                    ?>
                    <iframe src="<?= $previewUrl ?>" class="preview-frame" id="preview-frame"></iframe>
                    
                    <div style="margin-top: 10px;">
                        <span id="preview-status" style="color: var(--text-muted); font-size: 0.9em;"></span>
                    </div>
                </div>
                
                <!-- Theme Tabs -->
                <div class="theme-tabs">
                    <button class="theme-tab active" onclick="switchTab('simple')"><?= e(dcs_t('admin.themes.simple_customization')) ?></button>
                    <button class="theme-tab" onclick="switchTab('presets')"><?= e(dcs_t('admin.themes.theme_presets')) ?></button>
                    <button class="theme-tab" onclick="switchTab('header-image')"><?= e(dcs_t('admin.themes.header_image')) ?></button>
                    <button class="theme-tab" onclick="switchTab('menu')"><?= e(dcs_t('admin.themes.menu_configuration')) ?></button>
                    <button class="theme-tab" onclick="switchTab('charts')"><?= e(dcs_t('admin.themes.chart_colours')) ?></button>
                    <?php if ($isAirBoss): ?>
                    <button class="theme-tab" onclick="switchTab('advanced')"><?= e(dcs_t('admin.themes.advanced_css_upload')) ?></button>
                    <button class="theme-tab" onclick="switchTab('backups')"><?= e(dcs_t('admin.themes.backup_restore')) ?></button>
                    <?php endif; ?>
                </div>
                
                <!-- Simple Customization Tab -->
                <div id="simple-tab" class="tab-content active">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.colour_customization')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.colour_help')) ?></p>
                        <p style="font-size: 0.9em; color: var(--text-muted); margin-top: 10px;">
                            <?= e(dcs_t('admin.themes.colour_tip')) ?>
                        </p>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="update_colors">
                            
                        <?php foreach (getThemeColorGroups() as $groupName => $fields): ?>
                            <fieldset class="color-fieldset">
                                <legend><?= htmlspecialchars($groupName) ?></legend>
                                <?php if ($groupName === dcs_t('admin.themes.group_page_text')): ?>
                                    <div class="color-input-group" style="margin-bottom: 14px;">
                                        <label for="page_background_gradient_enabled"><?= e(dcs_t('admin.themes.page_background_gradient')) ?>:</label>
                                        <input type="checkbox" id="page_background_gradient_enabled" name="page_background_gradient_enabled"
                                               <?= !empty($themeOptions['page_background_gradient_enabled']) ? 'checked' : '' ?>>
                                    </div>
                                <?php endif; ?>
                                <?php if ($groupName === dcs_t('admin.themes.group_header_nav')): ?>
                                    <div class="color-input-group" style="margin-bottom: 14px;">
                                        <label for="header_title_gradient_enabled"><?= e(dcs_t('admin.themes.header_soft_gradient')) ?>:</label>
                                        <input type="checkbox" id="header_title_gradient_enabled" name="header_title_gradient_enabled"
                                               <?= !empty($themeOptions['header_title_gradient_enabled']) ? 'checked' : '' ?>>
                                    </div>
                                <?php endif; ?>
                                <div class="color-inputs">
                                        <?php foreach ($fields as $fieldKey => $label): ?>
                                            <div class="color-input-group">
                                                <label for="<?= htmlspecialchars($fieldKey) ?>"><?= htmlspecialchars($label) ?>:</label>
                                                <input type="color" id="<?= htmlspecialchars($fieldKey) ?>" name="<?= htmlspecialchars($fieldKey) ?>" 
                                                       value="<?= htmlspecialchars($customColors[$fieldKey]) ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </fieldset>
                            <?php endforeach; ?>
                            
                            <div style="margin-top: 20px; display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.themes.update_colours')) ?></button>
                                <button type="button" class="btn btn-secondary" onclick="restoreDefaultColors()"><?= e(dcs_t('admin.themes.restore_defaults')) ?></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="presets-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.theme_presets')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.presets_help')) ?></p>

                        <div class="preset-grid">
                            <?php foreach ($builtInThemePresets as $presetId => $preset): ?>
                                <?php $presetColors = array_merge(getDefaultThemeColors(), $preset['colors'] ?? []); ?>
                                <div class="preset-card">
                                    <div>
                                        <h3><?= htmlspecialchars($preset['name']) ?></h3>
                                        <p><?= htmlspecialchars($preset['description']) ?></p>
                                        <div class="preset-swatches" aria-hidden="true">
                                            <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['background_color']) ?>"></span>
                                            <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['card_color']) ?>"></span>
                                            <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['accent_color']) ?>"></span>
                                            <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['heading_color']) ?>"></span>
                                            <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['link_color']) ?>"></span>
                                        </div>
                                    </div>
                                    <form method="POST" action="" class="preset-actions">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="action" value="apply_theme_preset">
                                        <input type="hidden" name="preset_type" value="built_in">
                                        <input type="hidden" name="preset_id" value="<?= htmlspecialchars($presetId) ?>">
                                        <button type="submit" class="btn btn-primary btn-small"><?= e(dcs_t('admin.themes.apply_preset')) ?></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.custom_presets')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.custom_presets_help')) ?></p>

                        <form method="POST" action="" class="save-preset-row">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="save_theme_preset">
                            <div>
                                <label for="preset_name"><?= e(dcs_t('admin.themes.preset_name')) ?></label>
                                <input type="text" id="preset_name" name="preset_name" maxlength="60" placeholder="<?= e(dcs_t('admin.themes.preset_placeholder')) ?>" required>
                            </div>
                            <button type="submit" class="btn btn-secondary"><?= e(dcs_t('admin.themes.save_current_theme')) ?></button>
                        </form>

                        <?php if (!empty($customThemePresets)): ?>
                            <div class="preset-grid">
                                <?php foreach ($customThemePresets as $presetIndex => $preset): ?>
                                    <?php $presetColors = array_merge(getDefaultThemeColors(), $preset['colors'] ?? []); ?>
                                    <div class="preset-card">
                                        <div>
                                            <h3><?= htmlspecialchars($preset['name'] ?? 'Custom Preset') ?></h3>
                                            <p><?= htmlspecialchars($preset['description'] ?? 'Saved custom squadron theme') ?></p>
                                            <?php if (!empty($preset['created_at'])): ?>
                                                <p>Saved <?= htmlspecialchars(date('Y-m-d H:i', strtotime($preset['created_at']))) ?></p>
                                            <?php endif; ?>
                                            <div class="preset-swatches" aria-hidden="true">
                                                <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['background_color']) ?>"></span>
                                                <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['card_color']) ?>"></span>
                                                <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['accent_color']) ?>"></span>
                                                <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['heading_color']) ?>"></span>
                                                <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['link_color']) ?>"></span>
                                            </div>
                                        </div>
                                        <div class="preset-actions">
                                            <form method="POST" action="">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="action" value="apply_theme_preset">
                                                <input type="hidden" name="preset_type" value="custom">
                                                <input type="hidden" name="preset_id" value="<?= (int)$presetIndex ?>">
                                                <button type="submit" class="btn btn-primary btn-small"><?= e(dcs_t('admin.themes.apply')) ?></button>
                                            </form>
                                            <form method="POST" action="" onsubmit='return confirm(<?= json_encode(dcs_t('admin.themes.confirm_delete_preset')) ?>);'>
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="action" value="delete_theme_preset">
                                                <input type="hidden" name="preset_id" value="<?= (int)$presetIndex ?>">
                                                <button type="submit" class="btn btn-danger btn-small"><?= e(dcs_t('admin.themes.delete')) ?></button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted" style="margin-top: 16px;">No custom presets saved yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="header-image-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.header_image')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.header_image_help')) ?></p>
                        <p style="font-size: 0.9em; color: var(--text-muted); margin-top: 10px;">
                            Best fit: 2400 x 500 pixels or wider, JPG/PNG/WebP, under 5MB.
                        </p>

                        <div class="header-image-preview"
                             style="background-image: url('<?= htmlspecialchars($headerPreviewImage) ?>'); background-position: <?= (int)$headerImageSettings['position_x'] ?>% <?= (int)$headerImageSettings['position_y'] ?>%;">
                            <span><?= e(dcs_t('admin.themes.current_header_framing')) ?></span>
                        </div>

                        <form method="POST" action="" enctype="multipart/form-data" class="upload-section">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="update_header_image">

                            <div class="file-input-wrapper">
                                <input type="file" name="header_image" id="header_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <label for="header_image" class="file-input-button"><?= e(dcs_t('admin.themes.choose_header_image')) ?></label>
                            </div>
                            <span id="header-image-file-name" style="margin-left: 10px;">No new image selected</span>

                            <div class="color-input-group" style="margin-top: 18px; max-width: 420px;">
                                <label for="use_default_header_image"><?= e(dcs_t('admin.themes.use_default_header_image')) ?>:</label>
                                <input type="checkbox" id="use_default_header_image" name="use_default_header_image">
                            </div>

                            <div class="header-position-controls">
                                <label for="position_x">
                                    <?= e(dcs_t('admin.themes.horizontal_position')) ?>
                                    <input type="range" id="position_x" name="position_x" min="0" max="100" value="<?= (int)$headerImageSettings['position_x'] ?>">
                                </label>
                                <label for="position_y">
                                    <?= e(dcs_t('admin.themes.vertical_position')) ?>
                                    <input type="range" id="position_y" name="position_y" min="0" max="100" value="<?= (int)$headerImageSettings['position_y'] ?>">
                                </label>
                            </div>

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.header_branding')) ?></legend>
                                <p style="font-size: 0.9em; color: var(--text-muted); margin-top: 0;">
                                    Recommended logo size: transparent PNG/WebP around 360 x 96 pixels. Keep it under 2MB so the header stays the same height.
                                </p>

                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="branding_mode"><?= e(dcs_t('admin.themes.header_branding')) ?>:</label>
                                        <select id="branding_mode" name="branding_mode" class="form-control">
                                            <option value="text" <?= $headerImageSettings['branding_mode'] === 'text' ? 'selected' : '' ?>><?= e(dcs_t('admin.themes.text_only')) ?></option>
                                            <option value="both" <?= $headerImageSettings['branding_mode'] === 'both' ? 'selected' : '' ?>><?= e(dcs_t('admin.themes.logo_and_text')) ?></option>
                                            <option value="logo" <?= $headerImageSettings['branding_mode'] === 'logo' ? 'selected' : '' ?>><?= e(dcs_t('admin.themes.logo_only')) ?></option>
                                        </select>
                                    </div>

                                    <div class="color-input-group">
                                        <label for="logo_height"><?= e(dcs_t('admin.themes.logo_height')) ?>:</label>
                                        <input type="range" id="logo_height" name="logo_height" min="32" max="96" value="<?= (int)$headerImageSettings['logo_height'] ?>">
                                    </div>
                                </div>

                                <div class="header-logo-preview" style="--preview_logo_height: <?= (int)$headerImageSettings['logo_height'] ?>px;">
                                    <?php if ($headerLogoPreview): ?>
                                        <img src="<?= htmlspecialchars($headerLogoPreview) ?>" alt="Header logo preview">
                                    <?php else: ?>
                                        <span class="header-logo-placeholder">No logo uploaded</span>
                                    <?php endif; ?>
                                </div>

                                <div class="file-input-wrapper">
                                    <input type="file" name="header_logo" id="header_logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                    <label for="header_logo" class="file-input-button"><?= e(dcs_t('admin.themes.choose_header_logo')) ?></label>
                                </div>
                                <span id="header-logo-file-name" style="margin-left: 10px;">No new logo selected</span>

                                <div class="color-input-group" style="margin-top: 18px; max-width: 420px;">
                                    <label for="remove_header_logo"><?= e(dcs_t('admin.themes.remove_header_logo')) ?>:</label>
                                    <input type="checkbox" id="remove_header_logo" name="remove_header_logo">
                                </div>
                            </fieldset>

                            <button type="submit" class="btn btn-primary" style="margin-top: 20px;"><?= e(dcs_t('admin.themes.update_header_image')) ?></button>
                        </form>
                    </div>
                </div>
                
                <!-- Chart Colours Tab -->
                <div id="charts-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.chart_colours')) ?></h2>
                        <p>Customize the colours used by the leaderboard and homepage charts.</p>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="update_chart_colors">
                            
                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.leaderboard_chart')) ?></legend>
                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="chart_primary_color" title="<?= e(dcs_t('admin.themes.chart_primary_title')) ?>"><?= e(dcs_t('admin.themes.chart_primary')) ?>:</label>
                                        <input type="color" id="chart_primary_color" name="chart_primary_color" 
                                               value="<?= htmlspecialchars($chartColors['chart_primary_color']) ?>">
                                    </div>
                                    
                                    <div class="color-input-group">
                                        <label for="chart_secondary_color" title="<?= e(dcs_t('admin.themes.chart_secondary_title')) ?>"><?= e(dcs_t('admin.themes.chart_secondary')) ?>:</label>
                                        <input type="color" id="chart_secondary_color" name="chart_secondary_color" 
                                               value="<?= htmlspecialchars($chartColors['chart_secondary_color']) ?>">
                                    </div>
                                    
                                    <div class="color-input-group">
                                        <label for="chart_grid_color" title="<?= e(dcs_t('admin.themes.grid_lines_title')) ?>"><?= e(dcs_t('admin.themes.grid_lines')) ?>:</label>
                                        <input type="color" id="chart_grid_color" name="chart_grid_color" 
                                               value="<?= htmlspecialchars($chartColors['chart_grid_color']) ?>">
                                    </div>
                                    
                                    <div class="color-input-group">
                                        <label for="chart_text_color" title="<?= e(dcs_t('admin.themes.chart_text_title')) ?>"><?= e(dcs_t('admin.themes.chart_text')) ?>:</label>
                                        <input type="color" id="chart_text_color" name="chart_text_color" 
                                               value="<?= htmlspecialchars($chartColors['chart_text_color']) ?>">
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.home_top_pilots')) ?></legend>
                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="home_top_pilots_color" title="<?= e(dcs_t('admin.themes.bars_title')) ?>"><?= e(dcs_t('admin.themes.bars')) ?>:</label>
                                        <input type="color" id="home_top_pilots_color" name="home_top_pilots_color" 
                                               value="<?= htmlspecialchars($chartColors['home_top_pilots_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_top_pilots_grid_color" title="<?= e(dcs_t('admin.themes.grid_lines_title')) ?>"><?= e(dcs_t('admin.themes.grid_lines')) ?>:</label>
                                        <input type="color" id="home_top_pilots_grid_color" name="home_top_pilots_grid_color" 
                                               value="<?= htmlspecialchars($chartColors['home_top_pilots_grid_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_top_pilots_text_color" title="<?= e(dcs_t('admin.themes.chart_text_title')) ?>"><?= e(dcs_t('admin.themes.text')) ?>:</label>
                                        <input type="color" id="home_top_pilots_text_color" name="home_top_pilots_text_color" 
                                               value="<?= htmlspecialchars($chartColors['home_top_pilots_text_color']) ?>">
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.home_combat_stats')) ?></legend>
                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="home_combat_kills_color" title="<?= e(dcs_t('admin.themes.combat_kills_title')) ?>"><?= e(dcs_t('home.kills')) ?>:</label>
                                        <input type="color" id="home_combat_kills_color" name="home_combat_kills_color" 
                                               value="<?= htmlspecialchars($chartColors['home_combat_kills_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_combat_deaths_color" title="<?= e(dcs_t('admin.themes.combat_deaths_title')) ?>"><?= e(dcs_t('home.deaths')) ?>:</label>
                                        <input type="color" id="home_combat_deaths_color" name="home_combat_deaths_color" 
                                               value="<?= htmlspecialchars($chartColors['home_combat_deaths_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_combat_text_color" title="<?= e(dcs_t('admin.themes.chart_text_title')) ?>"><?= e(dcs_t('admin.themes.text')) ?>:</label>
                                        <input type="color" id="home_combat_text_color" name="home_combat_text_color" 
                                               value="<?= htmlspecialchars($chartColors['home_combat_text_color']) ?>">
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.home_top_squadrons')) ?></legend>
                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="home_squadrons_color" title="<?= e(dcs_t('admin.themes.bars_title')) ?>"><?= e(dcs_t('admin.themes.bars')) ?>:</label>
                                        <input type="color" id="home_squadrons_color" name="home_squadrons_color" 
                                               value="<?= htmlspecialchars($chartColors['home_squadrons_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_squadrons_grid_color" title="<?= e(dcs_t('admin.themes.grid_lines_title')) ?>"><?= e(dcs_t('admin.themes.grid_lines')) ?>:</label>
                                        <input type="color" id="home_squadrons_grid_color" name="home_squadrons_grid_color" 
                                               value="<?= htmlspecialchars($chartColors['home_squadrons_grid_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_squadrons_text_color" title="<?= e(dcs_t('admin.themes.chart_text_title')) ?>"><?= e(dcs_t('admin.themes.text')) ?>:</label>
                                        <input type="color" id="home_squadrons_text_color" name="home_squadrons_text_color" 
                                               value="<?= htmlspecialchars($chartColors['home_squadrons_text_color']) ?>">
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.home_player_activity')) ?></legend>
                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="home_activity_color" title="<?= e(dcs_t('admin.themes.line_fill_title')) ?>"><?= e(dcs_t('admin.themes.line_and_fill')) ?>:</label>
                                        <input type="color" id="home_activity_color" name="home_activity_color" 
                                               value="<?= htmlspecialchars($chartColors['home_activity_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_activity_grid_color" title="<?= e(dcs_t('admin.themes.grid_lines_title')) ?>"><?= e(dcs_t('admin.themes.grid_lines')) ?>:</label>
                                        <input type="color" id="home_activity_grid_color" name="home_activity_grid_color" 
                                               value="<?= htmlspecialchars($chartColors['home_activity_grid_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_activity_text_color" title="<?= e(dcs_t('admin.themes.chart_text_title')) ?>"><?= e(dcs_t('admin.themes.text')) ?>:</label>
                                        <input type="color" id="home_activity_text_color" name="home_activity_text_color" 
                                               value="<?= htmlspecialchars($chartColors['home_activity_text_color']) ?>">
                                    </div>
                                </div>
                            </fieldset>
                            
                            <div style="margin-top: 20px; display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.themes.update_chart_colours')) ?></button>
                                <button type="button" class="btn btn-secondary" onclick="restoreDefaultChartColors()"><?= e(dcs_t('admin.themes.restore_defaults')) ?></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Menu Configuration Tab -->
                <div id="menu-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.navigation_menu_configuration')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.navigation_menu_help')) ?></p>
                        
                        <form method="POST" action="" id="menu-form">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="update_menu">
                            
                            <div class="menu-items" id="menu-items">
                                <?php foreach ($menuItems as $index => $item): ?>
                                <div class="menu-item" data-index="<?= $index ?>">
                                    <div class="menu-item-handle">☰</div>
                                    <input type="hidden" name="menu_order[]" value="<?= $index ?>">
                                    <input type="hidden" name="menu_types[<?= $index ?>]" value="<?= htmlspecialchars($item['type'] ?? 'page') ?>">
                                    <div class="menu-item-fields">
                                        <input type="text" name="menu_names[<?= $index ?>]" value="<?= htmlspecialchars($item['name']) ?>" placeholder="<?= e(dcs_t('admin.themes.menu_name')) ?>" required>
                                        <?php if (in_array($item['type'] ?? 'page', ['discord', 'squadron_homepage'])): ?>
                                            <input type="text" name="menu_urls[<?= $index ?>]" value="<?= htmlspecialchars($item['url']) ?>" placeholder="URL" required title="External URL">
                                        <?php else: ?>
                                            <input type="text" name="menu_urls[<?= $index ?>]" value="<?= htmlspecialchars($item['url']) ?>" placeholder="URL" required readonly>
                                        <?php endif; ?>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="menu_enabled[<?= $index ?>]" <?= $item['enabled'] ? 'checked' : '' ?>>
                                            <span><?= e(dcs_t('admin.status.enabled')) ?></span>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-top: 20px;"><?= e(dcs_t('admin.themes.save_menu_configuration')) ?></button>
                            <button type="button" class="btn btn-secondary" onclick="resetMenu()" style="margin-top: 20px;"><?= e(dcs_t('admin.themes.reset_to_default')) ?></button>
                        </form>
                    </div>
                </div>
                
                <?php if ($isAirBoss): ?>
                <!-- Advanced CSS Upload Tab -->
                <div id="advanced-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.upload_custom_css')) ?></h2>
                        <div class="alert alert-warning">
                            <strong><?= e(dcs_t('admin.themes.warning')) ?>:</strong> <?= e(dcs_t('admin.themes.css_warning')) ?>
                        </div>
                        
                        <form method="POST" action="" enctype="multipart/form-data" class="upload-section">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="upload_css">
                            
                            <div class="file-input-wrapper">
                                <input type="file" name="css_file" id="css_file" accept=".css">
                                <label for="css_file" class="file-input-button"><?= e(dcs_t('admin.themes.choose_css_file')) ?></label>
                            </div>
                            <span id="file-name" style="margin-left: 10px;"><?= e(dcs_t('admin.themes.no_file_selected')) ?></span>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.themes.upload_css')) ?></button>
                            </div>
                        </form>
                        
                        <div style="margin-top: 30px;">
                            <h3><?= e(dcs_t('admin.themes.css_guidelines')) ?></h3>
                            <ul>
                                <li><?= e(dcs_t('admin.themes.css_guideline_size')) ?></li>
                                <li><?= e(dcs_t('admin.themes.css_guideline_variables')) ?></li>
                                <li><?= e(dcs_t('admin.themes.css_guideline_test')) ?></li>
                                <li><?= e(dcs_t('admin.themes.css_guideline_mobile')) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Backup & Restore Tab -->
                <div id="backups-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.theme_settings_backup')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.theme_settings_backup_help')) ?></p>

                        <div class="backup-list">
                            <div class="backup-item">
                                <div class="backup-info">
                                    <strong><?= e(dcs_t('admin.themes.current_theme_settings')) ?></strong><br>
                                    <small><?= e(dcs_t('admin.themes.current_theme_settings_help')) ?></small>
                                </div>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                    <input type="hidden" name="action" value="export_theme_settings">
                                    <button type="submit" class="btn btn-primary btn-sm"><?= e(dcs_t('admin.themes.download_backup')) ?></button>
                                </form>
                            </div>
                        </div>

                        <form method="POST" action="" enctype="multipart/form-data" class="upload-section">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="import_theme_settings">

                            <div class="file-input-wrapper">
                                <input type="file" name="theme_settings_file" id="theme_settings_file" accept=".json,application/json">
                                <label for="theme_settings_file" class="file-input-button"><?= e(dcs_t('admin.themes.choose_settings_backup')) ?></label>
                            </div>
                            <span id="theme-settings-file-name" style="margin-left: 10px;"><?= e(dcs_t('admin.themes.no_file_selected')) ?></span>

                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-primary"
                                        onclick='return confirm(<?= json_encode(dcs_t('admin.themes.confirm_restore_settings')) ?>)'>
                                    <?= e(dcs_t('admin.themes.upload_and_restore')) ?>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.advanced_css_backups')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.advanced_css_backups_help')) ?></p>
                        
                        <?php if (empty($backups)): ?>
                            <p><?= e(dcs_t('admin.update.no_backups')) ?></p>
                        <?php else: ?>
                            <div class="backup-list">
                                <?php foreach ($backups as $backup): ?>
                                    <div class="backup-item">
                                        <div class="backup-info">
                                            <strong><?= htmlspecialchars($backup['filename']) ?></strong><br>
                                            <small>
                                                <?= e(dcs_t('admin.admins.created')) ?>: <?= date('Y-m-d H:i:s', $backup['date']) ?> | 
                                                <?= e(dcs_t('admin.update.size')) ?>: <?= number_format($backup['size'] / 1024, 2) ?> KB
                                            </small>
                                        </div>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="action" value="restore_backup">
                                            <input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['filename']) ?>">
                                            <button type="submit" class="btn btn-sm" 
                                                    onclick='return confirm(<?= json_encode(dcs_t('admin.update.confirm_restore')) ?>)'>
                                                <?= e(dcs_t('admin.update.restore')) ?>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        const themeText = <?= json_encode([
            'noFileSelected' => dcs_t('admin.themes.no_file_selected'),
            'noNewImageSelected' => dcs_t('admin.themes.no_new_image_selected'),
            'defaultImageSelected' => dcs_t('admin.themes.default_image_selected'),
            'noNewLogoSelected' => dcs_t('admin.themes.no_new_logo_selected'),
            'logoWillBeRemoved' => dcs_t('admin.themes.logo_will_be_removed'),
            'updatingPreview' => dcs_t('admin.themes.updating_preview'),
            'previewUpdated' => dcs_t('admin.themes.preview_updated'),
            'resetMenuConfirm' => dcs_t('admin.themes.reset_menu_confirm'),
            'colorsRestored' => dcs_t('admin.themes.colors_restored')
        ], JSON_UNESCAPED_UNICODE) ?>;

        // Tab switching
        function switchTab(tabName) {
            // Remove active class from all tabs and contents
            document.querySelectorAll('.theme-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to selected tab and content
            event.target.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        }
        
        // File input handling
        const cssFileInput = document.getElementById('css_file');
        if (cssFileInput) {
            cssFileInput.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || themeText.noFileSelected;
                document.getElementById('file-name').textContent = fileName;
            });
        }

        const headerImageInput = document.getElementById('header_image');
        const headerImagePreview = document.querySelector('.header-image-preview');
        const positionX = document.getElementById('position_x');
        const positionY = document.getElementById('position_y');
        const useDefaultHeaderImage = document.getElementById('use_default_header_image');
        const defaultHeaderImageUrl = '../dcs-header-image.jpg';
        const headerLogoInput = document.getElementById('header_logo');
        const headerLogoPreview = document.querySelector('.header-logo-preview');
        const logoHeight = document.getElementById('logo_height');
        const removeHeaderLogo = document.getElementById('remove_header_logo');

        function updateHeaderImagePreview() {
            if (!headerImagePreview || !positionX || !positionY) return;
            headerImagePreview.style.backgroundPosition = `${positionX.value}% ${positionY.value}%`;
        }

        if (headerImageInput) {
            headerImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                document.getElementById('header-image-file-name').textContent = file?.name || themeText.noNewImageSelected;
                if (file && headerImagePreview) {
                    if (useDefaultHeaderImage) useDefaultHeaderImage.checked = false;
                    headerImagePreview.style.backgroundImage = `url('${URL.createObjectURL(file)}')`;
                    updateHeaderImagePreview();
                }
            });
        }

        const themeSettingsInput = document.getElementById('theme_settings_file');
        if (themeSettingsInput) {
            themeSettingsInput.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || themeText.noFileSelected;
                document.getElementById('theme-settings-file-name').textContent = fileName;
            });
        }

        if (useDefaultHeaderImage) {
            useDefaultHeaderImage.addEventListener('change', function() {
                if (this.checked && headerImagePreview) {
                    headerImagePreview.style.backgroundImage = `url('${defaultHeaderImageUrl}')`;
                    document.getElementById('header-image-file-name').textContent = themeText.defaultImageSelected;
                    if (headerImageInput) headerImageInput.value = '';
                    updateHeaderImagePreview();
                }
            });
        }

        [positionX, positionY].forEach(input => {
            if (input) {
                input.addEventListener('input', updateHeaderImagePreview);
            }
        });

        if (headerLogoInput) {
            headerLogoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                document.getElementById('header-logo-file-name').textContent = file?.name || themeText.noNewLogoSelected;
                if (file && headerLogoPreview) {
                    if (removeHeaderLogo) removeHeaderLogo.checked = false;
                    headerLogoPreview.innerHTML = `<img src="${URL.createObjectURL(file)}" alt="Header logo preview">`;
                }
            });
        }

        if (logoHeight && headerLogoPreview) {
            logoHeight.addEventListener('input', function() {
                headerLogoPreview.style.setProperty('--preview_logo_height', `${this.value}px`);
            });
        }

        if (removeHeaderLogo && headerLogoPreview) {
            removeHeaderLogo.addEventListener('change', function() {
                if (this.checked) {
                    headerLogoPreview.innerHTML = `<span class="header-logo-placeholder">${themeText.logoWillBeRemoved}</span>`;
                    document.getElementById('header-logo-file-name').textContent = themeText.noNewLogoSelected;
                    if (headerLogoInput) headerLogoInput.value = '';
                }
            });
        }
        
        
        // Debounce function to prevent too many updates
        let updateTimeout;
        function debounce(func, wait) {
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(updateTimeout);
                    func(...args);
                };
                clearTimeout(updateTimeout);
                updateTimeout = setTimeout(later, wait);
            };
        }
        
        // Live color preview
        function updatePreviewColors() {
            const iframe = document.getElementById('preview-frame');
            
            // Build URL with preview parameters
            const params = new URLSearchParams();
            params.set('preview', '1');
            document.querySelectorAll('#simple-tab input[type="color"]').forEach(input => {
                params.set(input.id, input.value.replace('#', ''));
            });
            const titleGradient = document.getElementById('header_title_gradient_enabled');
            if (titleGradient) {
                params.set('header_title_gradient_enabled', titleGradient.checked ? '1' : '0');
            }
            const pageGradient = document.getElementById('page_background_gradient_enabled');
            if (pageGradient) {
                params.set('page_background_gradient_enabled', pageGradient.checked ? '1' : '0');
            }
            
            // Update iframe source with preview parameters
            // Build URL to parent directory  
            const protocol = window.location.protocol;
            const host = window.location.host;
            const currentPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
            const parentPath = currentPath.substring(0, currentPath.lastIndexOf('/'));
            const baseUrl = protocol + '//' + host + parentPath + '/index.php';
            iframe.src = baseUrl + '?' + params.toString();
        }
        
        // Debounced version of updatePreviewColors
        const debouncedUpdate = debounce(updatePreviewColors, 500);
        
        // Add event listeners for real-time updates
        document.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener('input', function() {
                // Show status message immediately
                const status = document.getElementById('preview-status');
                status.textContent = themeText.updatingPreview;
                status.style.color = '#ff9800';
                
                // Update preview with debounce
                debouncedUpdate();
            });
            
            input.addEventListener('change', function() {
                // Show completed message
                const status = document.getElementById('preview-status');
                status.textContent = themeText.previewUpdated;
                status.style.color = '#4CAF50';
                setTimeout(() => {
                    status.textContent = '';
                }, 2000);
            });
        });

        const titleGradientToggle = document.getElementById('header_title_gradient_enabled');
        if (titleGradientToggle) {
            titleGradientToggle.addEventListener('change', function() {
                updatePreviewColors();
                const status = document.getElementById('preview-status');
                status.textContent = themeText.previewUpdated;
                status.style.color = '#4CAF50';
                setTimeout(() => {
                    status.textContent = '';
                }, 2000);
            });
        }
        const pageGradientToggle = document.getElementById('page_background_gradient_enabled');
        if (pageGradientToggle) {
            pageGradientToggle.addEventListener('change', function() {
                updatePreviewColors();
                const status = document.getElementById('preview-status');
                status.textContent = themeText.previewUpdated;
                status.style.color = '#4CAF50';
                setTimeout(() => {
                    status.textContent = '';
                }, 2000);
            });
        }
        
        // Menu drag and drop functionality
        let draggedElement = null;
        
        function initMenuDragDrop() {
            const menuItems = document.querySelectorAll('.menu-item');
            
            menuItems.forEach(item => {
                item.draggable = true;
                
                item.addEventListener('dragstart', function(e) {
                    draggedElement = this;
                    this.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', this.innerHTML);
                });
                
                item.addEventListener('dragend', function() {
                    this.classList.remove('dragging');
                });
                
                item.addEventListener('dragover', function(e) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    }
                    e.dataTransfer.dropEffect = 'move';
                    this.classList.add('drag-over');
                    return false;
                });
                
                item.addEventListener('dragleave', function() {
                    this.classList.remove('drag-over');
                });
                
                item.addEventListener('drop', function(e) {
                    if (e.stopPropagation) {
                        e.stopPropagation();
                    }
                    
                    this.classList.remove('drag-over');
                    
                    if (draggedElement !== this) {
                        const container = document.getElementById('menu-items');
                        const allItems = Array.from(container.querySelectorAll('.menu-item'));
                        const draggedIndex = allItems.indexOf(draggedElement);
                        const targetIndex = allItems.indexOf(this);
                        
                        if (draggedIndex < targetIndex) {
                            this.parentNode.insertBefore(draggedElement, this.nextSibling);
                        } else {
                            this.parentNode.insertBefore(draggedElement, this);
                        }
                        
                        // Update order inputs
                        updateMenuOrder();
                    }
                    
                    return false;
                });
            });
        }
        
        function updateMenuOrder() {
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach((item, index) => {
                const orderInput = item.querySelector('input[name="menu_order[]"]');
                orderInput.value = index;
                
                // Update field names to match new order
                const nameInput = item.querySelector('input[name^="menu_names"]');
                const urlInput = item.querySelector('input[name^="menu_urls"]');
                const enabledInput = item.querySelector('input[name^="menu_enabled"]');
                const typeInput = item.querySelector('input[name^="menu_types"]');
                
                nameInput.name = `menu_names[${index}]`;
                urlInput.name = `menu_urls[${index}]`;
                enabledInput.name = `menu_enabled[${index}]`;
                typeInput.name = `menu_types[${index}]`;
            });
        }
        
        function resetMenu() {
            if (confirm(themeText.resetMenuConfirm)) {
                // Create a form to submit reset action
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="action" value="update_menu">
                    <?php 
                    // Rebuild default menu items for reset
                    $resetMenuItems = [
                        ['name' => 'Home', 'url' => 'index.php', 'enabled' => true, 'type' => 'page'],
                        ['name' => 'Leaderboard', 'url' => 'leaderboard.php', 'enabled' => true, 'type' => 'page'],
                        ['name' => 'Pilot Statistics', 'url' => 'pilot_statistics.php', 'enabled' => true, 'type' => 'page'],
                        ['name' => 'Pilot Credits', 'url' => 'pilot_credits.php', 'enabled' => true, 'type' => 'page'],
                        ['name' => 'Squadrons', 'url' => 'squadrons.php', 'enabled' => true, 'type' => 'page'],
                        ['name' => 'Servers', 'url' => 'servers.php', 'enabled' => true, 'type' => 'page']
                    ];
                    if (isFeatureEnabled('show_discord_link')) {
                        $resetMenuItems[] = ['name' => 'Discord', 'url' => getFeatureValue('discord_link_url', 'https://discord.gg/DNENf6pUNX'), 'enabled' => true, 'type' => 'discord'];
                    }
                    if (isFeatureEnabled('show_squadron_homepage') && !empty(getFeatureValue('squadron_homepage_url'))) {
                        $resetMenuItems[] = ['name' => getFeatureValue('squadron_homepage_text', 'Squadron'), 'url' => getFeatureValue('squadron_homepage_url'), 'enabled' => true, 'type' => 'squadron_homepage'];
                    }
                    foreach ($resetMenuItems as $index => $item): ?>
                    <input type="hidden" name="menu_order[]" value="<?= $index ?>">
                    <input type="hidden" name="menu_names[<?= $index ?>]" value="<?= htmlspecialchars($item['name']) ?>">
                    <input type="hidden" name="menu_urls[<?= $index ?>]" value="<?= htmlspecialchars($item['url']) ?>">
                    <input type="hidden" name="menu_types[<?= $index ?>]" value="<?= htmlspecialchars($item['type']) ?>">
                    <?php if ($item['enabled']): ?>
                    <input type="hidden" name="menu_enabled[<?= $index ?>]" value="1">
                    <?php endif; ?>
                    <?php endforeach; ?>
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Restore default colors
        function restoreDefaultColors() {
            const defaults = <?= json_encode(getDefaultThemeColors()) ?>;
            
            // Set the color inputs to default values
            for (const [id, value] of Object.entries(defaults)) {
                const input = document.getElementById(id);
                if (input) input.value = value;
            }
            const titleGradient = document.getElementById('header_title_gradient_enabled');
            if (titleGradient) titleGradient.checked = false;
            const pageGradient = document.getElementById('page_background_gradient_enabled');
            if (pageGradient) pageGradient.checked = false;
            
            // Update preview immediately
            updatePreviewColors();
            
            // Show status message
            const status = document.getElementById('preview-status');
            status.textContent = themeText.colorsRestored;
            status.style.color = '#2196F3';
            setTimeout(() => {
                status.textContent = '';
            }, 3000);
        }

        function restoreDefaultChartColors() {
            const defaults = <?= json_encode(getDefaultChartTheme()) ?>;

            for (const [id, value] of Object.entries(defaults)) {
                const input = document.getElementById(id);
                if (input) input.value = value;
            }
        }
        
        // Initialize drag and drop when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initMenuDragDrop();
            
            // Initialize preview with current colors
            updatePreviewColors();
        });
    </script>
</body>
</html>
