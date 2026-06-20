<?php if ($adminNavState['canShowCms']): ?>
<li class="nav-dropdown <?= $isCmsPage ? 'open' : '' ?>">
    <a href="#" class="nav-dropdown-toggle <?= $isCmsPage ? 'active' : '' ?>">
        <span class="nav-icon">🧱</span>
        <span class="nav-dropdown-label"><?= e(dcs_t('admin.nav.cms_options')) ?></span>
        <span class="dropdown-arrow">▼</span>
    </a>
    <ul class="nav-dropdown-menu <?= $isCmsPage ? 'open' : '' ?>">
        <li><a href="cms_settings.php" <?= $currentPage === 'cms_settings.php' ? 'class="active"' : '' ?>><span class="nav-icon">⚙️</span><?= e(dcs_t('admin.cms.settings')) ?></a></li>
        <?php if ($adminNavState['cmsEnabled']): ?>
        <li><a href="cms_pages.php" <?= $currentPage === 'cms_pages.php' ? 'class="active"' : '' ?>><span class="nav-icon">📄</span><?= e(dcs_t('admin.cms.pages_title')) ?></a></li>
        <?php endif; ?>
    </ul>
</li>
<?php endif; ?>
