<?php
/**
 * Admin Settings Page - Site Feature Management
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once dirname(__DIR__) . '/site_features.php';

// Require admin login and permission
requireAdmin();
requirePermission('change_settings');

// Get current admin
$currentAdmin = getCurrentAdmin();

// Only Air Boss can change site features
if ($currentAdmin['role'] !== ROLE_AIR_BOSS) {
    header('Location: index.php?error=access_denied');
    exit();
}

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = ERROR_MESSAGES['csrf_invalid'];
        $messageType = 'error';
    } else {
        // Load current settings first to preserve custom settings
        $allFeatures = loadSiteFeatures();
        
        // Update only the features that are in groups based on checkboxes
        foreach (getFeatureGroups() as $group => $features) {
            foreach ($features as $key => $label) {
                $allFeatures[$key] = isset($_POST['features'][$key]);
            }
        }

        // Save maintenance whitelist
        $allFeatures['maintenance_whitelist'] = trim($_POST['maintenance_whitelist'] ?? '');
        
        // Note: Discord and Squadron settings are now handled in separate pages
        
        // Handle dependencies - if parent is disabled, disable children
        $dependencies = getFeatureDependencies();
        foreach ($dependencies as $parent => $children) {
            if (!$allFeatures[$parent]) {
                foreach ($children as $child) {
                    $allFeatures[$child] = false;
                }
            }
        }
        
        // Save settings
        if (saveSiteFeatures($allFeatures)) {
            logAdminActivity('SETTINGS_CHANGE', $_SESSION['admin_id'], 'settings', 'site_features', $allFeatures);
            $message = SUCCESS_MESSAGES['settings_saved'];
            $messageType = 'success';
        } else {
            $message = 'Failed to save settings';
            $messageType = 'error';
        }
    }
}

// Load current settings
$currentFeatures = loadSiteFeatures();
$featureGroups = getFeatureGroups();
$dependencies = getFeatureDependencies();

// Page title
$pageTitle = 'Site Settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .settings-group {
            background-color: var(--bg-tertiary);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .settings-group.collapsible-group {
            padding: 0;
        }
        
        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            margin-bottom: 0;
            color: var(--accent-primary);
            font-size: 18px;
            cursor: pointer;
            user-select: none;
            background-color: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }
        
        .group-header:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }
        
        .collapse-arrow {
            font-size: 14px;
            transition: transform 0.3s ease;
        }
        
        .group-header.collapsed .collapse-arrow {
            transform: rotate(-90deg);
        }
        
        .group-content {
            padding: 20px;
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }
        
        .group-content.collapsed {
            max-height: 0;
            padding: 0 20px;
        }
        
        .setting-item {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }
        
        .setting-item input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .setting-item label {
            cursor: pointer;
            flex: 1;
            user-select: none;
        }
        
        .setting-item.dependent {
            margin-left: 25px;
            opacity: 0.8;
        }
        
        .setting-item.disabled {
            opacity: 0.5;
        }
        
        .setting-item.disabled label {
            cursor: not-allowed;
        }
        
        .settings-actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .warning-box {
            background-color: rgba(255, 152, 0, 0.1);
            border: 1px solid var(--accent-warning);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .bulk-actions {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
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
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= e($message) ?>
                    </div>
                <?php endif; ?>
                
                <div class="warning-box">
                    <strong>⚠️ Important:</strong> Disabling features will immediately hide them from all public pages. 
                    Some features have dependencies - disabling a parent feature will automatically disable its related features.
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Site Features</h2>
                    </div>
                    
                    <form method="POST" action="" id="settingsForm">
                        <?= csrfField() ?>
                        
                        <div class="bulk-actions">
                            <button type="button" class="btn btn-secondary" onclick="toggleAll(true)">Enable All</button>
                            <button type="button" class="btn btn-secondary" onclick="toggleAll(false)">Disable All</button>
                            <button type="button" class="btn btn-secondary" onclick="toggleGroup('Navigation', false)">Minimal Mode</button>
                        </div>
                        
                        <div class="settings-grid">
                            <?php foreach ($featureGroups as $groupName => $features): ?>
                                <div class="settings-group collapsible-group">
                                    <h3 class="group-header" data-group="<?= e(strtolower(str_replace(' ', '_', $groupName))) ?>">
                                        <span class="group-title"><?= e($groupName) ?></span>
                                        <span class="collapse-arrow">▼</span>
                                    </h3>
                                    <div class="group-content" id="group_<?= e(strtolower(str_replace(' ', '_', $groupName))) ?>">
                                        <?php foreach ($features as $key => $label): ?>
                                            <?php
                                            $isDependent = false;
                                            $parentKey = null;
                                            foreach ($dependencies as $parent => $children) {
                                                if (in_array($key, $children)) {
                                                    $isDependent = true;
                                                    $parentKey = $parent;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <div class="setting-item <?= $isDependent ? 'dependent' : '' ?>" 
                                                 data-feature="<?= e($key) ?>"
                                                 <?= $parentKey ? 'data-parent="' . e($parentKey) . '"' : '' ?>>
                                                <input type="checkbox" 
                                                       id="feature_<?= e($key) ?>" 
                                                       name="features[<?= e($key) ?>]" 
                                                       value="1"
                                                       <?= $currentFeatures[$key] ? 'checked' : '' ?>
                                                       <?= $isDependent && !$currentFeatures[$parentKey] ? 'disabled' : '' ?>>
                                                <label for="feature_<?= e($key) ?>">
                                                    <?= e($label) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="setting-item" style="margin-top:20px; flex-direction: column; align-items: flex-start;">
                            <label for="maintenance_whitelist">Maintenance Mode Whitelist IPs (comma-separated)</label>
                            <div class="input-with-button">
                                <input type="text" id="maintenance_whitelist" name="maintenance_whitelist" value="<?= e($currentFeatures['maintenance_whitelist'] ?? '') ?>" placeholder="e.g. 127.0.0.1, 192.168.1.1" />
                                <button type="button" class="btn btn-secondary btn-small" id="add-current-ip">Add My IP</button>
                            </div>
                        </div>

                        <div class="settings-actions">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                            <span class="text-muted">Changes take effect immediately</span>
                        </div>
                        
                        <!-- Quick Links to Other Settings -->
                        <div class="card" style="margin-top: 30px;">
                            <div class="card-header">
                                <h3 class="card-title">Additional Settings</h3>
                            </div>
                            
                            <p class="text-muted">Configure other aspects of your site using the dedicated settings pages:</p>
                            
                            <div class="btn-group">
                                <a href="discord_settings.php" class="btn btn-secondary">
                                    <span class="nav-icon">💬</span>
                                    Discord Link Settings
                                </a>
                                <a href="squadron_settings.php" class="btn btn-secondary">
                                    <span class="nav-icon">🏆</span>
                                    Squadron Homepage Settings
                                </a>
                                <a href="themes.php" class="btn btn-secondary">
                                    <span class="nav-icon">🎨</span>
                                    Theme Settings
                                </a>
                                <a href="api_settings.php" class="btn btn-secondary">
                                    <span class="nav-icon">🔌</span>
                                    API Settings
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Feature Impact Guide -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Feature Impact Guide</h2>
                    </div>
                    
                    <div class="settings-grid">
                        <div>
                            <h4>Navigation Items</h4>
                            <p class="text-muted">Controls which pages are accessible from the main navigation menu.</p>
                        </div>
                        <div>
                            <h4>Homepage Sections</h4>
                            <p class="text-muted">Toggle individual sections on the homepage. Useful for simplifying the interface.</p>
                        </div>
                        <div>
                            <h4>Leaderboard Columns</h4>
                            <p class="text-muted">Hide columns you don't track. The table will automatically adjust.</p>
                        </div>
                        <div>
                            <h4>System Features</h4>
                            <p class="text-muted">Completely disable features like Squadrons or Credits if not used by your community.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Handle dependencies
        const dependencies = <?= json_encode($dependencies) ?>;
        
        // Initialize collapsible groups
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers to group headers
            document.querySelectorAll('.group-header').forEach(function(header) {
                header.addEventListener('click', function() {
                    const groupName = this.dataset.group;
                    const content = document.getElementById('group_' + groupName);
                    
                    // Toggle collapsed state
                    this.classList.toggle('collapsed');
                    content.classList.toggle('collapsed');
                });
            });
            
            // Add expand/collapse all buttons
            const bulkActions = document.querySelector('.bulk-actions');
            if (bulkActions) {
                const expandAllBtn = document.createElement('button');
                expandAllBtn.type = 'button';
                expandAllBtn.className = 'btn btn-secondary';
                expandAllBtn.textContent = 'Expand All Groups';
                expandAllBtn.onclick = function() { toggleAllGroups(false); };
                bulkActions.appendChild(expandAllBtn);

                const collapseAllBtn = document.createElement('button');
                collapseAllBtn.type = 'button';
                collapseAllBtn.className = 'btn btn-secondary';
                collapseAllBtn.textContent = 'Collapse All Groups';
                collapseAllBtn.onclick = function() { toggleAllGroups(true); };
                bulkActions.appendChild(collapseAllBtn);
            }

            const addIpBtn = document.getElementById('add-current-ip');
            if (addIpBtn) {
                const currentIp = '<?= e($_SERVER['REMOTE_ADDR'] ?? '') ?>';
                addIpBtn.addEventListener('click', function() {
                    const input = document.getElementById('maintenance_whitelist');
                    if (!input) return;
                    const ips = input.value.split(',').map(ip => ip.trim()).filter(ip => ip);
                    if (!ips.includes(currentIp)) {
                        ips.push(currentIp);
                        input.value = ips.join(', ');
                    }
                });
            }
        });
        
        // Toggle all groups
        function toggleAllGroups(collapse) {
            document.querySelectorAll('.group-header').forEach(function(header) {
                const groupName = header.dataset.group;
                const content = document.getElementById('group_' + groupName);
                
                if (collapse) {
                    header.classList.add('collapsed');
                    content.classList.add('collapsed');
                } else {
                    header.classList.remove('collapsed');
                    content.classList.remove('collapsed');
                }
            });
        }
        
        // Toggle all features
        function toggleAll(enable) {
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = enable;
                checkbox.disabled = false;
            });
            
            if (!enable) {
                // Re-apply dependency rules
                updateDependencies();
            }
        }
        
        // Toggle specific group
        function toggleGroup(groupName, enable) {
            // First disable all
            toggleAll(false);
            
            // Then enable only essential features
            if (!enable) {
                document.querySelectorAll('[id^="feature_nav_home"], [id^="feature_nav_leaderboard"]').forEach(checkbox => {
                    checkbox.checked = true;
                });
            }
        }
        
        // Update dependencies when parent changes
        function updateDependencies() {
            for (const [parent, children] of Object.entries(dependencies)) {
                const parentCheckbox = document.getElementById('feature_' + parent);
                if (parentCheckbox) {
                    const isEnabled = parentCheckbox.checked;
                    
                    children.forEach(child => {
                        const childElement = document.querySelector(`[data-feature="${child}"]`);
                        const childCheckbox = document.getElementById('feature_' + child);
                        
                        if (childElement && childCheckbox) {
                            if (!isEnabled) {
                                childCheckbox.checked = false;
                                childCheckbox.disabled = true;
                                childElement.classList.add('disabled');
                            } else {
                                childCheckbox.disabled = false;
                                childElement.classList.remove('disabled');
                            }
                        }
                    });
                }
            }
        }
        
        // Add change listeners to parent checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            for (const parent of Object.keys(dependencies)) {
                const checkbox = document.getElementById('feature_' + parent);
                if (checkbox) {
                    checkbox.addEventListener('change', updateDependencies);
                }
            }
        });
        
        // Confirm before saving if disabling major features
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            const majorFeatures = ['nav_home', 'credits_enabled', 'squadrons_enabled'];
            const disabledMajor = [];
            
            majorFeatures.forEach(feature => {
                const checkbox = document.getElementById('feature_' + feature);
                if (checkbox && !checkbox.checked) {
                    disabledMajor.push(feature);
                }
            });
            
            if (disabledMajor.length > 0) {
                if (!confirm('You are disabling major features. This will significantly change the site. Continue?')) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>