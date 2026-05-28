<?php
// Include path configuration
require_once __DIR__ . '/config_path.php';
require_once __DIR__ . '/api_config_helper.php';
require_once __DIR__ . '/site_metadata.php';
require_once __DIR__ . '/language.php';
require_once __DIR__ . '/site-config/demo_helpers.php';

// Load site configuration
$siteConfig = [];
$siteConfigFile = __DIR__ . '/site_config.json';
if (file_exists($siteConfigFile)) {
    $content = @file_get_contents($siteConfigFile);
    if ($content) {
        $siteConfig = json_decode($content, true) ?: [];
    }
}

$siteName = $siteConfig['site_name'] ?? 'DCS Statistics';
$siteMetadata = loadSiteMetadata();
$headerSettingsPath = __DIR__ . '/site-config/data/header_image.json';
$headerBranding = [
    'branding_mode' => 'text',
    'logo' => '',
    'logo_height' => 72
];

if (file_exists($headerSettingsPath)) {
    $savedHeaderSettings = json_decode(file_get_contents($headerSettingsPath), true);
    if (is_array($savedHeaderSettings)) {
        $headerBranding = array_merge($headerBranding, array_intersect_key($savedHeaderSettings, $headerBranding));
    }
}

$headerBranding['branding_mode'] = in_array($headerBranding['branding_mode'], ['text', 'logo', 'both'], true) ? $headerBranding['branding_mode'] : 'text';
$headerBranding['logo_height'] = max(32, min(96, (int)$headerBranding['logo_height']));
$headerLogoPath = ltrim((string)$headerBranding['logo'], '/');

if ($headerLogoPath === '' || !file_exists(__DIR__ . '/' . $headerLogoPath)) {
    $headerLogoPath = '';
    if ($headerBranding['branding_mode'] === 'logo') {
        $headerBranding['branding_mode'] = 'text';
    }
}

$showHeaderLogo = $headerLogoPath !== '' && in_array($headerBranding['branding_mode'], ['logo', 'both'], true);
$showHeaderText = in_array($headerBranding['branding_mode'], ['text', 'both'], true) || !$showHeaderLogo;

// Security headers for protection against common web vulnerabilities
header("X-Content-Type-Options: nosniff");
// Allow iframe embedding for theme preview, deny for everything else
if (isset($_GET['preview']) && $_GET['preview'] === '1') {
    header("X-Frame-Options: SAMEORIGIN");
} else {
    header("X-Frame-Options: DENY");
}
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Build dynamic CSP based on API configuration
$cspConnectSrc = "'self'";

// Load API configuration if available
$configResult = loadApiConfigWithFix();
if (!empty($configResult['config'])) {
    $config = $configResult['config'];
    if (!empty($config['api_base_url'])) {
        // Parse the API URL to add to CSP
        $parsedUrl = parse_url($config['api_base_url']);
        if ($parsedUrl) {
            $scheme = $parsedUrl['scheme'] ?? 'http';
            $host = $parsedUrl['host'] ?? '';
            $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
            
            if ($host) {
                // Add the specific API URL
                $cspConnectSrc .= " {$scheme}://{$host}{$port}";
                
                // Also add wildcard for subdomains
                $domain = preg_replace('/^[^.]+\./', '*.', $host);
                if ($domain !== $host) {
                    $cspConnectSrc .= " {$scheme}://{$domain}:*";
                }
            }
        }
    }
}

// Always allow localhost for development
$cspConnectSrc .= " http://localhost:* https://localhost:*";

// Build CSP header with frame-ancestors for preview mode
$frameAncestors = (isset($_GET['preview']) && $_GET['preview'] === '1') ? " frame-ancestors 'self';" : " frame-ancestors 'none';";
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src {$cspConnectSrc};" . $frameAncestors);

// Handle theme preview parameters
$previewColors = null;
if (isset($_GET['preview']) && $_GET['preview'] === '1') {
    $previewColorKeys = [
        'primary_color', 'secondary_color', 'background_color', 'background_gradient_color', 'surface_color', 'surface_dark_color',
        'card_color', 'card_alt_color', 'card_heading_color', 'card_text_color',
        'card_muted_text_color', 'text_color', 'muted_text_color', 'heading_color', 'link_color',
        'accent_color', 'accent_hover_color', 'border_color', 'nav_background_color',
        'nav_text_color', 'nav_hover_color', 'header_text_color', 'header_title_gradient_color',
        'header_subtitle_color',
        'footer_background_color', 'footer_text_color', 'success_color', 'warning_color',
        'danger_color', 'info_color', 'table_header_color', 'table_header_text_color',
        'table_row_color', 'table_text_color', 'table_player_name_color', 'table_hover_color'
    ];
    $previewColors = [];
    foreach ($previewColorKeys as $key) {
        $value = $_GET[$key] ?? null;
        $previewColors[$key] = (is_string($value) && preg_match('/^[0-9A-Fa-f]{6}$/', $value)) ? '#' . $value : null;
    }
}

