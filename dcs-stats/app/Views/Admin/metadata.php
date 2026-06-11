<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .metadata-help {
            background-color: rgba(76, 175, 80, 0.08);
            border: 1px solid rgba(76, 175, 80, 0.28);
            border-radius: 6px;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 20px;
            padding: 15px;
        }

        .character-count {
            color: var(--text-muted);
            display: block;
            font-size: 12px;
            margin-top: 6px;
        }

        textarea.form-control {
            min-height: 110px;
            resize: vertical;
        }

        .usage-data-disclaimer {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.5;
            margin: 18px 0 0;
        }
    </style>
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
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($demoRestricted): ?>
                <div class="alert alert-warning">
                    <?= e(demoRestrictionMessage()) ?>
                </div>
            <?php endif; ?>

            <?php require DCS_ROOT_PATH . '/app/Views/Admin/metadata/form.php'; ?>
        </div>
    </main>
</div>
</body>
</html>
