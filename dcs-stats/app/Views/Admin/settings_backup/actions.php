<div class="settings-note">
    <?= e(dcs_t('admin.settings_backup.note')) ?>
</div>

<div class="backup-grid">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= e(dcs_t('admin.settings_backup.export_settings')) ?></h2>
        </div>
        <p class="text-muted"><?= e(dcs_t('admin.settings_backup.export_text')) ?></p>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="export_settings">
            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.settings_backup.download_button')) ?></button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= e(dcs_t('admin.settings_backup.restore_settings')) ?></h2>
        </div>
        <p class="text-muted"><?= e(dcs_t('admin.settings_backup.restore_text')) ?></p>
        <form method="POST" enctype="multipart/form-data" onsubmit="return confirm(this.dataset.confirmMessage);" data-confirm-message="<?= e(dcs_t('admin.settings_backup.restore_confirm')) ?>">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="import_settings">
            <div class="file-input-row">
                <input type="file" name="settings_file" id="settings_file" accept=".json,application/json" required>
            </div>
            <button type="submit" class="btn btn-warning"><?= e(dcs_t('admin.settings_backup.restore_button')) ?></button>
        </form>
    </div>
</div>