// Maintenance mode check
$maintenanceFile = __DIR__ . '/site-config/data/maintenance.json';
if (file_exists($maintenanceFile)) {
    $maintenance = json_decode(file_get_contents($maintenanceFile), true);
    if (!empty($maintenance['enabled'])) {
        $allowed = $maintenance['ip_whitelist'] ?? [];
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!in_array($ip, $allowed)) {
            // Override redirect logic within maintenance.php
            if (!defined('MAINTENANCE_OVERRIDE')) {
                define('MAINTENANCE_OVERRIDE', true);
            }
            require __DIR__ . '/maintenance.php';
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(dcs_default_language(), ENT_QUOTES); ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php if (!empty($siteMetadata['description'])): ?>
  <meta name="description" content="<?php echo htmlspecialchars($siteMetadata['description'], ENT_QUOTES); ?>" />
  <?php endif; ?>
  <?php if (!empty($siteMetadata['keywords'])): ?>
  <meta name="keywords" content="<?php echo htmlspecialchars($siteMetadata['keywords'], ENT_QUOTES); ?>" />
  <?php endif; ?>
  <?php if (!empty($siteMetadata['block_search_engines'])): ?>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <?php else: ?>
  <meta name="robots" content="index,follow" />
  <?php endif; ?>
  <title><?php echo htmlspecialchars($siteName); ?> Dashboard</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('styles.php')); ?>" />
  <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('styles-mobile.css')); ?>" />
  <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('theme_overrides.css')); ?>" />
  <?php if (file_exists(__DIR__ . '/custom_theme.css')): ?>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('custom_theme.css')); ?>" />
  <?php endif; ?>
  <?php if (file_exists(__DIR__ . '/header_custom.css')): ?>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('header_custom.css')); ?>" />
  <?php endif; ?>
  <?php if ($previewColors): ?>
  <style>
    :root {
      <?php foreach ($previewColors as $var => $color): ?>
      <?php if ($color): ?>
      --<?php echo $var; ?>: <?php echo $color; ?> !important;
      <?php endif; ?>
      <?php endforeach; ?>
      <?php if (($_GET['header_title_gradient_enabled'] ?? '0') === '1'): ?>
      --header_title_gradient_enabled: 1 !important;
      --header_title_background: linear-gradient(135deg, var(--header_text_color) 0%, var(--header_title_gradient_color) 100%) !important;
      --header_title_fill: transparent !important;
      <?php else: ?>
      --header_title_gradient_enabled: 0 !important;
      --header_title_background: none !important;
      --header_title_fill: var(--header_text_color) !important;
      <?php endif; ?>
      <?php if (($_GET['page_background_gradient_enabled'] ?? '0') === '1'): ?>
      --page_background_gradient_enabled: 1 !important;
      --page_background_css: radial-gradient(circle at top left, color-mix(in srgb, var(--background_gradient_color) 36%, transparent) 0%, transparent 34%), linear-gradient(135deg, var(--background_color) 0%, var(--background_gradient_color) 100%) !important;
      <?php else: ?>
      --page_background_gradient_enabled: 0 !important;
      --page_background_css: var(--background_color) !important;
      <?php endif; ?>
    }
    body {
      background: var(--page_background_css) !important;
      background-color: var(--background_color) !important;
    }
  </style>
  <?php endif; ?>
  <script>
    // Path configuration for JavaScript
    window.DCS_CONFIG = <?php echo getJsConfig(); ?>;
    
    // XSS Protection function
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>
  <script src="<?php echo htmlspecialchars(assetUrl('js/api-client.js')); ?>"></script>
  <script src="<?php echo htmlspecialchars(assetUrl('mobile-enhancements.js')); ?>"></script>
