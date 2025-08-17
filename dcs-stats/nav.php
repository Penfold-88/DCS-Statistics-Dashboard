<?php
// Include site features configuration
require_once __DIR__ . '/site_features.php';
// Include path configuration if not already included
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/config_path.php';
}

// Get writable path for menu configuration (same logic as in themes.php)
if (!function_exists('getMenuConfigPath')) {
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
}

// Load menu configuration
$menuConfigFile = getMenuConfigPath();
$defaultMenuItems = [
    ['name' => 'Home', 'url' => 'index.php', 'enabled' => true],
    ['name' => 'Leaderboard', 'url' => 'leaderboard.php', 'enabled' => true],
    ['name' => 'Pilot Statistics', 'url' => 'pilot_statistics.php', 'enabled' => true],
    ['name' => 'Pilot Credits', 'url' => 'pilot_credits.php', 'enabled' => true],
    ['name' => 'Squadrons', 'url' => 'squadrons.php', 'enabled' => true],
    ['name' => 'Servers', 'url' => 'servers.php', 'enabled' => true]
];

$menuItems = $defaultMenuItems;
if (file_exists($menuConfigFile)) {
    $savedMenu = json_decode(file_get_contents($menuConfigFile), true);
    if ($savedMenu && is_array($savedMenu)) {
        $menuItems = $savedMenu;
    }
}
// Apply feature flags to standard navigation items
foreach ($menuItems as &$item) {
    switch ($item['url'] ?? '') {
        case 'index.php':
            if (!isFeatureEnabled('nav_home')) {
                $item['enabled'] = false;
            }
            break;
        case 'leaderboard.php':
            if (!isFeatureEnabled('nav_leaderboard')) {
                $item['enabled'] = false;
            }
            break;
        case 'pilot_statistics.php':
            if (!isFeatureEnabled('nav_pilot_statistics')) {
                $item['enabled'] = false;
            }
            break;
        case 'pilot_credits.php':
            if (!isFeatureEnabled('nav_pilot_credits')) {
                $item['enabled'] = false;
            }
            break;
        case 'squadrons.php':
            if (!isFeatureEnabled('nav_squadrons')) {
                $item['enabled'] = false;
            }
            break;
        case 'servers.php':
            if (!isFeatureEnabled('nav_servers')) {
                $item['enabled'] = false;
            }
            break;
    }
}
unset($item);
// Check if menu config exists, if not check for Discord/Squadron links to add
$hasDiscordInMenu = false;
$hasSquadronInMenu = false;
foreach ($menuItems as $item) {
    if (($item['type'] ?? '') === 'discord') $hasDiscordInMenu = true;
    if (($item['type'] ?? '') === 'squadron_homepage') $hasSquadronInMenu = true;
}

// Add Discord if enabled but not in menu AND has a valid URL configured
if (!$hasDiscordInMenu && isFeatureEnabled('show_discord_link')) {
    $discordUrl = getFeatureValue('discord_link_url', '');
    // Only add Discord menu item if URL is not empty
    if (!empty($discordUrl)) {
        $menuItems[] = [
            'name' => 'Discord',
            'url' => $discordUrl,
            'enabled' => true,
            'type' => 'discord'
        ];
    }
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
?>
<nav class="nav-bar" id="navBar">
  <div class="mobile-menu-header">
    <span class="mobile-menu-title">Navigation</span>
    <button class="mobile-menu-close" id="mobileMenuClose" aria-label="Close navigation menu">
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
        
        if ($item['url'] === 'pilot_credits.php' && !isFeatureEnabled('credits_enabled')) {
            $showItem = false;
        } elseif ($item['url'] === 'squadrons.php' && !isFeatureEnabled('squadrons_enabled')) {
            $showItem = false;
        } elseif ($itemType === 'discord') {
            // Check if Discord is enabled AND has a valid URL
            $discordUrl = getFeatureValue('discord_link_url', '');
            if (!isFeatureEnabled('show_discord_link') || empty($discordUrl)) {
                $showItem = false;
            }
        } elseif ($itemType === 'squadron_homepage' && (!isFeatureEnabled('show_squadron_homepage') || empty(getFeatureValue('squadron_homepage_url')))) {
            $showItem = false;
        }
        ?>
        <?php if ($showItem): ?>
          <?php if (in_array($itemType, ['discord', 'squadron_homepage'])): ?>
            <li><a class="nav-link" href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['name']) ?></a></li>
          <?php else: ?>
            <li><a class="nav-link" href="<?php echo url($item['url']); ?>"><?= htmlspecialchars($item['name']) ?></a></li>
          <?php endif; ?>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>
    
    <?php 
    // Check if user is logged in as admin
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): 
    ?>
      <li><a class="nav-link" href="<?php echo url('site-config/'); ?>">Site Config</a></li>
    <?php endif; ?>
  </ul>
</nav>
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
            if (window.innerWidth <= 768) {
                closeMenu();
            }
        });
    });
});
</script>
