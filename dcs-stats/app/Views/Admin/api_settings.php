<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/api_settings/styles.php'; ?>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php require DCS_ROOT_PATH . '/app/Views/Admin/partials/nav.php'; ?>

        <main class="admin-main">
            <div class="admin-content">
                <?php if ($message): ?>
                    <div class="alert alert-<?= e($messageType) ?>">
                        <?= e($message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($autoFixMessage): ?>
                    <div class="alert alert-info">
                        <?= e($autoFixMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if ($demoRestricted): ?>
                    <div class="alert alert-info">
                        <?= e(demoRestrictionMessage()) ?>
                    </div>
                <?php endif; ?>

                <?php if ($envApiKeyActive): ?>
                    <div class="alert alert-info">
                        <?= e(dcs_t('admin.api.env_key_active')) ?>
                    </div>
                <?php endif; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/api_settings/configuration_form.php'; ?>

                <?php if ($testResult): ?>
                    <div class="test-section">
                        <h3><?= e(dcs_t('admin.api.connection_test_result')) ?></h3>
                        <div class="test-result <?= $testResult['success'] ? 'success' : 'error' ?>">
                            <?= e($testResult['message']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/api_settings/endpoints_info.php'; ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/api_settings/form_script.php'; ?>
            </div>
        </main>
    </div>
</body>
</html>
