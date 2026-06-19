<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/permissions/styles.php'; ?>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php require DCS_ROOT_PATH . '/app/Views/Admin/partials/nav.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1><?= $pageTitle ?></h1>
                <div class="admin-user-menu">
                    <div class="admin-user-info">
                        <div class="admin-username"><?= e($currentAdmin['username']) ?></div>
                        <div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div>
                    </div>
                    <?php require DCS_APP_PATH . '/Views/Admin/partials/logout_form.php'; ?>
                </div>
            </header>

            <div class="admin-content">
                <div class="card">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($demoRestricted): ?>
                        <div class="alert alert-warning">
                            <?= e(demoRestrictionMessage()) ?>
                        </div>
                    <?php endif; ?>

                    <div class="permissions-info">
                        <strong><?= e(dcs_t('admin.permissions.about_title')) ?>:</strong><br>
                        <?= e(dcs_t('admin.permissions.about_text')) ?>
                    </div>

                    <?php require DCS_ROOT_PATH . '/app/Views/Admin/permissions/form.php'; ?>
                </div>
            </div>
        </main>
    </div>

    <?php require DCS_ROOT_PATH . '/app/Views/Admin/permissions/form_script.php'; ?>
</body>
</html>
