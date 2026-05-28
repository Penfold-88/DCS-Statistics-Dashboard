<?php
// Include site features configuration
require_once __DIR__ . '/site_features.php';
require_once __DIR__ . '/language.php';
require_once __DIR__ . '/site-config/demo_helpers.php';
// Include path configuration if not already included
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/config_path.php';
}

if (!isset($frontendDemoBannerShown)) {
    $frontendDemoBannerShown = false;
}

$frontendDemoMode = (function_exists('isDemoMode') && isDemoMode())
    || file_exists(__DIR__ . '/.demo')
    || file_exists(__DIR__ . '/site-config/.demo')
    || file_exists(dirname(__DIR__) . '/.demo');

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

// Get writable path for menu configuration (same logic as in themes.php)
function getMenuConfigPath() {
    // Try primary location with data directory
    $primaryPath = __DIR__ . '/site-config/data/menu_config.json';
    $primaryDir = dirname($primaryPath);
    
    if (is_dir($primaryDir) && is_writable($primaryDir)) {
        return $primaryPath;
    }
    
    if (!is_dir($primaryDir)) {
        @mkdir($primaryDir, 0777, true);
        @chmod($primaryDir, 0777);
        if (is_dir($primaryDir) && is_writable($primaryDir)) {
            return $primaryPath;
        }
    }
    
    // Try alternative location
    $altPath = __DIR__ . '/menu_config.json';
    $altDir = dirname($altPath);
    if (is_writable($altDir)) {
        return $altPath;
    }
    
    // Fall back to temp directory
    $tempDir = sys_get_temp_dir() . '/dcs_stats';
    if (!is_dir($tempDir)) {
        @mkdir($tempDir, 0777, true);
    }
    
    return $tempDir . '/menu_config.json';
}

// Load menu configuration
$menuConfigFile = getMenuConfigPath();
$defaultMenuItems = [
    ['name' => 'Home', 'url' => 'index.php', 'enabled' => true, 'label_key' => 'nav.home'],
    ['name' => 'Leaderboard', 'url' => 'leaderboard.php', 'enabled' => true, 'label_key' => 'nav.leaderboard'],
    ['name' => 'Pilot Statistics', 'url' => 'pilot_statistics.php', 'enabled' => true, 'label_key' => 'nav.pilot_statistics'],
    ['name' => 'Pilot Credits', 'url' => 'pilot_credits.php', 'enabled' => true, 'label_key' => 'nav.pilot_credits'],
    ['name' => 'Squadrons', 'url' => 'squadrons.php', 'enabled' => true, 'label_key' => 'nav.squadrons'],
    ['name' => 'Servers', 'url' => 'servers.php', 'enabled' => true, 'label_key' => 'nav.servers']
];

$menuItems = $defaultMenuItems;
if (file_exists($menuConfigFile)) {
    $savedMenu = json_decode(file_get_contents($menuConfigFile), true);
    if ($savedMenu && is_array($savedMenu)) {
        $menuItems = $savedMenu;
    }
}
// Check if menu config exists, if not check for Discord/Squadron links to add
$hasDiscordInMenu = false;
$hasSquadronInMenu = false;
foreach ($menuItems as $item) {
    if (($item['type'] ?? '') === 'discord') $hasDiscordInMenu = true;
    if (($item['type'] ?? '') === 'squadron_homepage') $hasSquadronInMenu = true;
}

// Add Discord if enabled but not in menu
if (!$hasDiscordInMenu && isFeatureEnabled('show_discord_link')) {
    $menuItems[] = [
        'name' => 'Discord',
        'url' => getFeatureValue('discord_link_url', 'https://discord.gg/DNENf6pUNX'),
        'enabled' => true,
        'type' => 'discord'
    ];
}

