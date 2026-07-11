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
