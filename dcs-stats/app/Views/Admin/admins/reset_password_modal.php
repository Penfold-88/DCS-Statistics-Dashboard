<div id="resetPasswordModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h2><?= e(dcs_t('admin.admins.reset_password')) ?></h2>
            <button type="button" class="modal-close" onclick="closeResetPasswordModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="admin_id" id="resetAdminId">

            <div class="form-group">
                <label><?= e(dcs_t('admin.nav.admins')) ?></label>
                <p id="resetAdminName"></p>
            </div>

            <div class="form-group">
                <label for="new_password"><?= e(dcs_t('admin.admins.new_password')) ?></label>
                <input type="password"
                       name="new_password"
                       id="new_password"
                       class="form-control"
                       required
                       minlength="8">
                <small class="text-muted"><?= e(dcs_t('admin.admins.minimum_8')) ?></small>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.admins.reset_password')) ?></button>
                <button type="button" class="btn btn-secondary" onclick="closeResetPasswordModal()"><?= e(dcs_t('admin.common.cancel')) ?></button>
            </div>
        </form>
    </div>
</div>
