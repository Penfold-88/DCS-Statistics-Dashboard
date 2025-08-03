<?php
/**
 * Maintenance Mode Settings
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';

// Require admin login and permission
requireAdmin();
requirePermission('change_settings');

// Current admin
$currentAdmin = getCurrentAdmin();

// Load current configuration
$maintenance = loadMaintenanceConfig();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = ERROR_MESSAGES['csrf_invalid'];
        $messageType = 'error';
    } else {
        // Update maintenance mode
        $maintenance['enabled'] = isset($_POST['enabled']);

        // Add IP to whitelist if provided
        $ip = trim($_POST['ip_address'] ?? '');
        if ($ip !== '') {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                if (!in_array($ip, $maintenance['ip_whitelist'])) {
                    $maintenance['ip_whitelist'][] = $ip;
                }
            } else {
                $message = 'Invalid IP address';
                $messageType = 'error';
            }
        }

        if ($messageType !== 'error') {
            saveMaintenanceConfig($maintenance);
            logAdminActivity('MAINTENANCE_UPDATE', $_SESSION['admin_id'], 'settings', 'maintenance', $maintenance);
            $message = 'Maintenance settings updated';
            $messageType = 'success';
        }
    }
}

$pageTitle = 'Maintenance Whitelist';
$currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-wrapper">
    <?php include 'nav.php'; ?>
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
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="enabled" <?= $maintenance['enabled'] ? 'checked' : '' ?>>
                        Enable Maintenance Mode
                    </label>
                </div>
                <div class="form-group">
                    <label for="ip_address">Whitelist IP</label>
                    <div class="d-flex gap-1">
                        <input type="text" name="ip_address" id="ip_address" class="form-control" placeholder="127.0.0.1">
                        <button type="button" class="btn btn-secondary" onclick="autofillIP()">Use My IP</button>
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
            <?php if (!empty($maintenance['ip_whitelist'])): ?>
                <div class="card mt-2">
                    <div class="card-header">
                        <h2 class="card-title">Current Whitelist</h2>
                    </div>
                    <div class="card-content">
                        <ul>
                            <?php foreach ($maintenance['ip_whitelist'] as $ip): ?>
                                <li><?= e($ip) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
function autofillIP() {
    document.getElementById('ip_address').value = '<?= $currentIP ?>';
}
</script>
</body>
</html>
