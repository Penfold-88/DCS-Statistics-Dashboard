<?php if ($adminNavState['canShowStatistics']): ?>
<li class="nav-dropdown <?= $isStatisticsPage ? 'open' : '' ?>">
    <a href="#" class="nav-dropdown-toggle <?= $isStatisticsPage ? 'active' : '' ?>">
        <span class="nav-icon">📊</span>
        <span class="nav-dropdown-label"><?= e(dcs_t('admin.nav.statistics_options')) ?></span>
        <span class="dropdown-arrow">▼</span>
    </a>
    <ul class="nav-dropdown-menu <?= $isStatisticsPage ? 'open' : '' ?>">
        <?php if ($navPermissions['manage_features']): ?>
        <li>
            <a href="settings.php" <?= $currentPage === 'settings.php' ? 'class="active"' : '' ?>>
                <span class="nav-icon">🎛️</span>
                <?= e(dcs_t('admin.nav.statistics_features')) ?>
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
        <li>
            <a href="api_health.php" <?= $currentPage === 'api_health.php' ? 'class="active"' : '' ?>>
                <span class="nav-icon">🩺</span>
                <?= e(dcs_t('admin.nav.api_health')) ?>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</li>
<?php endif; ?>
