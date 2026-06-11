<?php if (defined('ADMIN_PANEL') && $demoMode): ?>
<div class="demo-admin-banner" role="note" style="background: linear-gradient(90deg, #ffd21f 0%, #ff8a00 100%); border-bottom: 2px solid rgba(0,0,0,0.35); color: #101010; font-size: 14px; font-weight: 700; left: 0; letter-spacing: 0; padding: 10px 18px; position: fixed; right: 0; text-align: center; top: 0; z-index: 3000;">
    <strong>DEMO MODE:</strong>
    All sensitive data is restricted for security. Data resets every hour.
</div>
<style>
    .admin-wrapper {
        padding-top: 42px;
    }

    .demo-readonly-lock {
        opacity: 0.72;
        pointer-events: none;
    }
</style>
<?php endif; ?>
<?php if (defined('ADMIN_PANEL') && $demoRestricted): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.admin-main form, .admin-main button, .admin-main input, .admin-main select, .admin-main textarea').forEach(function(element) {
        if (element.closest('.admin-user-menu')) {
            return;
        }
        if (element.matches('.theme-tab, .tab-button, [role="tab"], [data-demo-readonly-nav]')) {
            return;
        }
        if (element.tagName === 'FORM') {
            element.addEventListener('submit', function(event) {
                event.preventDefault();
                alert('Demo mode is enabled. The admin panel is read-only on the public demo.');
            });
            element.classList.add('demo-readonly-lock');
            return;
        }
        element.disabled = true;
    });
});
</script>
<?php endif; ?>
<aside class="admin-sidebar">
    <div class="admin-logo">
        <h2>⚓ Admin Panel</h2>
    </div>
    <nav class="admin-nav">
        <ul>
            <li>
                <a href="../index.php">
                    <span class="nav-icon">🏠</span>
                    <?= e(dcs_t('admin.nav.go_to_website')) ?>
                </a>
            </li>
            <li>
                <a href="index.php" <?= $currentPage === 'index.php' ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📊</span>
                    <?= e(dcs_t('admin.nav.dashboard')) ?>
                </a>
            </li>
            <?php if ($navPermissions['view_logs']): ?>
            <li>
                <a href="logs.php" <?= $currentPage === 'logs.php' ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📋</span>
                    <?= e(dcs_t('admin.nav.activity_logs')) ?>
                </a>
            </li>
            <?php endif; ?>
            <?php if ($navPermissions['export_data']): ?>
            <li>
                <a href="export.php" <?= $currentPage === 'export.php' ? 'class="active"' : '' ?>>
                    <span class="nav-icon">📤</span>
                    <?= e(dcs_t('admin.nav.export_data')) ?>
                </a>
            </li>
            <?php endif; ?>
            <?php if ($adminNavState['canShowSettings']): ?>
            <li class="nav-dropdown <?= $isSettingsPage ? 'open' : '' ?>">
                <a href="#" class="nav-dropdown-toggle <?= $isSettingsPage ? 'active' : '' ?>">
                    <span class="nav-icon">⚙️</span>
                    <?= e(dcs_t('admin.nav.settings')) ?>
                    <span class="dropdown-arrow">▼</span>
                </a>
                <ul class="nav-dropdown-menu <?= $isSettingsPage ? 'open' : '' ?>">
                    <?php if ($navPermissions['manage_admins']): ?>
                    <li>
                        <a href="admins.php" <?= $currentPage === 'admins.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔐</span>
                            <?= e(dcs_t('admin.nav.admins')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($navPermissions['manage_permissions']): ?>
                    <li>
                        <a href="permissions.php" <?= $currentPage === 'permissions.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🛡️</span>
                            <?= e(dcs_t('admin.nav.permissions')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($navPermissions['manage_api']): ?>
                    <li>
                        <a href="api_settings.php" <?= $currentPage === 'api_settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔌</span>
                            <?= e(dcs_t('admin.nav.api_settings')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($navPermissions['manage_features']): ?>
                    <li>
                        <a href="settings.php" <?= $currentPage === 'settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🎛️</span>
                            <?= e(dcs_t('admin.nav.site_features')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="custom_links.php" <?= $currentPage === 'custom_links.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔗</span>
                            <?= e(dcs_t('admin.nav.custom_links')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="metadata.php" <?= $currentPage === 'metadata.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔎</span>
                            <?= e(dcs_t('admin.nav.privacy_seo')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="language_settings.php" <?= $currentPage === 'language_settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🌐</span>
                            <?= e(dcs_t('admin.nav.language')) ?>
                        </a>
                    </li>
                    <li>
                        <a href="settings_backup.php" <?= $currentPage === 'settings_backup.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">💾</span>
                            <?= e(dcs_t('admin.nav.settings_backup')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($navPermissions['manage_maintenance']): ?>
                    <li>
                        <a href="maintenance.php" <?= $currentPage === 'maintenance.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🛠️</span>
                            <?= e(dcs_t('admin.nav.maintenance')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($navPermissions['manage_updates']): ?>
                    <li>
                        <a href="update.php" <?= $currentPage === 'update.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🔄</span>
                            <?= e(dcs_t('admin.nav.update')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($navPermissions['manage_discord']): ?>
                    <li>
                        <a href="discord_settings.php" <?= $currentPage === 'discord_settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">🎮</span>
                            <?= e(dcs_t('admin.nav.discord_link')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($navPermissions['manage_squadrons']): ?>
                    <li>
                        <a href="squadron_settings.php" <?= $currentPage === 'squadron_settings.php' ? 'class="active"' : '' ?>>
                            <span class="nav-icon">✈️</span>
                            <?= e(dcs_t('admin.nav.squadron_homepage')) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($navPermissions['manage_themes']): ?>
                    <li>
                        <a href="themes.php" <?= $currentPage === 'themes.php' ? 'class="active"' : '' ?>>
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
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');

    dropdownToggles.forEach(function(toggle, index) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const dropdown = this.parentNode;
            const menu = dropdown.querySelector('.nav-dropdown-menu');
            const arrow = this.querySelector('.dropdown-arrow');

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

            const isCurrentlyOpen = dropdown.classList.contains('open');

            if (isCurrentlyOpen) {
                dropdown.classList.remove('open');
                if (menu) {
                    menu.classList.remove('open');
                }
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            } else {
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
