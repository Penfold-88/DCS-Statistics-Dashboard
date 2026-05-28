<?php
/**
 * Data Export Interface
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once __DIR__ . '/demo_helpers.php';

// Require admin login and permission
requireAdmin();
requirePermission('export_data');

// Get current admin
$currentAdmin = getCurrentAdmin();
$demoRestricted = isDemoRestricted($currentAdmin);

// Handle export request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = ERROR_MESSAGES['csrf_invalid'];
    } elseif ($demoRestricted) {
        $error = 'Demo mode is enabled. Data exports are locked on the public demo.';
    } else {
        $exportType = $_POST['export_type'] ?? '';
        $format = $_POST['format'] ?? 'csv';
        $dateFrom = $_POST['date_from'] ?? '';
        $dateTo = $_POST['date_to'] ?? '';
        
        // Log export action
        logAdminActivity('DATA_EXPORT', $_SESSION['admin_id'], 'export', $exportType, [
            'format' => $format,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        
        // Redirect to API endpoint for download
        $params = http_build_query([
            'type' => $exportType,
            'format' => $format,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'csrf_token' => getCSRFToken()
        ]);
        
        header('Location: api/export_data.php?' . $params);
        exit;
    }
}

// Page title
$pageTitle = 'Export Data';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
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
        <?php include 'nav.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
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
            
            <!-- Content -->
            <div class="admin-content">
                <?php if (isset($error)): ?>
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
                    
                    <!-- Player Data Export -->
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
                    
                    <!-- Mission Statistics Export -->
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
                    
                    <!-- Admin Activity Logs Export -->
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
                    
                    <!-- Full Database Export -->
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
                
                <!-- Export History -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Exports</h2>
                    </div>
                    
                    <?php
                    $recentExports = [];
                    $logs = json_decode(@file_get_contents(ADMIN_LOGS_FILE), true) ?: [];
                    $logs = array_map('normalizeAdminLog', $logs);
                    
                    foreach ($logs as $log) {
                        if (($log['action'] ?? '') === 'DATA_EXPORT' &&
                            adminLogTimestamp($log) > strtotime('-30 days')) {
                            $recentExports[] = $log;
                        }
                    }
                    
                    // Sort by date descending
                    usort($recentExports, function($a, $b) {
                        return adminLogTimestamp($b) - adminLogTimestamp($a);
                    });
                    
                    $recentExports = array_slice($recentExports, 0, 10);
                    ?>
                    
                    <?php if (empty($recentExports)): ?>
                        <p class="text-muted">No exports in the last 30 days.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Admin</th>
                                    <th>Type</th>
                                    <th>Format</th>
                                    <th>Date Range</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentExports as $export): ?>
                                    <?php
                                    $admin = null;
                                    foreach (getAdminUsers() as $u) {
                                        if ($u['id'] == $export['admin_id']) {
                                            $admin = $u;
                                            break;
                                        }
                                    }
                                    $details = $export['details'] ?? [];
                                    ?>
                                    <tr>
                                        <td><?= formatDate($export['created_at'] ?? '') ?></td>
                                        <td><?= e($admin['username'] ?? 'Unknown') ?></td>
                                        <td><?= e($export['target_id'] ?? 'Unknown') ?></td>
                                        <td><?= e($details['format'] ?? 'Unknown') ?></td>
                                        <td>
                                            <?php if (!empty($details['date_from']) && !empty($details['date_to'])): ?>
                                                <?= e($details['date_from']) ?> to <?= e($details['date_to']) ?>
                                            <?php else: ?>
                                                All dates
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Set default date ranges
        document.addEventListener('DOMContentLoaded', function() {
            // Set "To" date to today
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input[name="date_to"]').forEach(input => {
                if (!input.value) input.value = today;
            });
            
            // Set "From" date to 30 days ago
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
            const fromDate = thirtyDaysAgo.toISOString().split('T')[0];
            document.querySelectorAll('input[name="date_from"]').forEach(input => {
                if (!input.value && input.hasAttribute('required')) {
                    input.value = fromDate;
                }
            });
        });
    </script>
</body>
</html>
