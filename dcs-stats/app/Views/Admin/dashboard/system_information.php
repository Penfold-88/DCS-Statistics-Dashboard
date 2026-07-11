<!-- System Information -->
<div class="card compact-card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.dashboard.system_information')) ?></h2>
    </div>
    <table class="data-table system-table">
        <tr>
            <td><?= e(dcs_t('admin.dashboard.admin_panel_version')) ?></td>
            <td><?= ADMIN_PANEL_VERSION ?></td>
        </tr>
        <tr>
            <td><?= e(dcs_t('admin.dashboard.php_version')) ?></td>
            <td><?= phpversion() ?></td>
        </tr>
        <tr>
            <td><?= e(dcs_t('admin.dashboard.storage_mode')) ?></td>
            <td><?= e(USE_DATABASE ? dcs_t('admin.dashboard.database') : dcs_t('admin.dashboard.file_based')) ?></td>
        </tr>
        <tr>
            <td><?= e(dcs_t('admin.dashboard.data_directory')) ?></td>
            <td title="<?= htmlspecialchars(ADMIN_DATA_DIR) ?>"><?= basename(rtrim(ADMIN_DATA_DIR, '/')) ?>/</td>
        </tr>
        <tr>
            <td><?= e(dcs_t('admin.dashboard.log_retention')) ?></td>
            <td><?= e(dcs_t('admin.dashboard.days', ['count' => LOG_RETENTION_DAYS])) ?></td>
        </tr>
    </table>
</div>
