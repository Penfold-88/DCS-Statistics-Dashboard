<?php
/**
 * LSO Permissions Management
 * Allows Air Boss to manage LSO group permissions
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';

// Require admin login and permission
requireAdmin();
requirePermission('manage_permissions');

$message = '';
$error = '';

// Get writable path for permissions configuration
function getPermissionsConfigPath() {
    $primaryPath = __DIR__ . '/data/lso_permissions.json';
    $primaryDir = dirname($primaryPath);
    
    if (is_dir($primaryDir) && is_writable($primaryDir)) {
        return $primaryPath;
    }
    
    if (!is_dir($primaryDir)) {
        @mkdir($primaryDir, 0777, true);
        @chmod($primaryDir, 0777);
        if (is_dir($primaryDir) && is_writable($primaryDir)) {
            return $primaryPath;
        }
    }
    
    $tempDir = sys_get_temp_dir() . '/dcs_stats';
    if (!is_dir($tempDir)) {
        @mkdir($tempDir, 0777, true);
    }
    
    return $tempDir . '/lso_permissions.json';
}

// Default LSO permissions
$defaultLSOPermissions = [
    // Core Permissions
    'view_dashboard' => ['enabled' => true, 'label' => 'View Dashboard', 'description' => 'Access the main dashboard and statistics'],
    'export_data' => ['enabled' => true, 'label' => 'Export Data', 'description' => 'Export player and mission data'],
    'view_logs' => ['enabled' => true, 'label' => 'View Logs', 'description' => 'View activity logs and audit trails'],
    
    // Management Permissions
    'manage_admins' => ['enabled' => false, 'label' => 'Manage Admins', 'description' => 'Add, edit, and remove admin users'],
    'manage_permissions' => ['enabled' => false, 'label' => 'Manage Permissions', 'description' => 'Configure LSO group permissions'],
    'manage_api' => ['enabled' => false, 'label' => 'Manage API', 'description' => 'Configure API settings and connections'],
    'manage_features' => ['enabled' => false, 'label' => 'Manage Features', 'description' => 'Enable/disable site features'],
    'manage_themes' => ['enabled' => false, 'label' => 'Manage Themes', 'description' => 'Customize site appearance and themes'],
    'manage_discord' => ['enabled' => false, 'label' => 'Manage Discord', 'description' => 'Configure Discord integration and links'],
    'manage_squadrons' => ['enabled' => false, 'label' => 'Manage Squadrons', 'description' => 'Configure squadron homepage settings'],
    'manage_maintenance' => ['enabled' => false, 'label' => 'Manage Maintenance', 'description' => 'Configure maintenance mode settings'],
    'manage_updates' => ['enabled' => false, 'label' => 'Manage Updates', 'description' => 'Install updates and manage backups'],
    
    // Additional Permissions
    'change_settings' => ['enabled' => false, 'label' => 'Change Settings', 'description' => 'General permission to access settings menu']
];

// Load current permissions
$permissionsFile = getPermissionsConfigPath();
$lsoPermissions = $defaultLSOPermissions;

if (file_exists($permissionsFile)) {
    $saved = json_decode(file_get_contents($permissionsFile), true);
    if ($saved && is_array($saved)) {
        // Merge with defaults to ensure all permissions exist
        foreach ($saved as $key => $value) {
            if (isset($lsoPermissions[$key])) {
                $lsoPermissions[$key]['enabled'] = $value['enabled'] ?? false;
            }
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request token';
    } else {
        $enabledPerms = $_POST['permissions'] ?? [];
        
        // Update permissions
        foreach ($lsoPermissions as $key => &$perm) {
            $perm['enabled'] = in_array($key, $enabledPerms);
        }
        
        // Save to file
        $result = @file_put_contents($permissionsFile, json_encode($lsoPermissions, JSON_PRETTY_PRINT));
        if ($result === false) {
            $error = 'Failed to save permissions. Please check file permissions.';
        } else {
            $message = 'LSO permissions updated successfully';
            if (function_exists('logActivity')) {
                logActivity('PERMISSIONS_UPDATE', 'Updated LSO group permissions');
            }
            
            // Update the actual permissions in config if possible
            updateLSOPermissionsInConfig($lsoPermissions);
        }
    }
}

// Function to update permissions in the actual config/auth system
function updateLSOPermissionsInConfig($permissions) {
    // This would integrate with your existing permission system
    // For now, we'll store it separately and check it when validating permissions
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Page title
$pageTitle = 'LSO Permissions Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .permission-item {
            background: var(--bg-secondary);
            padding: 15px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
            min-height: 60px;
        }
        
        .permission-item:hover {
            border-color: var(--accent-primary);
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.1);
            transform: translateY(-1px);
        }
        
        .permission-checkbox {
            flex-shrink: 0;
        }
        
        .permission-checkbox input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--accent-primary);
        }
        
        .permission-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .permission-label {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .permission-description {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.4;
            opacity: 0.8;
        }
        
        .permission-item.enabled {
            background: rgba(76, 175, 80, 0.08);
            border-color: rgba(76, 175, 80, 0.4);
        }
        
        .permission-item.enabled .permission-label {
            color: var(--accent-primary);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 25px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .section-header h3 {
            margin: 0;
            color: var(--text-primary);
            font-size: 18px;
            font-weight: 600;
        }
        
        .permission-count {
            background: var(--accent-primary);
            color: white;
            padding: 3px 10px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .quick-actions button {
            padding: 8px 16px;
            font-size: 13px;
            white-space: nowrap;
        }
        
        .permissions-info {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .permissions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php include __DIR__ . '/nav.php'; ?>
        
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <h1><?= $pageTitle ?></h1>
                <div class="admin-user-menu">
                    <div class="admin-user-info">
                        <div class="admin-username"><?= e(getCurrentAdmin()['username']) ?></div>
                        <div class="admin-role"><?= getRoleBadge(getCurrentAdmin()['role']) ?></div>
                    </div>
                    <a href="logout.php" class="btn btn-secondary btn-small">Logout</a>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content">
                <div class="card">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <div class="permissions-info">
                        <strong>About LSO Permissions:</strong><br>
                        Configure what members of the Landing Signal Officer (LSO) group can access in the admin panel. 
                        These permissions apply to all users with the LSO role. Air Boss users always have full access to all features.
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="quick-actions">
                            <button type="button" class="btn btn-sm" onclick="selectAll()">Select All</button>
                            <button type="button" class="btn btn-sm" onclick="selectNone()">Select None</button>
                            <button type="button" class="btn btn-sm" onclick="selectDefault()">Reset to Default</button>
                        </div>
                        
                        <div class="section-header">
                            <h3>Core Permissions</h3>
                            <span class="permission-count" id="core-count">0</span>
                        </div>
                        
                        <div class="permissions-grid" id="core-permissions">
                            <?php 
                            $corePerms = ['view_dashboard', 'export_data', 'view_logs'];
                            foreach ($corePerms as $key): 
                                if (isset($lsoPermissions[$key])):
                                    $perm = $lsoPermissions[$key];
                            ?>
                            <div class="permission-item <?= $perm['enabled'] ? 'enabled' : '' ?>" data-perm="<?= $key ?>">
                                <div class="permission-checkbox">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="<?= $key ?>" 
                                           id="perm_<?= $key ?>"
                                           <?= $perm['enabled'] ? 'checked' : '' ?>
                                           onchange="updatePermissionUI(this)">
                                </div>
                                <div class="permission-details">
                                    <label for="perm_<?= $key ?>" class="permission-label">
                                        <?= htmlspecialchars($perm['label']) ?>
                                    </label>
                                    <div class="permission-description">
                                        <?= htmlspecialchars($perm['description']) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; endforeach; ?>
                        </div>
                        
                        <div class="section-header">
                            <h3>Management Permissions</h3>
                            <span class="permission-count" id="mgmt-count">0</span>
                        </div>
                        
                        <div class="permissions-grid" id="mgmt-permissions">
                            <?php 
                            $mgmtPerms = ['manage_admins', 'manage_permissions', 'manage_api', 'manage_features', 'manage_themes', 'manage_discord', 'manage_squadrons', 'manage_maintenance', 'manage_updates', 'change_settings'];
                            foreach ($mgmtPerms as $key): 
                                if (isset($lsoPermissions[$key])):
                                    $perm = $lsoPermissions[$key];
                            ?>
                            <div class="permission-item <?= $perm['enabled'] ? 'enabled' : '' ?>" data-perm="<?= $key ?>">
                                <div class="permission-checkbox">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="<?= $key ?>" 
                                           id="perm_<?= $key ?>"
                                           <?= $perm['enabled'] ? 'checked' : '' ?>
                                           onchange="updatePermissionUI(this)">
                                </div>
                                <div class="permission-details">
                                    <label for="perm_<?= $key ?>" class="permission-label">
                                        <?= htmlspecialchars($perm['label']) ?>
                                    </label>
                                    <div class="permission-description">
                                        <?= htmlspecialchars($perm['description']) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; endforeach; ?>
                        </div>
                        
                        
                        <button type="submit" class="btn btn-primary" style="margin-top: 30px;">Save Permissions</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function updatePermissionUI(checkbox) {
            const permItem = checkbox.closest('.permission-item');
            if (checkbox.checked) {
                permItem.classList.add('enabled');
            } else {
                permItem.classList.remove('enabled');
            }
            updateCounts();
        }
        
        function updateCounts() {
            // Update core permissions count
            const coreChecked = document.querySelectorAll('#core-permissions input[type="checkbox"]:checked').length;
            document.getElementById('core-count').textContent = coreChecked;
            
            // Update management permissions count
            const mgmtChecked = document.querySelectorAll('#mgmt-permissions input[type="checkbox"]:checked').length;
            document.getElementById('mgmt-count').textContent = mgmtChecked;
        }
        
        function selectAll() {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                cb.checked = true;
                updatePermissionUI(cb);
            });
        }
        
        function selectNone() {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                cb.checked = false;
                updatePermissionUI(cb);
            });
        }
        
        function selectDefault() {
            // Default permissions for LSO
            const defaults = ['view_dashboard', 'export_data', 'view_logs'];
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                cb.checked = defaults.includes(cb.value);
                updatePermissionUI(cb);
            });
        }
        
        // Initialize counts on page load
        document.addEventListener('DOMContentLoaded', updateCounts);
    </script>
</body>
</html>