<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/language_settings/styles.php'; ?>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php require DCS_ROOT_PATH . '/app/Views/Admin/partials/nav.php'; ?>

        <main class="admin-main">
            <div class="admin-content">
                <?php if ($message): ?>
                    <div class="alert alert-<?= e($messageType) ?>"><?= e($message) ?></div>
                <?php endif; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/language_settings/languages.php'; ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/language_settings/actions.php'; ?>
            </div>
        </main>
    </div>
</body>
</html>