</head>
<body>
  <?php $frontendDemoBannerShown = false; ?>
  <?php if (function_exists('isDemoMode') && isDemoMode()): ?>
  <?php $frontendDemoBannerShown = true; ?>
  <div class="demo-notice-bar" role="note">
    <strong>DEMO:</strong>
    Data provided by VFS-252 Sky Pirates.
    BO Demo available here:
    <a href="<?php echo htmlspecialchars(url('site-config/login.php')); ?>">Admin Login</a>
    <span>Username: <strong>Demo</strong></span>
    <span>Password: <strong>Demo123!</strong></span>
  </div>
  <?php endif; ?>
  <header class="main-header">
    <div class="header-background"></div>
    <div class="header-overlay"></div>
    <div class="header-container">
      <div class="header-brand">
        <?php if ($showHeaderLogo): ?>
        <img class="site-logo" src="<?php echo url($headerLogoPath); ?>" alt="<?php echo htmlspecialchars($siteName); ?> logo" style="--site_logo_height: <?php echo (int)$headerBranding['logo_height']; ?>px;" />
        <?php endif; ?>
        <div class="brand-text<?php echo $showHeaderText ? '' : ' is-hidden'; ?>">
          <?php if ($showHeaderText): ?>
          <h1 class="site-title"><?php echo htmlspecialchars($siteName); ?></h1>
          <p class="site-subtitle"><?php echo htmlspecialchars(dcs_t('site.subtitle')); ?></p>
          <?php endif; ?>
        </div>
      </div>
      <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="<?php echo htmlspecialchars(dcs_t('header.toggle_navigation')); ?>">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
      </button>
      <div class="header-actions">
        <div class="status-indicator">
          <span class="status-dot"></span>
          <span class="status-text"><?php echo htmlspecialchars(dcs_t('header.live_data')); ?></span>
        </div>
      </div>
    </div>
  </header>
  <script>
    (function() {
      const storageKey = 'dcs_stats_server_scope';

      function normaliseServerName(name) {
        return String(name || '').trim();
      }

      function getSavedScope() {
        try {
          return normaliseServerName(localStorage.getItem(storageKey) || '');
        } catch (error) {
          return '';
        }
      }

      function setSavedScope(value) {
        const serverName = normaliseServerName(value);
        try {
          if (serverName) {
            localStorage.setItem(storageKey, serverName);
          } else {
            localStorage.removeItem(storageKey);
          }
        } catch (error) {
          // Some locked-down browsers can block localStorage.
        }
        window.DCS_SELECTED_SERVER = serverName;
        window.dispatchEvent(new CustomEvent('dcs-server-scope-change', { detail: { server: serverName } }));
      }

      function isServerScopeEnabled() {
        return window.DCS_SERVER_SCOPE_ENABLED !== false;
      }

      function getServerScopeFeatureKey(serverName) {
        let slug = String(serverName || 'unknown_server')
          .toLowerCase()
          .trim()
          .replace(/[^a-z0-9]+/g, '_')
          .replace(/^_+|_+$/g, '');

        if (!slug) {
          slug = 'unknown_server';
        }

        return `server_card_${slug}`;
      }

      function isServerScopeOptionEnabled(serverName) {
        const visibility = window.DCS_SERVER_SCOPE_CARD_VISIBILITY || {};
        return visibility[getServerScopeFeatureKey(serverName)] !== false;
      }

      window.DCS_SELECTED_SERVER = getSavedScope();
      window.getDcsSelectedServer = function() {
        if (!isServerScopeEnabled()) {
          return '';
        }
        return normaliseServerName(window.DCS_SELECTED_SERVER || getSavedScope());
      };

      async function populateServerScopeSelector() {
        const control = document.getElementById('serverScopeControl');
        const select = document.getElementById('serverScopeSelect');
        if (!control || !select || !window.dcsAPI) return;

        if (!isServerScopeEnabled()) {
          control.hidden = true;
          setSavedScope('');
          return;
        }

        try {
          const result = await window.dcsAPI.getServers({ ignoreScope: true });
          const responseData = result.data || result;
          const servers = Array.isArray(responseData) ? responseData : (responseData.servers || []);
          const names = [...new Set(servers
            .map(server => normaliseServerName(server.name || server.server_name))
            .filter(Boolean)
            .filter(isServerScopeOptionEnabled))];

          if (!names.length) {
            control.hidden = true;
            return;
          }

          const savedScope = getSavedScope();
          select.innerHTML = `<option value="">${escapeHtml(<?php echo json_encode(dcs_t('server_scope.all_servers')); ?>)}</option>`;
          names.forEach((name, index) => {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            select.appendChild(option);
          });

          if (savedScope && names.includes(savedScope)) {
            select.value = savedScope;
            window.DCS_SELECTED_SERVER = savedScope;
          } else if (savedScope) {
            setSavedScope('');
          }

          control.hidden = false;
        } catch (error) {
          control.hidden = true;
        }
      }

      document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('serverScopeSelect');
        if (select) {
          select.addEventListener('change', function() {
            setSavedScope(select.value);
          });
        }
        populateServerScopeSelector();
      });
    })();
  </script>
