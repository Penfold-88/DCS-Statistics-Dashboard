<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .export-option {
            background-color: var(--bg-tertiary);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .export-option h3 {
            margin-bottom: 10px;
            color: var(--accent-primary);
        }

        .export-option p {
            color: var(--text-muted);
            margin-bottom: 15px;
            font-size: 14px;
        }

        .export-fields {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
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
                    <a href="logout.php" class="btn btn-secondary btn-small">Logout</a>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($demoRestricted): ?>
                    <div class="alert alert-warning">
                        Demo mode is enabled. Data exports are locked on the public demo.
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Export Options</h2>
                    </div>

                    <div class="export-option">
                        <h3>Player Data</h3>
                        <p>Export player information including names, UCIDs, and statistics.</p>

                        <form method="POST" action="">
                            <?= csrfField() ?>
                            <input type="hidden" name="export_type" value="players">

                            <div class="export-fields">
                                <div class="form-group">
                                    <label for="players_format">Format</label>
                                    <select name="format" id="players_format" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                                        <option value="csv">CSV</option>
                                        <option value="json">JSON</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="players_date_from">From Date (Optional)</label>
                                    <input type="date" name="date_from" id="players_date_from" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                                </div>

                                <div class="form-group">
                                    <label for="players_date_to">To Date (Optional)</label>
                                    <input type="date" name="date_to" id="players_date_to" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                                </div>
                            </div>

                            <button type="submit" name="export" class="btn btn-primary" <?= $demoRestricted ? 'disabled' : '' ?>>
                                Export Player Data
                            </button>
                        </form>
                    </div>

                    <div class="export-option">
                        <h3>Mission Statistics</h3>
                        <p>Export detailed mission events including kills, deaths, and flight data.</p>

                        <form method="POST" action="">
                            <?= csrfField() ?>
                            <input type="hidden" name="export_type" value="missions">

                            <div class="export-fields">
                                <div class="form-group">
                                    <label for="missions_format">Format</label>
                                    <select name="format" id="missions_format" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                                        <option value="csv">CSV</option>
                                        <option value="json">JSON</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="missions_date_from">From Date</label>
                                    <input type="date" name="date_from" id="missions_date_from" class="form-control" required <?= $demoRestricted ? 'disabled' : '' ?>>
                                </div>

                                <div class="form-group">
                                    <label for="missions_date_to">To Date</label>
                                    <input type="date" name="date_to" id="missions_date_to" class="form-control" required <?= $demoRestricted ? 'disabled' : '' ?>>
                                </div>
                            </div>

                            <button type="submit" name="export" class="btn btn-primary" <?= $demoRestricted ? 'disabled' : '' ?>>
                                Export Mission Data
                            </button>
                        </form>
                    </div>

                    <div class="export-option">
                        <h3>Admin Activity Logs</h3>
                        <p>Export admin panel activity logs for audit purposes.</p>

                        <form method="POST" action="">
                            <?= csrfField() ?>
                            <input type="hidden" name="export_type" value="admin_logs">

                            <div class="export-fields">
                                <div class="form-group">
                                    <label for="logs_format">Format</label>
                                    <select name="format" id="logs_format" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                                        <option value="csv">CSV</option>
                                        <option value="json">JSON</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="logs_date_from">From Date</label>
                                    <input type="date" name="date_from" id="logs_date_from" class="form-control" required <?= $demoRestricted ? 'disabled' : '' ?>>
                                </div>

                                <div class="form-group">
                                    <label for="logs_date_to">To Date</label>
                                    <input type="date" name="date_to" id="logs_date_to" class="form-control" required <?= $demoRestricted ? 'disabled' : '' ?>>
                                </div>
                            </div>

                            <button type="submit" name="export" class="btn btn-primary" <?= $demoRestricted ? 'disabled' : '' ?>>
                                Export Activity Logs
                            </button>
                        </form>
                    </div>

                    <?php if ($currentAdmin['role'] == ROLE_AIR_BOSS): ?>
                        <div class="export-option" style="border: 2px solid var(--accent-danger);">
                            <h3 style="color: var(--accent-danger);">Full Data Export</h3>
                            <p style="color: var(--accent-warning);">
                                <strong>Warning:</strong> This will export ALL data from the system.
                                Large exports may take significant time.
                            </p>

                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to export ALL data?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="export_type" value="full">

                                <div class="export-fields">
                                    <div class="form-group">
                                        <label for="full_format">Format</label>
                                        <select name="format" id="full_format" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                                            <option value="json">JSON (Recommended)</option>
                                            <option value="csv">CSV (Multiple Files)</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" name="export" class="btn btn-danger" <?= $demoRestricted ? 'disabled' : '' ?>>
                                    Export All Data
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/export/recent_exports.php'; ?>
            </div>
        </main>
    </div>

    <?php require DCS_ROOT_PATH . '/app/Views/Admin/export/date_defaults_script.php'; ?>
</body>
</html>
