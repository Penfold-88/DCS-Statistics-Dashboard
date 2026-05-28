<?php
/**
 * Admin Navigation Template
 * Include this file to add consistent navigation to admin pages
 */

// Ensure this is being included from an admin page
if (!defined('ADMIN_PANEL')) {
    die('Direct access not permitted');
}

require_once dirname(__DIR__) . '/language.php';

// No need for path configuration with relative paths

// Get current admin if not already available
if (!isset($currentAdmin)) {
    $currentAdmin = getCurrentAdmin();
}
?>
<?php if (function_exists('isDemoMode') && isDemoMode()): ?>
<div class="demo-admin-banner" role="note">
    <strong>DEMO MODE:</strong>
    All sensitive data is restricted for security. Data resets every hour.
</div>
<?php endif; ?>
<!-- Sidebar -->
<aside class="admin-sidebar">
    <div class="admin-logo">
        <h2>⚓ Admin Panel</h2>
    </div>
    <nav class="admin-nav">
        <ul>
            <li>
                <a href="index.php" <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📊</span>
                    <?= e(dcs_t('admin.nav.dashboard')) ?>
                </a>
            </li>
            <?php if (hasPermission('view_logs')): ?>
            <li>
                <a href="logs.php" <?= basename($_SERVER['PHP_SELF']) === 'logs.php' ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📋</span>
                    <?= e(dcs_t('admin.nav.activity_logs')) ?>
                </a>
            </li>
            <?php endif; ?>
            <?php if (hasPermission('export_data')): ?>
            <li>
                <a href="export.php" <?= basename($_SERVER['PHP_SELF']) === 'export.php' ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📤</span>
                    <?= e(dcs_t('admin.nav.export_data')) ?>
                </a>
            </li>
            <?php endif; ?>
            <?php if (hasPermission('change_settings') || hasPermission('manage_admins') || hasPermission('manage_permissions') || hasPermission('manage_api') || hasPermission('manage_features') || hasPermission('manage_maintenance') || hasPermission('manage_updates') || hasPermission('manage_discord') || hasPermission('manage_squadrons') || hasPermission('manage_themes')): ?>
<?php $isSettingsPage = in_array(basename($_SERVER['PHP_SELF']), ['settings.php', 'metadata.php', 'language_settings.php', 'custom_links.php', 'settings_backup.php', 'api_settings.php', 'api_health.php', 'themes.php', 'discord_settings.php', 'squadron_settings.php', 'admins.php', 'permissions.php', 'maintenance.php', 'update.php']); ?>
            <li class="nav-dropdown <?= $isSettingsPage ? 'open' : '' ?>">
                <a href="#" class="nav-dropdown-toggle <?= $isSettingsPage ? 'active' : '' ?>">
                    <span class="nav-icon">⚙️</span>
                    <?= e(dcs_t('admin.nav.settings')) ?>
                    <span class="dropdown-arrow">▼</span>
                </a>
                <ul class="nav-dropdown-menu <?= $isSettingsPage ? 'open' : '' ?>">
                    <?php if (hasPermission('manage_admins')): ?>
                    <li>
                        <a href="admins.php" <?= basename($_SERVER['PHP_SELF']) === 'admins.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔐</span>
                            <?= e(dcs_t('admin.nav.admins')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_permissions')): ?>
                    <li>
                        <a href="permissions.php" <?= basename($_SERVER['PHP_SELF']) === 'permissions.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🛡️</span>
                            <?= e(dcs_t('admin.nav.permissions')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_api')): ?>
                    <li>
                        <a href="api_settings.php" <?= basename($_SERVER['PHP_SELF']) === 'api_settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔌</span>
                            <?= e(dcs_t('admin.nav.api_settings')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_features')): ?>
                    <li>
                        <a href="settings.php" <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🎛️</span>
                            <?= e(dcs_t('admin.nav.site_features')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="custom_links.php" <?= basename($_SERVER['PHP_SELF']) === 'custom_links.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔗</span>
                            <?= e(dcs_t('admin.nav.custom_links')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="metadata.php" <?= basename($_SERVER['PHP_SELF']) === 'metadata.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔎</span>
                            <?= e(dcs_t('admin.nav.privacy_seo')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="language_settings.php" <?= basename($_SERVER['PHP_SELF']) === 'language_settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🌐</span>
                            <?= e(dcs_t('admin.nav.language')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="settings_backup.php" <?= basename($_SERVER['PHP_SELF']) === 'settings_backup.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">💾</span>
                            <?= e(dcs_t('admin.nav.settings_backup')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_maintenance')): ?>
                    <li>
                        <a href="maintenance.php" <?= basename($_SERVER['PHP_SELF']) === 'maintenance.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🛠️</span>
                            <?= e(dcs_t('admin.nav.maintenance')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_updates')): ?>
                    <li>
                        <a href="update.php" <?= basename($_SERVER['PHP_SELF']) === 'update.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔄</span>
                            <?= e(dcs_t('admin.nav.update')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_discord')): ?>
                    <li>
                        <a href="discord_settings.php" <?= basename($_SERVER['PHP_SELF']) === 'discord_settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🎮</span>
                            <?= e(dcs_t('admin.nav.discord_link')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_squadrons')): ?>
                    <li>
                        <a href="squadron_settings.php" <?= basename($_SERVER['PHP_SELF']) === 'squadron_settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">✈️</span>
                            <?= e(dcs_t('admin.nav.squadron_homepage')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_themes')): ?>
                    <li>
                        <a href="themes.php" <?= basename($_SERVER['PHP_SELF']) === 'themes.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🎨</span>
                            <?= e(dcs_t('admin.nav.themes')) ?>
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
            const settingsPages = ['settings.php', 'metadata.php', 'language_settings.php', 'custom_links.php', 'settings_backup.php', 'api_settings.php', 'api_health.php', 'themes.php', 'discord_settings.php', 'squadron_settings.php', 'admins.php', 'permissions.php', 'maintenance.php', 'update.php'];
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
