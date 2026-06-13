<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(dcs_t('admin.login.title')) ?> - DCS Statistics</title>
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/login/styles.php'; ?>
</head>
<body>
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/login/form.php'; ?>

    <?php require DCS_ROOT_PATH . '/app/Views/Admin/login/script.php'; ?>
</body>
</html>
