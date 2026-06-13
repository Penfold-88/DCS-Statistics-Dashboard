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
