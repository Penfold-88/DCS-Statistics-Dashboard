<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(dcs_t('admin.install.already_installed_title')) ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        body { align-items: center; display: flex; justify-content: center; min-height: 100vh; padding: 24px; }
        .installer-locked-card { background: var(--bg-secondary, #252525); border: 1px solid var(--border-color, #444); border-radius: 8px; max-width: 560px; padding: 28px; text-align: center; width: 100%; }
        .installer-locked-card h1 { margin-top: 0; }
        .installer-locked-actions { display: flex; gap: 12px; justify-content: center; margin-top: 22px; flex-wrap: wrap; }
    </style>
</head>
<body class="admin-body">
    <div class="installer-locked-card">
        <h1><?= e(dcs_t('admin.install.already_installed_title')) ?></h1>
        <p><?= e(dcs_t('admin.install.already_installed_message')) ?></p>
        <p class="text-muted"><?= e(dcs_t('admin.install.already_installed_delete_note')) ?></p>
        <div class="installer-locked-actions">
            <a href="index.php" class="btn btn-primary"><?= e(dcs_t('admin.install.go_to_dashboard')) ?></a>
            <a href="logout.php" class="btn btn-secondary"><?= e(dcs_t('admin.common.logout')) ?></a>
        </div>
    </div>
</body>
</html>
