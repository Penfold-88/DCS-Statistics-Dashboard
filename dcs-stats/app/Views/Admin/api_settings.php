<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .api-form {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--accent-primary);
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 10px;
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: 4px;
            color: var(--text-primary);
            font-size: 14px;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus,
        .form-group input[type="number"]:focus,
        .form-group select:focus {
            border-color: var(--accent-primary);
            outline: none;
        }

        .form-group .help-text {
            margin-top: 5px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }

        .test-section {
            background-color: var(--bg-tertiary);
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }

        .test-result {
            margin-top: 15px;
            padding: 15px;
            border-radius: 4px;
        }

        .test-result.success {
            background-color: rgba(76, 175, 80, 0.2);
            border: 1px solid var(--accent-success);
            color: var(--accent-success);
        }

        .test-result.error {
            background-color: rgba(244, 67, 54, 0.2);
            border: 1px solid var(--accent-danger);
            color: var(--accent-danger);
        }

        .endpoints-info {
            background-color: var(--bg-tertiary);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .endpoints-info h4 {
            margin-bottom: 15px;
            color: var(--accent-primary);
        }

        .endpoints-list {
            font-family: monospace;
            font-size: 13px;
            line-height: 1.8;
        }

        .endpoints-list ul {
            margin: 8px 0 18px;
            padding-left: 20px;
        }

        .endpoints-list code {
            color: var(--accent-primary);
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 250px; flex-shrink: 0; background: #2a2a2a; }
        .admin-main { flex: 1; min-width: 0; overflow-x: hidden; }
        .admin-content { padding: 30px; max-width: 100%; overflow-x: hidden; }
        .card { max-width: 100%; overflow-x: auto; }
    </style>
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
