<?php
$headerState = (new \DcsStats\Services\HeaderService())->state();
$siteName = $headerState['siteName'];
$siteMetadata = $headerState['siteMetadata'];
$headerBranding = $headerState['headerBranding'];
$headerLogoPath = $headerState['headerLogoPath'];
$showHeaderLogo = $headerState['showHeaderLogo'];
$showHeaderText = $headerState['showHeaderText'];
$hasPageBackgroundImage = $headerState['hasPageBackgroundImage'];
$previewColors = $headerState['previewColors'];
$frontendDemoMode = $headerState['frontendDemoMode'];
$pageSeo = isset($pageSeo) && is_array($pageSeo) ? $pageSeo : [];
$documentTitle = !empty($pageSeo['title']) ? $pageSeo['title'] . ' - ' . $siteName : $siteName . ' Dashboard';
$metaDescription = !empty($pageSeo['description']) ? $pageSeo['description'] : ($siteMetadata['description'] ?? '');
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(dcs_default_language(), ENT_QUOTES); ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php if ($metaDescription !== ''): ?>
  <meta name="description" content="<?php echo htmlspecialchars($metaDescription, ENT_QUOTES); ?>" />
  <?php endif; ?>
  <?php if (!empty($siteMetadata['keywords'])): ?>
  <meta name="keywords" content="<?php echo htmlspecialchars($siteMetadata['keywords'], ENT_QUOTES); ?>" />
  <?php endif; ?>
  <?php if (!empty($isPreview) || !empty($siteMetadata['block_search_engines'])): ?>
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <?php else: ?>
  <meta name="robots" content="index,follow" />
  <?php endif; ?>
  <title><?php echo htmlspecialchars($documentTitle); ?></title>
  <?php if (!empty($pageSeo)): ?>
  <meta property="og:title" content="<?= e($pageSeo['title'] ?? '') ?>" />
  <?php if ($metaDescription !== ''): ?><meta property="og:description" content="<?= e($metaDescription) ?>" /><?php endif; ?>
  <?php if (!empty($pageSeo['image'])): ?><meta property="og:image" content="<?= e(url($pageSeo['image'])) ?>" /><?php endif; ?>
  <meta property="og:type" content="website" />
  <?php endif; ?>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('styles.php')); ?>" />
  <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('styles-mobile.css')); ?>" />
  <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('theme_overrides.css')); ?>" />
  <?php if (file_exists(DCS_ROOT_PATH . '/custom_theme.css')): ?>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(assetUrl('custom_theme.css')); ?>" />
  <?php endif; ?>
  <?php if (file_exists(DCS_ROOT_PATH . '/header_custom.css')): ?>
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
  <?php if ($frontendDemoMode): ?>
  <style>
    body.has-demo-banner .main-header {
      top: 42px;
    }
  </style>
  <?php endif; ?>
</head>
<body class="<?php echo trim(($frontendDemoMode ? 'has-demo-banner ' : '') . ($hasPageBackgroundImage ? 'has-page-background-image' : '')); ?>">
  <?php $frontendDemoBannerShown = false; ?>
  <?php if ($frontendDemoMode): ?>
  <?php $frontendDemoBannerShown = true; ?>
  <div class="demo-notice-bar" role="note" style="align-items: center; background: linear-gradient(90deg, #ffd21f 0%, #ff8a00 100%); border-bottom: 2px solid rgba(0,0,0,0.28); color: #101010; display: flex; flex-wrap: wrap; gap: 8px 14px; justify-content: center; padding: 10px 18px; text-align: center; font-size: 14px; font-weight: 700; letter-spacing: 0; position: sticky; left: 0; right: 0; top: 0; z-index: 5000;">
    <strong>DEMO:</strong>
    Data provided by VFS-252 Sky Pirates.
    BO Demo available here:
    <a href="<?php echo htmlspecialchars(url('site-config/login.php')); ?>" style="background: rgba(0,0,0,0.16); border: 1px solid rgba(0,0,0,0.24); border-radius: 6px; color: #101010; padding: 4px 10px; text-decoration: none;">Admin Login</a>
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
    window.DCS_SERVER_SCOPE_CONFIG = {
      allServersLabel: <?php echo json_encode(dcs_t('server_scope.all_servers')); ?>
    };
  </script>
  <script src="<?php echo htmlspecialchars(assetUrl('js/layout/server-scope.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
