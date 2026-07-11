<div id="restoreModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?= e(dcs_t('admin.update.restore_backup')) ?></h3>
            <button class="modal-close" onclick="closeModal('restoreModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p><?= e(dcs_t('admin.update.select_backup_restore')) ?></p>
            <div id="restore-backup-list"><?= e(dcs_t('admin.update.loading_backups')) ?></div>
        </div>
    </div>
</div>

<div id="downgradeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?= e(dcs_t('admin.update.downgrade_version')) ?></h3>
            <button class="modal-close" onclick="closeModal('downgradeModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="downgrade-form">
                <div class="form-group">
                    <label for="downgrade-version"><?= e(dcs_t('admin.update.select_version')) ?></label>
                    <select id="downgrade-version" class="form-control">
                        <option value=""><?= e(dcs_t('admin.update.loading_versions')) ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <p class="text-muted"><?= e(dcs_t('admin.update.auto_backup_before_downgrade')) ?></p>
                </div>
                <button type="submit" class="btn btn-warning"><?= e(dcs_t('admin.update.downgrade')) ?></button>
            </form>
        </div>
    </div>
</div>
