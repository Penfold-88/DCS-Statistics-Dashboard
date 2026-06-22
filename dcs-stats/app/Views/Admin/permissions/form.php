<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

    <div class="quick-actions">
        <button type="button" class="btn btn-sm" onclick="selectAll()" <?= $demoRestricted ? 'disabled' : '' ?>><?= e(dcs_t('admin.permissions.select_all')) ?></button>
        <button type="button" class="btn btn-sm" onclick="selectNone()" <?= $demoRestricted ? 'disabled' : '' ?>><?= e(dcs_t('admin.permissions.select_none')) ?></button>
        <button type="button" class="btn btn-sm" onclick="selectDefault()" <?= $demoRestricted ? 'disabled' : '' ?>><?= e(dcs_t('admin.themes.reset_to_default')) ?></button>
    </div>

    <div class="section-header">
        <h3><?= e(dcs_t('admin.permissions.core_permissions')) ?></h3>
        <span class="permission-count" id="core-count">0</span>
    </div>

    <div class="permissions-grid" id="core-permissions">
        <?php
        $corePerms = ['view_dashboard', 'export_data', 'view_logs'];
        foreach ($corePerms as $key):
            if (isset($lsoPermissions[$key])):
                $perm = $lsoPermissions[$key];
        ?>
        <div class="permission-item <?= $perm['enabled'] ? 'enabled' : '' ?> <?= $demoRestricted ? 'is-demo-locked' : '' ?>" data-perm="<?= $key ?>">
            <div class="permission-checkbox">
                <input type="checkbox"
                       name="permissions[]"
                       value="<?= $key ?>"
                       id="perm_<?= $key ?>"
                       <?= $perm['enabled'] ? 'checked' : '' ?>
                       <?= $demoRestricted ? 'disabled' : '' ?>
                       onchange="updatePermissionUI(this)">
            </div>
            <div class="permission-details">
                <label for="perm_<?= $key ?>" class="permission-label">
                    <?= htmlspecialchars($perm['label']) ?>
                </label>
                <div class="permission-description">
                    <?= htmlspecialchars($perm['description']) ?>
                </div>
            </div>
        </div>
        <?php endif; endforeach; ?>
    </div>

    <div class="section-header">
        <h3><?= e(dcs_t('admin.permissions.management_permissions')) ?></h3>
        <span class="permission-count" id="mgmt-count">0</span>
    </div>

    <div class="permissions-grid" id="mgmt-permissions">
        <?php
        $mgmtPerms = ['manage_api', 'manage_features', 'manage_themes', 'manage_pages', 'manage_discord', 'manage_squadrons', 'manage_maintenance', 'manage_updates', 'change_settings'];
        foreach ($mgmtPerms as $key):
            if (isset($lsoPermissions[$key])):
                $perm = $lsoPermissions[$key];
        ?>
        <div class="permission-item <?= $perm['enabled'] ? 'enabled' : '' ?> <?= $demoRestricted ? 'is-demo-locked' : '' ?>" data-perm="<?= $key ?>">
            <div class="permission-checkbox">
                <input type="checkbox"
                       name="permissions[]"
                       value="<?= $key ?>"
                       id="perm_<?= $key ?>"
                       <?= $perm['enabled'] ? 'checked' : '' ?>
                       <?= $demoRestricted ? 'disabled' : '' ?>
                       onchange="updatePermissionUI(this)">
            </div>
            <div class="permission-details">
                <label for="perm_<?= $key ?>" class="permission-label">
                    <?= htmlspecialchars($perm['label']) ?>
                </label>
                <div class="permission-description">
                    <?= htmlspecialchars($perm['description']) ?>
                </div>
            </div>
        </div>
        <?php endif; endforeach; ?>
    </div>

    <button type="submit" class="btn btn-primary" style="margin-top: 30px;" <?= $demoRestricted ? 'disabled' : '' ?>><?= e(dcs_t('admin.permissions.save_permissions')) ?></button>
</form>
