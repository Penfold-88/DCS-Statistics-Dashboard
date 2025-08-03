<?php
// Include path configuration
require_once __DIR__ . '/config_path.php';
require_once __DIR__ . '/site_features.php';

// Security headers for protection against common web vulnerabilities
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
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

header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src {$cspConnectSrc};");

if (isFeatureEnabled('maintenance_mode')) {
    $whitelist = array_filter(array_map('trim', explode(',', getFeatureValue('maintenance_whitelist', ''))));
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!in_array($clientIp, $whitelist)) {
        include __DIR__ . '/maintenance.php';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DCS Statistics Dashboard</title>
  <link rel="stylesheet" href="<?php echo url('styles.php'); ?>" />
  <link rel="stylesheet" href="<?php echo url('styles-mobile.css'); ?>" />
  <?php if (file_exists(__DIR__ . '/custom_theme.css')): ?>
  <link rel="stylesheet" href="<?php echo url('custom_theme.css'); ?>" />
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
          <h1 class="site-title">DCS Statistics</h1>
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
