<?php
/**
 * Maintenance Mode Settings
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once dirname(__DIR__) . '/language.php';

// Require admin login and permission
requireAdmin();
requirePermission('manage_maintenance');

// Current admin
$currentAdmin = getCurrentAdmin();
$demoRestricted = isDemoRestricted($currentAdmin);

// Load current configuration
$maintenance = loadMaintenanceConfig();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = dcs_t('admin.maintenance.csrf_invalid');
        $messageType = 'error';
    } elseif ($demoRestricted) {
        $message = demoWriteLockMessage();
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? 'update';
        $maintenance['ip_whitelist'] = $maintenance['ip_whitelist'] ?? [];

        if ($action === 'remove_ip') {
            $ipToRemove = trim($_POST['ip'] ?? '');
            $originalCount = count($maintenance['ip_whitelist']);
            $maintenance['ip_whitelist'] = array_values(array_filter(
                $maintenance['ip_whitelist'],
                static fn($ip) => $ip !== $ipToRemove
            ));

            if ($ipToRemove === '' || count($maintenance['ip_whitelist']) === $originalCount) {
                $message = dcs_t('admin.maintenance.ip_not_found');
                $messageType = 'error';
            } else {
                saveMaintenanceConfig($maintenance);
                logAdminActivity('MAINTENANCE_IP_REMOVE', $_SESSION['admin_id'], 'settings', 'maintenance', ['ip' => $ipToRemove]);
                $message = dcs_t('admin.maintenance.ip_removed');
                $messageType = 'success';
            }
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
                    $message = dcs_t('admin.maintenance.invalid_ip');
                    $messageType = 'error';
                }
            }

            if ($messageType !== 'error') {
                saveMaintenanceConfig($maintenance);
                logAdminActivity('MAINTENANCE_UPDATE', $_SESSION['admin_id'], 'settings', 'maintenance', $maintenance);
                $message = dcs_t('admin.maintenance.save_success');
                $messageType = 'success';
            }
        }
    }
}

$pageTitle = dcs_t('admin.maintenance.title');
$currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-wrapper">
    <?php include 'nav.php'; ?>
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
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update">
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="enabled" <?= $maintenance['enabled'] ? 'checked' : '' ?>>
                        <?= e(dcs_t('admin.maintenance.enable_mode')) ?>
                    </label>
                </div>
                <div class="form-group">
                    <label for="ip_address"><?= e(dcs_t('admin.maintenance.whitelist_ip')) ?></label>
                    <div class="d-flex gap-1">
                        <input type="text" name="ip_address" id="ip_address" class="form-control" placeholder="127.0.0.1">
                        <button type="button" class="btn btn-secondary" onclick="autofillIP()"><?= e(dcs_t('admin.maintenance.use_my_ip')) ?></button>
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.settings.save_settings')) ?></button>
                </div>
            </form>
            <?php if (!empty($maintenance['ip_whitelist'])): ?>
                <div class="card mt-2">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.maintenance.current_whitelist')) ?></h2>
                    </div>
                    <div class="card-content">
                        <ul class="maintenance-whitelist">
                            <?php foreach ($maintenance['ip_whitelist'] as $ip): ?>
                                <li class="maintenance-whitelist-item">
                                    <span><?= e($ip) ?></span>
                                    <form method="POST" class="maintenance-whitelist-remove">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="remove_ip">
                                        <input type="hidden" name="ip" value="<?= e($ip) ?>">
                                        <button type="submit" class="btn btn-danger btn-small"><?= e(dcs_t('admin.custom_links.remove')) ?></button>
                                    </form>
                                </li>
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
<style>
.maintenance-whitelist {
    list-style: none;
    margin: 0;
    padding: 0;
}

.maintenance-whitelist-item {
    align-items: center;
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    padding: 0.5rem 0;
}

.maintenance-whitelist-remove {
    margin: 0;
}
</style>
</body>
</html>
