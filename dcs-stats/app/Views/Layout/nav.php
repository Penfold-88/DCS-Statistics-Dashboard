<?php
// Include site features configuration
\DcsStats\Core\SupportBootstrap::siteFeatures();
\DcsStats\Core\SupportBootstrap::language();
\DcsStats\Core\AdminBootstrap::demo();

if (!isset($frontendDemoBannerShown)) {
    $frontendDemoBannerShown = false;
}

$frontendDemoMode = (function_exists('isDemoMode') && isDemoMode())
    || file_exists(DCS_ROOT_PATH . '/.demo')
    || file_exists(DCS_ROOT_PATH . '/site-config/.demo')
    || file_exists(dirname(DCS_ROOT_PATH) . '/.demo');

if (!$frontendDemoBannerShown && $frontendDemoMode): ?>
<div class="demo-notice-bar" role="note" style="align-items: center; background: linear-gradient(90deg, #ffd21f 0%, #ff8a00 100%); border-bottom: 2px solid rgba(0,0,0,0.28); color: #101010; display: flex; flex-wrap: wrap; gap: 8px 14px; justify-content: center; padding: 10px 18px; text-align: center; font-size: 14px; font-weight: 700; letter-spacing: 0; position: relative; z-index: 1200;">
    <strong>DEMO:</strong>
    Data provided by VFS-252 Sky Pirates.
    BO Demo available here:
    <a href="<?php echo htmlspecialchars(url('site-config/login.php')); ?>" style="background: rgba(0,0,0,0.16); border: 1px solid rgba(0,0,0,0.24); border-radius: 6px; color: #101010; padding: 4px 10px; text-decoration: none;">Admin Login</a>
    <span>Username: <strong>Demo</strong></span>
    <span>Password: <strong>Demo123!</strong></span>
</div>
<?php
    $frontendDemoBannerShown = true;
endif;

$navigationService = new \DcsStats\Services\PublicNavigationService();
$navigationState = $navigationService->state();
$menuItems = $navigationState['menuItems'];
$serverScopeEnabled = $navigationState['serverScopeEnabled'];
$serverCardVisibility = $navigationState['serverCardVisibility'];
$visibleMenuItems = array_values(array_filter($menuItems, fn(array $item): bool => $navigationService->shouldShow($item)));
$menuChildren = [];
foreach ($visibleMenuItems as $visibleItem) {
    if (!empty($visibleItem['parent_id'])) {
        $menuChildren[$visibleItem['parent_id']][] = $visibleItem;
    }
}
?>
<nav class="nav-bar" id="navBar">
  <div class="mobile-menu-header">
    <span class="mobile-menu-title"><?= htmlspecialchars(dcs_t('nav.mobile_title')) ?></span>
    <button class="mobile-menu-close" id="mobileMenuClose" aria-label="<?= htmlspecialchars(dcs_t('nav.close')) ?>">
      <span>&times;</span>
    </button>
  </div>
  <ul class="nav-menu">
    <?php foreach ($visibleMenuItems as $item): if (!empty($item['parent_id'])) continue; ?>
      <?php $children = $menuChildren[$item['id']] ?? []; ?>
      <?php if ($children): ?>
        <?php
          $external = in_array($item['type'] ?? '', ['external', 'discord', 'squadron_homepage'], true);
          $href = $external ? $item['url'] : url($item['url']);
          $target = $external && !empty($item['new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
        ?>
        <li class="public-nav-dropdown">
          <div class="public-nav-dropdown-toggle">
            <?php if (($item['type'] ?? '') !== 'group'): ?>
              <a class="nav-link public-nav-parent-link" href="<?= e($href) ?>"<?= $target ?>><?= e($navigationService->label($item)) ?></a>
            <?php else: ?>
              <span class="nav-link public-nav-parent-label"><?= e($navigationService->label($item)) ?></span>
            <?php endif; ?>
            <button type="button" class="nav-link nav-dropdown-button" aria-expanded="false" aria-label="<?= e($navigationService->label($item)) ?> menu"><span class="nav-dropdown-caret">▼</span></button>
          </div>
          <ul class="public-nav-dropdown-menu">
            <?php foreach ($children as $child):
              $external = in_array($child['type'] ?? '', ['external', 'discord', 'squadron_homepage'], true);
              $href = $external ? $child['url'] : url($child['url']);
              $target = $external && !empty($child['new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
            ?>
              <li><a class="nav-link public-nav-dropdown-link" href="<?= e($href) ?>"<?= $target ?>><?= e($navigationService->label($child)) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
      <?php elseif (($item['type'] ?? '') !== 'group'):
        $external = in_array($item['type'] ?? '', ['external', 'discord', 'squadron_homepage'], true);
        $href = $external ? $item['url'] : url($item['url']);
        $target = $external && !empty($item['new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
      ?>
        <li><a class="nav-link" href="<?= e($href) ?>"<?= $target ?>><?= e($navigationService->label($item)) ?></a></li>
      <?php endif; ?>
    <?php endforeach; ?>
    
    <?php 
    // Check if user is logged in as admin
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): 
    ?>
      <li><a class="nav-link" href="<?php echo url('site-config/'); ?>"><?= htmlspecialchars(dcs_t('nav.site_config')) ?></a></li>
    <?php endif; ?>
  </ul>
</nav>
<script>
window.DCS_SERVER_SCOPE_ENABLED = <?= $serverScopeEnabled ? 'true' : 'false' ?>;
window.DCS_SERVER_SCOPE_CARD_VISIBILITY = <?= json_encode($serverCardVisibility) ?>;
</script>
<?php if ($serverScopeEnabled): ?>
<div class="server-scope-bar" id="serverScopeControl" hidden>
  <div class="server-scope-control">
    <select id="serverScopeSelect" aria-label="<?= htmlspecialchars(dcs_t('server_scope.label')) ?>">
      <option value=""><?= htmlspecialchars(dcs_t('server_scope.all_servers')) ?></option>
    </select>
  </div>
</div>
<?php endif; ?>
<div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
<script src="<?= e(assetUrl('js/layout/public-nav.js')) ?>"></script>
