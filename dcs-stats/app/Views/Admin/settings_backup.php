<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <?php if (!$demoRestricted): ?>
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/settings_backup/styles.php'; ?>
    <?php endif; ?>
</head>
<body>
<div class="admin-wrapper">
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/partials/nav.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <h1><?= e($pageTitle) ?></h1>
            <div class="admin-user-menu">
                <div class="admin-user-info">
                    <div class="admin-username"><?= e($currentAdmin['username']) ?></div>
                    <div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div>
                </div>
                <a href="logout.php" class="btn btn-secondary btn-small"><?= e(dcs_t('admin.common.logout')) ?></a>
            </div>
        </header>

        <div class="admin-content">
            <?php if ($demoRestricted): ?>
                <div class="card">
                    <div class="alert alert-warning">
                        Demo mode is enabled. Settings backup and restore are locked on the public demo.
                    </div>
                </div>
            <?php else: ?>
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>">
                        <?= e($message) ?>
                    </div>
                <?php endif; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/settings_backup/actions.php'; ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/settings_backup/contents.php'; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
