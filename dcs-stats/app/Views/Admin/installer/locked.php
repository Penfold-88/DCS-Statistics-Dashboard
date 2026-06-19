<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(dcs_t('admin.install.already_installed_title')) ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="../css/admin/installer.css">
</head>
<body class="admin-body">
    <div class="installer-locked-card">
        <h1><?= e(dcs_t('admin.install.already_installed_title')) ?></h1>
        <p><?= e(dcs_t('admin.install.already_installed_message')) ?></p>
        <p class="text-muted"><?= e(dcs_t('admin.install.already_installed_delete_note')) ?></p>
        <div class="installer-locked-actions">
            <a href="index.php" class="btn btn-primary"><?= e(dcs_t('admin.install.go_to_dashboard')) ?></a>
            <?php require DCS_APP_PATH . '/Views/Admin/partials/logout_form.php'; ?>
        </div>
    </div>
</body>
</html>
