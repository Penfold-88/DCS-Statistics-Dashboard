<!-- Quick Actions -->
<div class="card compact-card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.dashboard.quick_actions')) ?></h2>
    </div>
    <div class="quick-action-grid">
        <?php if (hasPermission('manage_admins')): ?>
            <a href="admins.php" class="btn btn-primary"><?= e(dcs_t('admin.dashboard.manage_admins')) ?></a>
        <?php endif; ?>
        <?php if (hasPermission('manage_features')): ?>
            <a href="settings.php" class="btn btn-secondary"><?= e(dcs_t('admin.nav.site_features')) ?></a>
            <a href="settings_backup.php" class="btn btn-secondary"><?= e(dcs_t('admin.nav.settings_backup')) ?></a>
        <?php endif; ?>
        <?php if (hasPermission('manage_api')): ?>
            <a href="api_settings.php" class="btn btn-secondary"><?= e(dcs_t('admin.nav.api_settings')) ?></a>
        <?php endif; ?>
        <?php if (hasPermission('manage_themes')): ?>
            <a href="themes.php" class="btn btn-secondary"><?= e(dcs_t('admin.nav.themes')) ?></a>
        <?php endif; ?>
        <?php if (hasPermission('manage_updates')): ?>
            <a href="update.php" class="btn btn-secondary"><?= e(dcs_t('admin.dashboard.updates')) ?></a>
        <?php endif; ?>
        <?php if (hasPermission('view_logs')): ?>
            <a href="logs.php" class="btn btn-secondary"><?= e(dcs_t('admin.dashboard.view_logs')) ?></a>
        <?php endif; ?>
        <?php if (hasPermission('export_data')): ?>
            <a href="export.php" class="btn btn-secondary"><?= e(dcs_t('admin.nav.export_data')) ?></a>
        <?php endif; ?>
    </div>
</div>
