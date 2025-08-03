<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';

// Require admin login
requireAdmin();
requirePermission('view_dashboard');

// Get current admin
$currentAdmin = getCurrentAdmin();

// Get dashboard statistics
$stats = getDashboardStats();

// Page title
$pageTitle = 'Flight Deck Operations';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Critical inline CSS for layout */
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 250px; flex-shrink: 0; background: #2a2a2a; }
        .admin-main { flex: 1; min-width: 0; overflow-x: hidden; }
        .admin-content { padding: 30px; max-width: 100%; overflow-x: hidden; }
        .card { max-width: 100%; overflow-x: auto; }
        .data-table { width: 100%; table-layout: fixed; }
        .data-table td { word-wrap: break-word; overflow-wrap: break-word; }
        
        /* Bridge Log / Activity List Styles */
        .activity-list { max-width: 100%; }
        .activity-item {
            padding: 15px;
            border-bottom: 1px solid #444;
            word-wrap: break-word;
            overflow-wrap: break-word;
            transition: background-color 0.2s;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-item:hover { background-color: #333; }
        .activity-time {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
            font-style: italic;
        }
        .activity-action {
            margin-bottom: 5px;
            word-break: break-word;
            line-height: 1.6;
        }
        .activity-action strong {
            color: #4CAF50;
            margin-right: 5px;
        }
        .activity-details {
            font-size: 12px;
            color: #aaa;
            background: #1a1a1a;
            padding: 8px;
            border-radius: 4px;
            margin-top: 8px;
            word-break: break-all;
            max-width: 100%;
            overflow-x: auto;
            border-left: 3px solid #4CAF50;
        }
        code {
            background: #1a1a1a;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #ff9800;
            word-break: break-all;
            display: inline-block;
            max-width: 100%;
        }
        .text-muted {
            color: #888;
            font-size: 13px;
        }
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
            .admin-wrapper { flex-direction: column; }
            .admin-content { padding: 15px; }
            .activity-item { padding: 10px; }
            .activity-details { font-size: 11px; padding: 5px; }
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
                <!-- Welcome Message -->
                <div class="alert alert-info">
                    Welcome aboard, <?= e($currentAdmin['username']) ?>! 
                    Last watch: <?= formatDate($currentAdmin['last_login']) ?>
                </div>
                
                <!-- Statistics Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Bridge Officers</div>
                        <div class="stat-value"><?= number_format($stats['total_admins']) ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Active Pilots</div>
                        <div class="stat-value"><?= number_format($stats['total_players']) ?></div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Bridge Log</h2>
                        <a href="logs.php" class="btn btn-primary btn-small">View All</a>
                    </div>
                    
                    <?php if (empty($stats['recent_activity'])): ?>
                        <p class="text-muted">No recent activity to display.</p>
                    <?php else: ?>
                        <div class="activity-list">
                            <?php foreach ($stats['recent_activity'] as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-time"><?= formatDate($activity['created_at']) ?></div>
                                    <div class="activity-action">
                                        <strong><?= e($activity['admin_username']) ?></strong>
                                        <?= e(LOG_ACTIONS[$activity['action']] ?? $activity['action']) ?>
                                        <?php if ($activity['target_type']): ?>
                                            <div style="margin-top: 5px;">
                                                <span class="text-muted">Target: <?= e($activity['target_type']) ?></span>
                                                <?php if ($activity['target_id']): ?>
                                                    <code style="font-size: 11px;"><?= e(substr($activity['target_id'], 0, 50)) ?><?= strlen($activity['target_id']) > 50 ? '...' : '' ?></code>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($activity['details'] && !empty($activity['details'])): ?>
                                        <div class="activity-details">
                                            <?php 
                                            $details = is_array($activity['details']) ? json_encode($activity['details']) : $activity['details'];
                                            echo e(substr($details, 0, 100)) . (strlen($details) > 100 ? '...' : '');
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Quick Actions</h2>
                    </div>
                    <div class="btn-group">
                        <?php if (hasPermission('manage_admins')): ?>
                            <a href="admins.php" class="btn btn-primary">Manage Admins</a>
                        <?php endif; ?>
                        <?php if (hasPermission('view_logs')): ?>
                            <a href="logs.php" class="btn btn-secondary">View Logs</a>
                        <?php endif; ?>
                          <?php if (hasPermission('export_data')): ?>
                              <a href="export.php" class="btn btn-secondary">Export Data</a>
                          <?php endif; ?>
                          <?php if (hasPermission('change_settings')): ?>
                              <a href="updater.php" class="btn btn-secondary">Updater</a>
                          <?php endif; ?>
                      </div>
                  </div>
                
                <!-- System Information -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">System Information</h2>
                    </div>
                    <table class="data-table">
                        <tr>
                            <td>Admin Panel Version</td>
                            <td><?= ADMIN_PANEL_VERSION ?></td>
                        </tr>
                        <tr>
                            <td>PHP Version</td>
                            <td><?= phpversion() ?></td>
                        </tr>
                        <tr>
                            <td>Storage Mode</td>
                            <td><?= USE_DATABASE ? 'Database' : 'File-based' ?></td>
                        </tr>
                        <tr>
                            <td>Data Directory</td>
                            <td title="<?= htmlspecialchars(ADMIN_DATA_DIR) ?>"><?= basename(rtrim(ADMIN_DATA_DIR, '/')) ?>/</td>
                        </tr>
                        <tr>
                            <td>Log Retention</td>
                            <td><?= LOG_RETENTION_DAYS ?> days</td>
                        </tr>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Auto-refresh activity every 30 seconds
        setInterval(() => {
            // In a real implementation, this would fetch new activity via AJAX
        }, 30000);
    </script>
</body>
</html>