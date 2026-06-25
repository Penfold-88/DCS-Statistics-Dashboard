<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.admins.add_new')) ?></h2>
    </div>

    <form method="POST" action="" autocomplete="off">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_admin">
        <input type="text" name="browser_autofill_username" autocomplete="username" style="position:absolute; left:-9999px; width:1px; height:1px;" tabindex="-1" aria-hidden="true">
        <input type="password" name="browser_autofill_password" autocomplete="current-password" style="position:absolute; left:-9999px; width:1px; height:1px;" tabindex="-1" aria-hidden="true">

        <div class="form-group">
            <label for="username"><?= e(dcs_t('admin.admins.username')) ?></label>
            <input type="text"
                   id="username"
                   name="username"
                   class="form-control"
                   required
                   pattern="[a-zA-Z0-9_]{3,50}"
                   value=""
                   autocomplete="new-password"
                   title="3-50 characters, letters, numbers and underscore only">
        </div>

        <div class="form-group">
            <label for="email"><?= e(dcs_t('admin.admins.email')) ?></label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control"
                   value=""
                   autocomplete="off"
                   required>
        </div>

        <div class="form-group">
            <label for="password"><?= e(dcs_t('admin.admins.password')) ?></label>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control"
                   required
                   minlength="8"
                   value=""
                   autocomplete="new-password">
            <small class="text-muted"><?= e(dcs_t('admin.admins.minimum_8')) ?></small>
        </div>

        <div class="form-group">
            <label for="role"><?= e(dcs_t('admin.admins.role')) ?></label>
            <select id="role" name="role" class="form-control">
                <option value="<?= ROLE_LSO ?>"><?= e(dcs_t('admin.admins.role_lso')) ?></option>
                <?php if ($currentAdmin['role'] == ROLE_AIR_BOSS): ?>
                    <option value="<?= ROLE_AIR_BOSS ?>"><?= e(dcs_t('admin.admins.role_air_boss')) ?></option>
                <?php endif; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.admins.add_admin')) ?></button>
    </form>
</div>
