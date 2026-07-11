<form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="update">
    <div class="form-group">
        <label>
            <input type="checkbox" name="enabled" <?= $maintenance['enabled'] ? 'checked' : '' ?>>
            <?= e(dcs_t('admin.maintenance.enable_mode')) ?>
        </label>
    </div>
    <div class="form-group">
        <label for="ip_address"><?= e(dcs_t('admin.maintenance.whitelist_ip')) ?></label>
        <div class="d-flex gap-1">
            <input type="text" name="ip_address" id="ip_address" class="form-control" placeholder="127.0.0.1">
            <button type="button" class="btn btn-secondary" onclick="autofillIP()"><?= e(dcs_t('admin.maintenance.use_my_ip')) ?></button>
        </div>
    </div>
    <div class="btn-group">
        <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.settings.save_settings')) ?></button>
    </div>
</form>
