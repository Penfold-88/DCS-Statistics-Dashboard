<form method="POST" action="logout.php" style="display: inline;">
    <?= csrfField() ?>
    <button type="submit" class="btn btn-secondary btn-small"><?= e(dcs_t('admin.common.logout')) ?></button>
</form>
