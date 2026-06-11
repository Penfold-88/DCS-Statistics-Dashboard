<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 250px; flex-shrink: 0; background: #2a2a2a; }
        .admin-main { flex: 1; min-width: 0; overflow-x: hidden; }
        .admin-content { padding: 30px; max-width: 100%; overflow-x: hidden; }
        .card { max-width: 100%; overflow-x: auto; }
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
            .admin-wrapper { flex-direction: column; }
            .admin-content { padding: 15px; }
        }
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .log-entry {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .log-entry:last-child {
            border-bottom: none;
        }
        .log-entry:hover {
            background-color: var(--bg-tertiary);
        }
        .log-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .log-action {
            font-weight: bold;
            color: var(--accent-primary);
        }
        .log-time {
            color: var(--text-muted);
            font-size: 12px;
        }
        .log-details {
            font-size: 14px;
            color: var(--text-secondary);
        }
        .log-meta {
            margin-top: 8px;
            font-size: 12px;
            color: var(--text-muted);
        }
        .action-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .action-login { background-color: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .action-logout { background-color: rgba(158, 158, 158, 0.2); color: #9E9E9E; }
        .action-ban { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }
        .action-unban { background-color: rgba(255, 152, 0, 0.2); color: #ff9800; }
        .action-export { background-color: rgba(33, 150, 243, 0.2); color: #2196F3; }
        .action-edit { background-color: rgba(156, 39, 176, 0.2); color: #9c27b0; }
    </style>
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
                    <a href="logout.php" class="btn btn-secondary btn-small">Logout</a>
                </div>
            </header>

            <div class="admin-content">
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/logs/filters.php'; ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/logs/entries.php'; ?>
            </div>
        </main>
    </div>
</body>
</html>
