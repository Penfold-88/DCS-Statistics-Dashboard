<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <?php if (!$demoRestricted): ?>
    <style>
        .backup-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .settings-note {
            background-color: rgba(76, 175, 80, 0.08);
            border: 1px solid rgba(76, 175, 80, 0.28);
            border-radius: 6px;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 20px;
            padding: 15px;
        }

        .included-list,
        .excluded-list {
            display: grid;
            gap: 8px;
            margin: 12px 0 0;
            padding-left: 18px;
        }

        .file-input-row {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 14px 0;
        }
    </style>
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
