<?php
/**
 * Admin Settings Page - Site Feature Management
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once dirname(__DIR__) . '/language.php';
require_once dirname(__DIR__) . '/site_features.php';
require_once dirname(__DIR__) . '/api_client_enhanced.php';

// Require admin login and permission
requireAdmin();
requirePermission('manage_features');

// Get current admin
$currentAdmin = getCurrentAdmin();
$demoRestricted = isDemoRestricted($currentAdmin);

$lockedFeatures = [
    'leaderboard_sorties' => dcs_t('admin.settings.locked_sorties_reason')
];

function settingsTranslationKey($value) {
    return preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string)$value)));
}

function settingsGroupLabel($groupName) {
    $key = 'admin.settings.group.' . settingsTranslationKey($groupName);
    $translated = dcs_t($key);
    return $translated === $key ? $groupName : $translated;
}

function settingsFeatureLabel($featureKey, $fallback) {
    $key = 'admin.settings.feature.' . $featureKey;
    $translated = dcs_t($key);
    return $translated === $key ? $fallback : $translated;
}

function getDetectedServerCardFeatures() {
    try {
        $client = createEnhancedAPIClient();
        $response = $client->request('/servers', null, 'GET');
        $servers = is_array($response) ? $response : [];
        $features = [];

        foreach ($servers as $index => $server) {
            if (!is_array($server)) {
                continue;
            }

            $name = trim((string)($server['name'] ?? dcs_t('admin.settings.server_number', ['number' => $index + 1])));
            if ($name === '') {
                $name = dcs_t('admin.settings.server_number', ['number' => $index + 1]);
            }

            $features[serverCardFeatureKey($name)] = $name;
        }

        return $features;
    } catch (Exception $e) {
        return [];
    }
}

$dynamicServerFeatures = getDetectedServerCardFeatures();

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = dcs_t('admin.settings.csrf_invalid');
        $messageType = 'error';
    } elseif ($demoRestricted) {
        $message = demoWriteLockMessage();
        $messageType = 'error';
    } else {
        // Load current settings first to preserve custom settings
        $allFeatures = loadSiteFeatures();
        $featureGroupsForSave = getFeatureGroups();
        if (!empty($dynamicServerFeatures)) {
            $featureGroupsForSave['Server Features'] = array_merge(
                $featureGroupsForSave['Server Features'],
                $dynamicServerFeatures
            );
        }
        
        // Update only the features that are in groups based on checkboxes
        foreach ($featureGroupsForSave as $group => $features) {
            foreach ($features as $key => $label) {
                $allFeatures[$key] = isset($_POST['features'][$key]);
            }
        }
        
        // Note: Discord, Squadron homepage, and custom links are handled in separate pages.

        // Locked features are visible in the UI but cannot be enabled yet.
        foreach ($lockedFeatures as $lockedKey => $reason) {
            $allFeatures[$lockedKey] = false;
        }
        
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
        if ($messageType === 'error') {
            // Keep the validation message set above.
        } elseif (saveSiteFeatures($allFeatures)) {
            logAdminActivity('SETTINGS_CHANGE', $_SESSION['admin_id'], 'settings', 'site_features', $allFeatures);
            $message = dcs_t('admin.settings.save_success');
            $messageType = 'success';
        } else {
            $message = dcs_t('admin.settings.save_failed');
            $messageType = 'error';
        }
    }
}

// Load current settings
$currentFeatures = loadSiteFeatures();
$featureGroups = getFeatureGroups();
if (!empty($dynamicServerFeatures)) {
    $featureGroups['Server Features'] = array_merge(
        $featureGroups['Server Features'],
        $dynamicServerFeatures
    );
}
$dependencies = getFeatureDependencies();

// Page title
$pageTitle = dcs_t('admin.settings.title');
?>
<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
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

        .setting-item.locked-feature input[type="checkbox"] {
            cursor: not-allowed;
        }

        .settings-subheading {
            border-top: 1px solid var(--border-color);
            color: var(--accent-primary);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.06em;
            margin: 18px 0 12px;
            padding-top: 16px;
            text-transform: uppercase;
        }

        .setting-item.dynamic-server {
            align-items: flex-start;
        }

        .setting-item.dynamic-server input[type="checkbox"] {
            flex: 0 0 auto;
            margin-top: 2px;
        }

        .setting-item.dynamic-server label {
            font-size: clamp(12px, 1.2vw, 14px);
            line-height: 1.35;
            overflow-wrap: anywhere;
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
                <h1><?= e($pageTitle) ?></h1>
                <div class="admin-user-menu">
                    <div class="admin-user-info">
                        <div class="admin-username"><?= e($currentAdmin['username']) ?></div>
                        <div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div>
                    </div>
                    <a href="logout.php" class="btn btn-secondary btn-small"><?= e(dcs_t('admin.common.logout')) ?></a>
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
                    <strong><?= e(dcs_t('admin.settings.important_label')) ?></strong> <?= e(dcs_t('admin.settings.important_text')) ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.settings.site_features')) ?></h2>
                    </div>
                    
                    <form method="POST" action="" id="settingsForm">
                        <?= csrfField() ?>
                        
                        <div class="bulk-actions">
                            <button type="button" class="btn btn-secondary" onclick="toggleAll(true)"><?= e(dcs_t('admin.settings.enable_all')) ?></button>
                            <button type="button" class="btn btn-secondary" onclick="toggleAll(false)"><?= e(dcs_t('admin.settings.disable_all')) ?></button>
                            <button type="button" class="btn btn-secondary" onclick="toggleGroup('Navigation', false)"><?= e(dcs_t('admin.settings.minimal_mode')) ?></button>
                        </div>
                        
                        <div class="settings-grid">
                            <?php foreach ($featureGroups as $groupName => $features): ?>
                                <div class="settings-group collapsible-group">
                                    <h3 class="group-header" data-group="<?= e(strtolower(str_replace(' ', '_', $groupName))) ?>">
                                        <span class="group-title"><?= e(settingsGroupLabel($groupName)) ?></span>
                                        <span class="collapse-arrow">▼</span>
                                    </h3>
                                    <div class="group-content" id="group_<?= e(strtolower(str_replace(' ', '_', $groupName))) ?>">
                                        <?php $serverSubheadingShown = false; ?>
                                        <?php foreach ($features as $key => $label): ?>
                                            <?php
                                            $isDependent = false;
                                            $parentKey = null;
                                            $isLocked = isset($lockedFeatures[$key]);
                                            $isDynamicServer = isset($dynamicServerFeatures[$key]);
                                            foreach ($dependencies as $parent => $children) {
                                                if (in_array($key, $children)) {
                                                    $isDependent = true;
                                                    $parentKey = $parent;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <?php if ($isDynamicServer && !$serverSubheadingShown): ?>
                                                <div class="settings-subheading"><?= e(dcs_t('admin.settings.detected_servers')) ?></div>
                                                <?php $serverSubheadingShown = true; ?>
                                            <?php endif; ?>
                                            <div class="setting-item <?= $isDependent ? 'dependent' : '' ?> <?= $isLocked ? 'disabled locked-feature' : '' ?> <?= $isDynamicServer ? 'dynamic-server' : '' ?>" 
                                                 data-feature="<?= e($key) ?>"
                                                 <?= $parentKey ? 'data-parent="' . e($parentKey) . '"' : '' ?>
                                                 <?= $isLocked ? 'data-locked="true" title="' . e($lockedFeatures[$key]) . '"' : '' ?>>
                                                <input type="checkbox" 
                                                       id="feature_<?= e($key) ?>" 
                                                       name="features[<?= e($key) ?>]" 
                                                       value="1"
                                                       <?= (!$isLocked && ($currentFeatures[$key] ?? true)) ? 'checked' : '' ?>
                                                       <?= ($isLocked || ($isDependent && !($currentFeatures[$parentKey] ?? true))) ? 'disabled' : '' ?>>
                                                <label for="feature_<?= e($key) ?>">
                                                    <?= e(settingsFeatureLabel($key, $label)) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="settings-actions">
                            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.settings.save_settings')) ?></button>
                            <span class="text-muted"><?= e(dcs_t('admin.settings.changes_immediate')) ?></span>
                        </div>
                        
                        <!-- Quick Links to Other Settings -->
                        <div class="card" style="margin-top: 30px;">
                            <div class="card-header">
                                <h3 class="card-title"><?= e(dcs_t('admin.settings.additional_settings')) ?></h3>
                            </div>
                            
                            <p class="text-muted"><?= e(dcs_t('admin.settings.additional_text')) ?></p>
                            
                            <div class="btn-group">
                                <a href="discord_settings.php" class="btn btn-secondary">
                                    <span class="nav-icon">💬</span>
                                    <?= e(dcs_t('admin.settings.discord_link_settings')) ?>
                                </a>
                                <a href="squadron_settings.php" class="btn btn-secondary">
                                    <span class="nav-icon">🏆</span>
                                    <?= e(dcs_t('admin.settings.squadron_homepage_settings')) ?>
                                </a>
                                <a href="custom_links.php" class="btn btn-secondary">
                                    <span class="nav-icon">🔗</span>
                                    <?= e(dcs_t('admin.nav.custom_links')) ?>
                                </a>
                                <a href="themes.php" class="btn btn-secondary">
                                    <span class="nav-icon">🎨</span>
                                    <?= e(dcs_t('admin.settings.theme_settings')) ?>
                                </a>
                                <a href="api_settings.php" class="btn btn-secondary">
                                    <span class="nav-icon">🔌</span>
                                    <?= e(dcs_t('admin.nav.api_settings')) ?>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Feature Impact Guide -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.settings.impact_guide')) ?></h2>
                    </div>
                    
                    <div class="settings-grid">
                        <div>
                            <h4><?= e(dcs_t('admin.settings.guide_navigation')) ?></h4>
                            <p class="text-muted"><?= e(dcs_t('admin.settings.guide_navigation_text')) ?></p>
                        </div>
                        <div>
                            <h4><?= e(dcs_t('admin.settings.guide_homepage')) ?></h4>
                            <p class="text-muted"><?= e(dcs_t('admin.settings.guide_homepage_text')) ?></p>
                        </div>
                        <div>
                            <h4><?= e(dcs_t('admin.settings.guide_leaderboard')) ?></h4>
                            <p class="text-muted"><?= e(dcs_t('admin.settings.guide_leaderboard_text')) ?></p>
                        </div>
                        <div>
                            <h4><?= e(dcs_t('admin.settings.guide_system')) ?></h4>
                            <p class="text-muted"><?= e(dcs_t('admin.settings.guide_system_text')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Handle dependencies
        const dependencies = <?= json_encode($dependencies) ?>;
        const settingsText = <?= json_encode([
            'expandAll' => dcs_t('admin.settings.expand_all_groups'),
            'collapseAll' => dcs_t('admin.settings.collapse_all_groups'),
            'majorConfirm' => dcs_t('admin.settings.major_confirm')
        ]) ?>;
        
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
                expandAllBtn.textContent = settingsText.expandAll;
                expandAllBtn.onclick = function() { toggleAllGroups(false); };
                bulkActions.appendChild(expandAllBtn);
                
                const collapseAllBtn = document.createElement('button');
                collapseAllBtn.type = 'button';
                collapseAllBtn.className = 'btn btn-secondary';
                collapseAllBtn.textContent = settingsText.collapseAll;
                collapseAllBtn.onclick = function() { toggleAllGroups(true); };
                bulkActions.appendChild(collapseAllBtn);
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
                if (checkbox.closest('[data-locked="true"]')) {
                    checkbox.checked = false;
                    checkbox.disabled = true;
                    return;
                }
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
                            if (childElement.dataset.locked === 'true') {
                                childCheckbox.checked = false;
                                childCheckbox.disabled = true;
                                childElement.classList.add('disabled');
                                return;
                            }
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
                if (!confirm(settingsText.majorConfirm)) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>
