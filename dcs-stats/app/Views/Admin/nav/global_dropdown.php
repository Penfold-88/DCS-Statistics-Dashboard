<?php if ($adminNavState['canShowGlobal']): ?>
<li class="nav-dropdown <?= $isGlobalPage ? 'open' : '' ?>">
    <a href="#" class="nav-dropdown-toggle <?= $isGlobalPage ? 'active' : '' ?>">
        <span class="nav-icon">⚙️</span>
        <span class="nav-dropdown-label"><?= e(dcs_t('admin.nav.global_options')) ?></span>
        <span class="dropdown-arrow">▼</span>
    </a>
    <ul class="nav-dropdown-menu <?= $isGlobalPage ? 'open' : '' ?>">
        <?php if ($navPermissions['manage_features']): ?>
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
    </ul>
</li>
<?php endif; ?>
