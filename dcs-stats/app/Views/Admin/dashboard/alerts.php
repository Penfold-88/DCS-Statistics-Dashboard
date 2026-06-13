<!-- Welcome Message -->
<div class="alert alert-info">
    <?= e(dcs_t('admin.dashboard.welcome', ['user' => $currentAdmin['username']])) ?>
    <?= e(dcs_t('admin.dashboard.last_watch')) ?>: <?= formatDate($currentAdmin['last_login']) ?>
</div>

<?php if ($installDeleteMessage !== ''): ?>
<div class="alert alert-<?= e($installDeleteMessageType) ?>">
    <?= e($installDeleteMessage) ?>
</div>
<?php endif; ?>

<?php if ($installFilePresent): ?>
<div class="alert alert-warning install-file-warning">
    <div>
        <strong><?= e(dcs_t('admin.dashboard.install_file_warning_title')) ?>:</strong>
        <?= e(dcs_t('admin.dashboard.install_file_warning_text')) ?>
    </div>
    <?php if (hasPermission('change_settings')): ?>
    <form method="POST" action="index.php" onsubmit='return confirm(<?= json_encode(dcs_t('admin.dashboard.install_file_delete_confirm')) ?>);'>
        <?= csrfField() ?>
        <input type="hidden" name="action" value="delete_installer">
        <button type="submit" class="btn btn-danger btn-small"><?= e(dcs_t('admin.dashboard.install_file_delete_button')) ?></button>
    </form>
    <?php endif; ?>
</div>
<?php endif; ?>
