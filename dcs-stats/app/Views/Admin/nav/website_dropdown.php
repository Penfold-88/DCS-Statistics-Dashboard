<?php if ($adminNavState['canShowWebsite']): ?>
<li class="nav-dropdown <?= $isWebsitePage ? 'open' : '' ?>">
    <a href="#" class="nav-dropdown-toggle <?= $isWebsitePage ? 'active' : '' ?>">
        <span class="nav-icon">🌐</span>
        <span class="nav-dropdown-label"><?= e(dcs_t('admin.nav.website_options')) ?></span>
        <span class="dropdown-arrow">▼</span>
    </a>
    <ul class="nav-dropdown-menu <?= $isWebsitePage ? 'open' : '' ?>">
        <?php if ($navPermissions['manage_themes']): ?>
        <li>
            <a href="themes.php" <?= $currentPage === 'themes.php' ? 'class="active"' : '' ?>>
                <span class="nav-icon">🎨</span>
                <?= e(dcs_t('admin.nav.themes')) ?>
            </a>
        </li>
        <?php endif; ?>
        <?php if ($navPermissions['manage_features']): ?>
        <li>
            <a href="menu_manager.php" <?= $currentPage === 'menu_manager.php' ? 'class="active"' : '' ?>>
                <span class="nav-icon">☰</span>
                <?= e(dcs_t('admin.nav.menu_manager')) ?>
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
    </ul>
</li>
<?php endif; ?>
