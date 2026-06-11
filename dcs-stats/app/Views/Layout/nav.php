<?php
// Include site features configuration
require_once DCS_ROOT_PATH . '/site_features.php';
require_once DCS_ROOT_PATH . '/language.php';
require_once DCS_ROOT_PATH . '/site-config/demo_helpers.php';
// Include path configuration if not already included
if (!defined('BASE_PATH')) {
    require_once DCS_ROOT_PATH . '/config_path.php';
}

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
$customLinks = $navigationState['customLinks'];
$customLinksMenuText = $navigationState['customLinksMenuText'];
$serverScopeEnabled = $navigationState['serverScopeEnabled'];
$serverCardVisibility = $navigationState['serverCardVisibility'];
?>
<nav class="nav-bar" id="navBar">
  <div class="mobile-menu-header">
    <span class="mobile-menu-title"><?= htmlspecialchars(dcs_t('nav.mobile_title')) ?></span>
    <button class="mobile-menu-close" id="mobileMenuClose" aria-label="<?= htmlspecialchars(dcs_t('nav.close')) ?>">
      <span>&times;</span>
    </button>
  </div>
  <ul class="nav-menu">
    <?php foreach ($menuItems as $item): ?>
      <?php if ($navigationService->shouldShow($item)): ?>
        <?php 
        $itemType = $item['type'] ?? 'page';
        ?>
        <?php if (in_array($itemType, ['discord', 'squadron_homepage'])): ?>
          <li><a class="nav-link" href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($navigationService->label($item)) ?></a></li>
        <?php else: ?>
          <li><a class="nav-link" href="<?php echo url($item['url']); ?>"><?= htmlspecialchars($navigationService->label($item)) ?></a></li>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>

    <?php if (isFeatureEnabled('nav_custom_links') && !empty($customLinks)): ?>
      <li class="public-nav-dropdown">
        <button type="button" class="nav-link nav-dropdown-button" aria-expanded="false">
          <?= htmlspecialchars($customLinksMenuText) ?>
          <span class="nav-dropdown-caret">▼</span>
        </button>
        <ul class="public-nav-dropdown-menu">
          <?php foreach ($customLinks as $link): ?>
            <?php
              $linkUrl = trim((string)$link['url']);
              $isExternal = preg_match('#^https?://#i', $linkUrl);
              $target = ($link['new_tab'] ?? true) ? ' target="_blank" rel="noopener noreferrer"' : '';
            ?>
            <li>
              <a class="nav-link public-nav-dropdown-link" href="<?= htmlspecialchars($linkUrl) ?>"<?= $isExternal ? $target : '' ?>>
                <?= htmlspecialchars($link['label']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </li>
    <?php endif; ?>
    
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
<script>
// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('mobileMenuToggle');
    const menuClose = document.getElementById('mobileMenuClose');
    const navBar = document.getElementById('navBar');
    const overlay = document.getElementById('mobileMenuOverlay');
    const body = document.body;
    
    function openMenu() {
        navBar.classList.add('mobile-menu-open');
        overlay.classList.add('active');
        body.style.overflow = 'hidden';
    }
    
    function closeMenu() {
        navBar.classList.remove('mobile-menu-open');
        overlay.classList.remove('active');
        body.style.overflow = '';
    }
    
    if (menuToggle) {
        menuToggle.addEventListener('click', openMenu);
    }
    
    if (menuClose) {
        menuClose.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeMenu();
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeMenu);
    }
    
    // Close menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (link.classList.contains('nav-dropdown-button')) {
                return;
            }
            if (window.innerWidth <= 768) {
                closeMenu();
            }
        });
    });

    document.querySelectorAll('.public-nav-dropdown').forEach(dropdown => {
        const button = dropdown.querySelector('.nav-dropdown-button');
        if (!button) return;

        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            document.querySelectorAll('.public-nav-dropdown.open').forEach(openDropdown => {
                if (openDropdown !== dropdown) {
                    openDropdown.classList.remove('open');
                    const openButton = openDropdown.querySelector('.nav-dropdown-button');
                    if (openButton) openButton.setAttribute('aria-expanded', 'false');
                }
            });

            const isOpen = dropdown.classList.toggle('open');
            button.setAttribute('aria-expanded', String(isOpen));
        });
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.public-nav-dropdown')) {
            return;
        }

        document.querySelectorAll('.public-nav-dropdown.open').forEach(dropdown => {
            dropdown.classList.remove('open');
            const button = dropdown.querySelector('.nav-dropdown-button');
            if (button) button.setAttribute('aria-expanded', 'false');
        });
    });
});
</script>
