<?php
// Include path configuration
require_once __DIR__ . '/config_path.php';

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
$configFile = __DIR__ . '/api_config.json';
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
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
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src {$cspConnectSrc};" . $frameAncestors);

// Handle theme preview parameters
$previewColors = null;
if (isset($_GET['preview']) && $_GET['preview'] === '1') {
    $previewColors = [
        'primary_color' => isset($_GET['primary']) ? '#' . $_GET['primary'] : null,
        'secondary_color' => isset($_GET['secondary']) ? '#' . $_GET['secondary'] : null,
        'background_color' => isset($_GET['background']) ? '#' . $_GET['background'] : null,
        'text_color' => isset($_GET['text']) ? '#' . $_GET['text'] : null,
        'link_color' => isset($_GET['link']) ? '#' . $_GET['link'] : null,
        'border_color' => isset($_GET['border']) ? '#' . $_GET['border'] : null,
        'nav_link_color' => isset($_GET['navlink']) ? '#' . $_GET['navlink'] : null,
        'nav_link_hover_bg' => isset($_GET['navhoverbg']) ? '#' . $_GET['navhoverbg'] : null,
        'nav_link_hover_color' => isset($_GET['navhover']) ? '#' . $_GET['navhover'] : null,
    ];
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
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($siteName); ?> Dashboard</title>
  <link rel="stylesheet" href="<?php echo url('styles.php'); ?>" />
  <link rel="stylesheet" href="<?php echo url('styles-mobile.css'); ?>" />
  <?php if (file_exists(__DIR__ . '/custom_theme.css')): ?>
  <link rel="stylesheet" href="<?php echo url('custom_theme.css'); ?>" />
  <?php endif; ?>
  <?php if ($previewColors): ?>
  <style>
    :root {
      <?php foreach ($previewColors as $var => $color): ?>
      <?php if ($color): ?>
      --<?php echo $var; ?>: <?php echo $color; ?> !important;
      <?php endif; ?>
      <?php endforeach; ?>
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
  <script src="<?php echo url('js/api-client.js'); ?>"></script>
  <script src="<?php echo url('mobile-enhancements.js'); ?>"></script>
</head>
<body>
  <header class="main-header">
    <div class="header-background"></div>
    <div class="header-overlay"></div>
    <div class="header-container">
      <div class="header-brand">
        <div class="brand-text">
          <h1 class="site-title"><?php echo htmlspecialchars($siteName); ?></h1>
          <p class="site-subtitle">Combat Data & Analytics Platform</p>
        </div>
      </div>
      <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation menu">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
      </button>
      <div class="header-actions">
        <div class="status-indicator">
          <span class="status-dot"></span>
          <span class="status-text">Live Data</span>
        </div>
      </div>
    </div>
  </header>
