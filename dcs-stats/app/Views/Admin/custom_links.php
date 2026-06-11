<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .custom-links-editor {
            display: grid;
            gap: 14px;
            margin-top: 16px;
        }

        .custom-link-row {
            align-items: end;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(150px, 1fr) minmax(220px, 1.4fr) auto auto auto;
            padding: 14px;
        }

        .custom-link-row .form-group {
            margin: 0;
        }

        .custom-link-check {
            align-items: center;
            display: flex;
            gap: 8px;
            min-height: 38px;
            white-space: nowrap;
        }

        .custom-links-note {
            background-color: rgba(76, 175, 80, 0.08);
            border: 1px solid rgba(76, 175, 80, 0.28);
            border-radius: 6px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 20px;
            padding: 15px;
        }

        @media screen and (max-width: 900px) {
            .custom-link-row {
                grid-template-columns: 1fr;
            }
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

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/custom_links/editor.php'; ?>
            </div>
        </main>
    </div>

    <?php require DCS_ROOT_PATH . '/app/Views/Admin/custom_links/editor_script.php'; ?>
</body>
</html>
