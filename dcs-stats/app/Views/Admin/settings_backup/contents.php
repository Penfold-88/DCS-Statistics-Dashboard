<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.settings_backup.backup_contents')) ?></h2>
    </div>
    <div class="backup-grid">
        <div>
            <h3><?= e(dcs_t('admin.settings_backup.included')) ?></h3>
            <ul class="included-list">
                <?php foreach ($backupPreview['includes'] as $item): ?>
                    <li><?= e($backupPreviewLabels['includes'][$item] ?? $item) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h3><?= e(dcs_t('admin.settings_backup.excluded')) ?></h3>
            <ul class="excluded-list">
                <?php foreach ($backupPreview['excluded'] as $item): ?>
                    <li><?= e($backupPreviewLabels['excluded'][$item] ?? $item) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
