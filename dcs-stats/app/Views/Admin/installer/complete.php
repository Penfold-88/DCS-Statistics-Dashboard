<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(dcs_t('admin.install.complete_page_title')) ?> - DCS Statistics</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="../css/admin/installer.css">
</head>
<body>
    <div class="install-container install-container--complete">
        <div class="card text-center">
            <div class="success-icon">✓</div>
            <h1 class="text-success mb-3"><?= e(dcs_t('admin.install.complete_title')) ?></h1>

            <div class="card mb-3">
                <h3 class="card-title"><?= e(dcs_t('admin.install.account_created')) ?></h3>
                <p><strong><?= e(dcs_t('admin.install.username')) ?>:</strong> <?= htmlspecialchars($username) ?></p>
                <p><strong><?= e(dcs_t('admin.install.email')) ?>:</strong> <?= htmlspecialchars($email) ?></p>
                <p class="text-muted"><strong><?= e(dcs_t('admin.install.password')) ?>:</strong> <?= e(dcs_t('admin.install.password_entered')) ?></p>
            </div>

            <div class="card mb-3">
                <h3 class="card-title"><?= e(dcs_t('admin.install.api_configuration')) ?>:</h3>
                <p><strong><?= e(dcs_t('admin.install.api_endpoint')) ?>:</strong> <?= htmlspecialchars($api_url) ?></p>
                <p><strong><?= e(dcs_t('admin.install.site_name')) ?>:</strong> <?= htmlspecialchars($site_name) ?></p>
                <?php if (!empty($discord_url)): ?>
                <p><strong>Discord:</strong> <?= htmlspecialchars($discord_url) ?></p>
                <?php endif; ?>
            </div>

            <a href="login.php" class="btn btn-primary" style="width: 100%;"><?= e(dcs_t('admin.install.go_to_login')) ?></a>

            <?php if ($installerSelfDeleteStatus === 'removed'): ?>
                <div class="alert alert-success mt-3">
                    <strong><?= e(dcs_t('admin.install.security_notice')) ?>:</strong><br>
                    <?= e(dcs_t('admin.install.security_notice_removed')) ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-3">
                    <strong><?= e(dcs_t('admin.install.security_notice')) ?>:</strong><br>
                    <?= e(dcs_t('admin.install.security_notice_text')) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?= getDevModeIndicator() ?>
</body>
</html>
