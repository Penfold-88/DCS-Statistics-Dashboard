<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/export/styles.php'; ?>
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
                    <a href="logout.php" class="btn btn-secondary btn-small">Logout</a>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($demoRestricted): ?>
                    <div class="alert alert-warning">
                        Demo mode is enabled. Data exports are locked on the public demo.
                    </div>
                <?php endif; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/export/options.php'; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/export/recent_exports.php'; ?>
            </div>
        </main>
    </div>

    <?php require DCS_ROOT_PATH . '/app/Views/Admin/export/date_defaults_script.php'; ?>
</body>
</html>
