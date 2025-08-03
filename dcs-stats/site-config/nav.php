<?php
/**
 * Admin Navigation Template
 * Include this file to add consistent navigation to admin pages
 */

// Ensure this is being included from an admin page
if (!defined('ADMIN_PANEL')) {
    die('Direct access not permitted');
}

// No need for path configuration with relative paths

// Get current admin if not already available
if (!isset($currentAdmin)) {
    $currentAdmin = getCurrentAdmin();
}
?>
<!-- Sidebar -->
<aside class="admin-sidebar">
    <div class="admin-logo">
        <h2>⚓ CAG Bridge</h2>
    </div>
    <nav class="admin-nav">
        <ul>
            <li>
                <a href="index.php" <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📊</span>
                    Dashboard
                </a>
            </li>
            <?php if (hasPermission('view_logs')): ?>
            <li>
                <a href="logs.php" <?= basename($_SERVER['PHP_SELF']) === 'logs.php' ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📋</span>
                    Activity Logs
                </a>
            </li>
            <?php endif; ?>
            <?php if (hasPermission('export_data')): ?>
            <li>
                <a href="export.php" <?= basename($_SERVER['PHP_SELF']) === 'export.php' ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📤</span>
                    Export Data
                </a>
            </li>
            <?php endif; ?>
            <?php if (hasPermission('change_settings')): ?>
<?php $isSettingsPage = in_array(basename($_SERVER['PHP_SELF']), ['settings.php', 'api_settings.php', 'themes.php', 'discord_settings.php', 'squadron_settings.php', 'admins.php', 'permissions.php', 'maintenance.php']); ?>
            <li class="nav-dropdown <?= $isSettingsPage ? 'open' : '' ?>">
                <a href="#" class="nav-dropdown-toggle <?= $isSettingsPage ? 'active' : '' ?>">
                    <span class="nav-icon">⚙️</span>
                    Settings
                    <span class="dropdown-arrow">▼</span>
                </a>
                <ul class="nav-dropdown-menu <?= $isSettingsPage ? 'open' : '' ?>">
                    <?php if (hasPermission('manage_admins')): ?>
                    <li>
                        <a href="admins.php" <?= basename($_SERVER['PHP_SELF']) === 'admins.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔐</span>
                            Admins
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($currentAdmin['role'] === ROLE_AIR_BOSS): // Only Air Boss can manage permissions ?>
                    <li>
                        <a href="permissions.php" <?= basename($_SERVER['PHP_SELF']) === 'permissions.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🛡️</span>
                            LSO Permissions
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_api') || $currentAdmin['role'] === ROLE_AIR_BOSS): ?>
                    <li>
                        <a href="api_settings.php" <?= basename($_SERVER['PHP_SELF']) === 'api_settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔌</span>
                            API Settings
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_features') || hasPermission('change_settings')): ?>
                    <li>
                        <a href="settings.php" <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🎛️</span>
                            Site Features
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="maintenance.php" <?= basename($_SERVER['PHP_SELF']) === 'maintenance.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🛠️</span>
                            Maintenance
                        </a>
                    </li>
                    <?php if ($currentAdmin['role'] === ROLE_AIR_BOSS): // Only Air Boss can access Navigation Settings ?>
                    <li>
                        <a href="discord_settings.php" <?= basename($_SERVER['PHP_SELF']) === 'discord_settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🎮</span>
                            Discord Link
                        </a>
                    </li>
                    <li>
                        <a href="squadron_settings.php" <?= basename($_SERVER['PHP_SELF']) === 'squadron_settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">✈️</span>
                            Squadron Homepage
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_themes') || hasPermission('change_settings')): ?>
                    <li>
                        <a href="themes.php" <?= basename($_SERVER['PHP_SELF']) === 'themes.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🎨</span>
                            Themes
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>

<script>
// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
    
    dropdownToggles.forEach(function(toggle, index) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.parentNode;
            const menu = dropdown.querySelector('.nav-dropdown-menu');
            const arrow = this.querySelector('.dropdown-arrow');
            
            // Close other dropdowns first
            document.querySelectorAll('.nav-dropdown').forEach(function(otherDropdown) {
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove('open');
                    const otherMenu = otherDropdown.querySelector('.nav-dropdown-menu');
                    const otherArrow = otherDropdown.querySelector('.dropdown-arrow');
                    if (otherMenu) {
                        otherMenu.classList.remove('open');
                    }
                    if (otherArrow) {
                        otherArrow.style.transform = 'rotate(0deg)';
                    }
                }
            });
            
            // Toggle current dropdown
            const isCurrentlyOpen = dropdown.classList.contains('open');
            
            if (isCurrentlyOpen) {
                // Close it
                dropdown.classList.remove('open');
                if (menu) {
                    menu.classList.remove('open');
                }
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            } else {
                // Open it
                dropdown.classList.add('open');
                if (menu) {
                    menu.classList.add('open');
                }
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        });
    });
    
    // Close dropdown when clicking outside (but not if we're already on a settings page)
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-dropdown')) {
            const currentPath = window.location.pathname;
            const settingsPages = ['settings.php', 'api_settings.php', 'themes.php', 'discord_settings.php', 'squadron_settings.php', 'admins.php', 'maintenance.php'];
            const isOnSettingsPage = settingsPages.some(page => currentPath.includes(page));
            
            if (!isOnSettingsPage) {
                document.querySelectorAll('.nav-dropdown.open').forEach(function(dropdown) {
                    dropdown.classList.remove('open');
                    const menu = dropdown.querySelector('.nav-dropdown-menu');
                    const arrow = dropdown.querySelector('.dropdown-arrow');
                    if (menu) {
                        menu.classList.remove('open');
                    }
                    if (arrow) {
                        arrow.style.transform = 'rotate(0deg)';
                    }
                });
            }
        }
    });
});
</script>