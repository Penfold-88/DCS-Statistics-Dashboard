<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.admins.users', ['count' => count($admins)])) ?></h2>
    </div>

    <?php foreach ($admins as $admin): ?>
        <?php $isProtectedAdmin = !empty($protectedAdminIds[(int)($admin['id'] ?? 0)]); ?>
        <div class="admin-card">
            <div class="admin-info">
                <h3>
                    <?= e($admin['username']) ?>
                    <?= getRoleBadge($admin['role']) ?>
                    <span class="admin-status <?= $admin['is_active'] ? 'status-active' : 'status-inactive' ?>">
                        <?= e($admin['is_active'] ? dcs_t('admin.admins.active') : dcs_t('admin.admins.inactive')) ?>
                    </span>
                </h3>
                <div class="admin-meta">
                    <?= e(dcs_t('admin.admins.email')) ?>: <?= e($admin['email']) ?><br>
                    <?= e(dcs_t('admin.admins.created')) ?>: <?= formatDate($admin['created_at']) ?><br>
                    <?= e(dcs_t('admin.admins.last_login')) ?>: <?= formatDate($admin['last_login']) ?>
                    <?php if ($admin['failed_attempts'] > 0): ?>
                        <br><span class="text-warning"><?= e(dcs_t('admin.admins.failed_attempts')) ?>: <?= $admin['failed_attempts'] ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="btn-group">
                <?php if ($admin['id'] != $_SESSION['admin_id'] && !$isProtectedAdmin): ?>
                    <form method="POST" action="" style="display: inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="toggle_active">
                        <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                        <button type="submit" class="btn btn-secondary btn-small">
                            <?= e($admin['is_active'] ? dcs_t('admin.admins.deactivate') : dcs_t('admin.admins.activate')) ?>
                        </button>
                    </form>

                    <button type="button"
                            class="btn btn-secondary btn-small"
                            onclick='showResetPasswordModal(<?= $admin['id'] ?>, <?= json_encode($admin['username']) ?>)'>
                        <?= e(dcs_t('admin.admins.reset_password')) ?>
                    </button>

                    <form method="POST" action="" style="display: inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="remove_admin">
                        <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                        <button type="submit"
                                class="btn btn-danger btn-small"
                                onclick='return confirm(<?= json_encode(dcs_t('admin.admins.confirm_remove') . ' ' . $admin['username'] . '? ' . dcs_t('admin.admins.cannot_undone')) ?>)'>
                            <?= e(dcs_t('admin.admins.remove')) ?>
                        </button>
                    </form>
                <?php elseif ($isProtectedAdmin): ?>
                    <span class="text-muted">Protected account</span>
                <?php else: ?>
                    <span class="text-muted"><?= e(dcs_t('admin.admins.current_user')) ?></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
