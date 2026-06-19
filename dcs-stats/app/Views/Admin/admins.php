<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/admins/styles.php'; ?>
</head>
<body>
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
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= e($message) ?>
                    </div>
                <?php endif; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/admins/add_form.php'; ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/admins/users_list.php'; ?>
            </div>
        </main>
    </div>

    <?php require DCS_ROOT_PATH . '/app/Views/Admin/admins/reset_password_modal.php'; ?>
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/admins/reset_password_script.php'; ?>
</body>
</html>
