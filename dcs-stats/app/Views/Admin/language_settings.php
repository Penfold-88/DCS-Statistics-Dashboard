<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 250px; flex-shrink: 0; background: #2a2a2a; }
        .admin-main { flex: 1; min-width: 0; overflow-x: hidden; }
        .admin-content { padding: 30px; max-width: 100%; overflow-x: hidden; }
        .language-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            margin: 18px 0 24px;
        }
        .language-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: 8px;
            padding: 14px;
        }
        .language-card strong {
            color: var(--text-primary);
            display: block;
            font-size: 1rem;
            margin-bottom: 6px;
        }
        .language-meta {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .language-badge {
            background: var(--accent-primary);
            border-radius: 999px;
            color: var(--bg-primary);
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            margin-top: 10px;
            padding: 4px 9px;
            text-transform: uppercase;
        }
        .language-actions {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            color: var(--accent-primary);
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .form-control {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: 4px;
            color: var(--text-primary);
            max-width: 420px;
            padding: 10px;
            width: 100%;
        }
        .help-text {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-top: 8px;
        }
        .template-link {
            color: var(--accent-primary);
            display: inline-block;
            font-weight: 700;
            margin-bottom: 14px;
            text-decoration: none;
        }
    </style>
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