// Add Squadron Homepage if enabled but not in menu
if (!$hasSquadronInMenu && isFeatureEnabled('show_squadron_homepage') && !empty(getFeatureValue('squadron_homepage_url'))) {
    $menuItems[] = [
        'name' => getFeatureValue('squadron_homepage_text', 'Squadron'),
        'url' => getFeatureValue('squadron_homepage_url'),
        'enabled' => true,
        'type' => 'squadron_homepage'
    ];
}

$customLinks = getFeatureValue('custom_links', []);
if (!is_array($customLinks)) {
    $customLinks = [];
}
$customLinks = array_values(array_filter($customLinks, function($link) {
    return is_array($link)
        && ($link['enabled'] ?? true)
        && !empty(trim($link['label'] ?? ''))
        && !empty(trim($link['url'] ?? ''));
}));
$customLinksMenuText = trim((string)getFeatureValue('custom_links_menu_text', 'Squadron Links'));
if ($customLinksMenuText === '') {
    $customLinksMenuText = dcs_t('nav.squadron_links');
}

$serverScopeEnabled = isFeatureEnabled('server_scope_filter');
$serverCardVisibility = [];
$siteFeatureValues = loadSiteFeatures();
foreach ($siteFeatureValues as $featureKey => $enabled) {
    if (strpos($featureKey, 'server_card_') === 0) {
        $serverCardVisibility[$featureKey] = (bool)$enabled;
    }
}

function dcs_nav_label($item) {
    $knownLabels = [
        'index.php' => ['Home', 'nav.home'],
        'leaderboard.php' => ['Leaderboard', 'nav.leaderboard'],
        'pilot_statistics.php' => ['Pilot Statistics', 'nav.pilot_statistics'],
        'pilot_credits.php' => ['Pilot Credits', 'nav.pilot_credits'],
        'squadrons.php' => ['Squadrons', 'nav.squadrons'],
        'servers.php' => ['Servers', 'nav.servers']
    ];

    if (!empty($item['label_key'])) {
        return dcs_t($item['label_key']);
    }

    $url = $item['url'] ?? '';
    $name = $item['name'] ?? '';
    if (isset($knownLabels[$url]) && $name === $knownLabels[$url][0]) {
        return dcs_t($knownLabels[$url][1]);
    }

    return $name;
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
    <?php foreach ($menuItems as $item): ?>
      <?php if ($item['enabled']): ?>
        <?php 
        // Check feature flags for specific pages
        $showItem = true;
        $itemType = $item['type'] ?? 'page';
        
        $pageFeatureMap = [
            'index.php' => 'nav_home',
            'leaderboard.php' => 'nav_leaderboard',
            'pilot_statistics.php' => 'nav_pilot_statistics',
            'pilot_credits.php' => 'nav_pilot_credits',
            'squadrons.php' => 'nav_squadrons',
            'servers.php' => 'nav_servers'
        ];

        if (isset($pageFeatureMap[$item['url']]) && !isFeatureEnabled($pageFeatureMap[$item['url']])) {
            $showItem = false;
        } elseif ($item['url'] === 'pilot_credits.php' && !isFeatureEnabled('credits_enabled')) {
            $showItem = false;
        } elseif ($item['url'] === 'squadrons.php' && !isFeatureEnabled('squadrons_enabled')) {
            $showItem = false;
        } elseif ($itemType === 'discord' && !isFeatureEnabled('show_discord_link')) {
            $showItem = false;
        } elseif ($itemType === 'squadron_homepage' && (!isFeatureEnabled('show_squadron_homepage') || empty(getFeatureValue('squadron_homepage_url')))) {
            $showItem = false;
        }
        ?>
        <?php if ($showItem): ?>
          <?php if (in_array($itemType, ['discord', 'squadron_homepage'])): ?>
            <li><a class="nav-link" href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars(dcs_nav_label($item)) ?></a></li>
          <?php else: ?>
            <li><a class="nav-link" href="<?php echo url($item['url']); ?>"><?= htmlspecialchars(dcs_nav_label($item)) ?></a></li>
          <?php endif; ?>
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
