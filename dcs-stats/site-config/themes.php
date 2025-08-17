<?php
/**
 * Themes Management Page
 * Allows super admins to customize site appearance
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once __DIR__ . '/../config_path.php';

// Require admin login and permission
requireAdmin();
requirePermission('manage_themes');

// Get current admin to check specific permissions
$currentAdmin = getCurrentAdmin();
$isAirBoss = ($currentAdmin['role'] === ROLE_AIR_BOSS);

$message = '';
$error = '';

// Get writable path for menu configuration
if (!function_exists('getMenuConfigPath')) {
    function getMenuConfigPath() {
    // Try primary location with data directory
    $primaryPath = __DIR__ . '/data/menu_config.json';
    $primaryDir = dirname($primaryPath);
    
    // Check if directory exists and is writable
    if (is_dir($primaryDir) && is_writable($primaryDir)) {
        return $primaryPath;
    }
    
    // Try to create directory with proper permissions
    if (!is_dir($primaryDir)) {
        @mkdir($primaryDir, 0777, true);
        @chmod($primaryDir, 0777);
        if (is_dir($primaryDir) && is_writable($primaryDir)) {
            return $primaryPath;
        }
    }
    
    // Try alternative location in parent directory
    $altPath = __DIR__ . '/../menu_config.json';
    $altDir = dirname($altPath);
    if (is_writable($altDir)) {
        return $altPath;
    }
    
    // Fall back to temp directory
    $tempDir = sys_get_temp_dir() . '/dcs_stats';
    if (!is_dir($tempDir)) {
        @mkdir($tempDir, 0777, true);
    }
    
    return $tempDir . '/menu_config.json';
    }
}

// Load site features to check if Discord/Squadron links are enabled
require_once __DIR__ . '/../site_features.php';

// Load menu configuration
$menuConfigFile = getMenuConfigPath();
$defaultMenuItems = [
    ['name' => 'Home', 'url' => 'index.php', 'enabled' => true, 'type' => 'page'],
    ['name' => 'Leaderboard', 'url' => 'leaderboard.php', 'enabled' => true, 'type' => 'page'],
    ['name' => 'Pilot Statistics', 'url' => 'pilot_statistics.php', 'enabled' => true, 'type' => 'page'],
    ['name' => 'Pilot Credits', 'url' => 'pilot_credits.php', 'enabled' => true, 'type' => 'page'],
    ['name' => 'Squadrons', 'url' => 'squadrons.php', 'enabled' => true, 'type' => 'page'],
    ['name' => 'Servers', 'url' => 'servers.php', 'enabled' => true, 'type' => 'page']
];

// Add Discord if enabled
if (isFeatureEnabled('show_discord_link')) {
    $defaultMenuItems[] = [
        'name' => 'Discord',
        'url' => getFeatureValue('discord_link_url', 'https://discord.gg/DNENf6pUNX'),
        'enabled' => true,
        'type' => 'discord'
    ];
}

// Add Squadron Homepage if enabled
if (isFeatureEnabled('show_squadron_homepage') && !empty(getFeatureValue('squadron_homepage_url'))) {
    $defaultMenuItems[] = [
        'name' => getFeatureValue('squadron_homepage_text', 'Squadron'),
        'url' => getFeatureValue('squadron_homepage_url'),
        'enabled' => true,
        'type' => 'squadron_homepage'
    ];
}

$menuItems = $defaultMenuItems;
if (file_exists($menuConfigFile)) {
    $savedMenu = json_decode(file_get_contents($menuConfigFile), true);
    if ($savedMenu && is_array($savedMenu)) {
        // Merge with default items to pick up any new Discord/Squadron links
        $savedUrls = array_column($savedMenu, 'url');
        foreach ($defaultMenuItems as $defaultItem) {
            $found = false;
            foreach ($savedMenu as &$savedItem) {
                if ($savedItem['url'] === $defaultItem['url'] || 
                    (isset($savedItem['type']) && isset($defaultItem['type']) && $savedItem['type'] === $defaultItem['type'])) {
                    $found = true;
                    // Update URL for Discord/Squadron links in case they changed
                    if (in_array($defaultItem['type'] ?? '', ['discord', 'squadron_homepage'])) {
                        $savedItem['url'] = $defaultItem['url'];
                        $savedItem['name'] = $defaultItem['name']; // Update name too for squadron
                    }
                    break;
                }
            }
            if (!$found && in_array($defaultItem['type'] ?? '', ['discord', 'squadron_homepage'])) {
                // Add new Discord/Squadron link that wasn't in saved config
                $savedMenu[] = $defaultItem;
            }
        }
        $menuItems = $savedMenu;
    }
}

// Handle theme actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request token';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_menu':
                // Handle menu updates
                $newMenuItems = [];
                $menuNames = $_POST['menu_names'] ?? [];
                $menuUrls = $_POST['menu_urls'] ?? [];
                $menuEnabled = $_POST['menu_enabled'] ?? [];
                $menuOrder = $_POST['menu_order'] ?? [];
                $menuTypes = $_POST['menu_types'] ?? [];
                
                // Rebuild menu items based on posted data
                foreach ($menuOrder as $index) {
                    if (isset($menuNames[$index]) && isset($menuUrls[$index])) {
                        $newMenuItems[] = [
                            'name' => $menuNames[$index],
                            'url' => $menuUrls[$index],
                            'enabled' => isset($menuEnabled[$index]),
                            'type' => $menuTypes[$index] ?? 'page'
                        ];
                    }
                }
                
                // Save to file
                $result = @file_put_contents($menuConfigFile, json_encode($newMenuItems, JSON_PRETTY_PRINT));
                if ($result === false) {
                    $error = 'Failed to save menu configuration. Please check file permissions.';
                } else {
                    $menuItems = $newMenuItems;
                    $message = 'Menu configuration updated successfully';
                    if (function_exists('logActivity')) {
                        logActivity('MENU_UPDATE', 'Updated navigation menu configuration');
                    }
                }
                break;
                
            case 'upload_css':
                // Only Air Boss can upload CSS
                if (!$isAirBoss) {
                    $error = 'Only Air Boss can upload custom CSS files';
                    break;
                }
                // Handle CSS file upload
                if (isset($_FILES['css_file']) && $_FILES['css_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadedFile = $_FILES['css_file'];
                    $fileName = $uploadedFile['name'];
                    $fileTmp = $uploadedFile['tmp_name'];
                    $fileSize = $uploadedFile['size'];
                    
                    // Validate file type
                    $allowedTypes = ['text/css', 'text/plain'];
                    $fileType = mime_content_type($fileTmp);
                    
                    if (!in_array($fileType, $allowedTypes) || !preg_match('/\.css$/i', $fileName)) {
                        $error = 'Please upload a valid CSS file';
                    } elseif ($fileSize > 1048576) { // 1MB limit
                        $error = 'CSS file size must be less than 1MB';
                    } else {
                        // Backup current CSS
                        $currentCSS = __DIR__ . '/../styles.css';
                        $backupDir = __DIR__ . '/theme_backups';
                        
                        if (!is_dir($backupDir)) {
                            mkdir($backupDir, 0755, true);
                        }
                        
                        $backupFile = $backupDir . '/styles_' . date('Y-m-d_H-i-s') . '.css';
                        copy($currentCSS, $backupFile);
                        
                        // Upload new CSS
                        if (move_uploaded_file($fileTmp, $currentCSS)) {
                            $message = 'CSS file uploaded successfully. Previous version backed up.';
                            if (function_exists('logActivity')) {
                                logActivity('THEME_UPLOAD', 'Uploaded new CSS file: ' . $fileName);
                            }
                        } else {
                            $error = 'Failed to upload CSS file';
                        }
                    }
                } else {
                    $error = 'Please select a CSS file to upload';
                }
                break;
                
            case 'update_colors':
                // Handle color updates
                $colors = [
                    'primary_color' => $_POST['primary_color'] ?? '',
                    'secondary_color' => $_POST['secondary_color'] ?? '',
                    'background_color' => $_POST['background_color'] ?? '',
                    'text_color' => $_POST['text_color'] ?? '',
                    'link_color' => $_POST['link_color'] ?? '',
                    'border_color' => $_POST['border_color'] ?? '',
                    'nav_link_color' => $_POST['nav_link_color'] ?? '',
                    'nav_link_hover_bg' => $_POST['nav_link_hover_bg'] ?? '',
                    'nav_link_hover_color' => $_POST['nav_link_hover_color'] ?? ''
                ];
                
                // Generate CSS variables
                $cssVars = ":root {\n";
                foreach ($colors as $key => $value) {
                    if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                        $cssVars .= "    --{$key}: {$value};\n";
                    }
                }
                $cssVars .= "}\n\n";
                
                // Save to custom theme file
                $customCSS = __DIR__ . '/../custom_theme.css';
                file_put_contents($customCSS, $cssVars);
                
                $message = 'Color theme updated successfully';
                if (function_exists('logActivity')) {
                    logActivity('THEME_COLORS', 'Updated theme colors');
                }
                break;
                
            case 'restore_backup':
                // Only Air Boss can restore backups
                if (!$isAirBoss) {
                    $error = 'Only Air Boss can restore theme backups';
                    break;
                }
                // Restore from backup
                $backupFile = $_POST['backup_file'] ?? '';
                $backupPath = __DIR__ . '/theme_backups/' . basename($backupFile);
                
                if (file_exists($backupPath)) {
                    $currentCSS = __DIR__ . '/../styles.css';
                    copy($backupPath, $currentCSS);
                    $message = 'Theme restored from backup';
                    if (function_exists('logActivity')) {
                        logActivity('THEME_RESTORE', 'Restored theme from: ' . $backupFile);
                    }
                } else {
                    $error = 'Backup file not found';
                }
                break;
        }
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Get list of backup files
$backupDir = __DIR__ . '/theme_backups';
$backups = [];
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if (preg_match('/^styles_.*\.css$/', $file)) {
            $backups[] = [
                'filename' => $file,
                'date' => filemtime($backupDir . '/' . $file),
                'size' => filesize($backupDir . '/' . $file)
            ];
        }
    }
    // Sort by date descending
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Load current custom colors if they exist
$customColors = [
    'primary_color' => '#1a1a1a',
    'secondary_color' => '#2a2a2a',
    'background_color' => '#0f0f0f',
    'text_color' => '#e0e0e0',
    'link_color' => '#4a9eff',
    'border_color' => '#333333',
    'nav_link_color' => '#c2d4c9',
    'nav_link_hover_bg' => '#2e3e47',
    'nav_link_hover_color' => '#ffffff'
];

$customCSS = __DIR__ . '/../custom_theme.css';
if (file_exists($customCSS)) {
    $content = file_get_contents($customCSS);
    preg_match_all('/--([a-z_]+):\s*(#[0-9a-fA-F]{6});/', $content, $matches);
    if (!empty($matches[1]) && !empty($matches[2])) {
        foreach ($matches[1] as $i => $varName) {
            if (isset($customColors[$varName])) {
                $customColors[$varName] = $matches[2][$i];
            }
        }
    }
}

// Page title
$pageTitle = 'Theme Management';
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
        
        .theme-section {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .color-inputs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 20px;
            max-width: 1000px;
        }
        
        .color-input-group {
            display: flex;
            align-items: center;
            background: var(--bg-tertiary);
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }
        
        .color-input-group:hover {
            border-color: var(--accent-primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .color-input-group label {
            flex: 1;
            font-size: 0.95em;
            font-weight: 500;
            cursor: pointer;
        }
        
        .color-input-group input[type="color"] {
            width: 60px;
            height: 40px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            cursor: pointer;
            padding: 2px;
            background: var(--bg-secondary);
            transition: all 0.2s ease;
        }
        
        .color-input-group input[type="color"]:hover {
            border-color: var(--accent-primary);
            transform: scale(1.05);
        }
        
        .color-input-group input[type="color"]::-webkit-color-swatch {
            border-radius: 4px;
            border: none;
        }
        
        .color-input-group input[type="color"]::-moz-color-swatch {
            border-radius: 4px;
            border: none;
        }
        
        @media (max-width: 600px) {
            .color-inputs {
                grid-template-columns: 1fr;
            }
        }
        
        .upload-section {
            margin-top: 20px;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-button {
            display: inline-block;
            padding: 10px 20px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-input-button:hover {
            background: var(--bg-primary);
        }
        
        .backup-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .backup-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin-bottom: 5px;
            background: var(--bg-tertiary);
            border-radius: 4px;
        }
        
        .backup-info {
            flex: 1;
        }
        
        .preview-frame {
            width: 100%;
            height: 500px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: #fff;
        }
        
        .theme-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .theme-tab {
            padding: 10px 20px;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .theme-tab.active {
            color: var(--text-primary);
            border-bottom-color: var(--accent-primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Menu Configuration Styles */
        .menu-items {
            margin-top: 20px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            margin-bottom: 10px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .menu-item.dragging {
            opacity: 0.5;
        }
        
        .menu-item.drag-over {
            border-color: var(--accent-primary);
            border-style: dashed;
        }
        
        .menu-item-handle {
            cursor: grab;
            font-size: 20px;
            color: var(--text-muted);
            user-select: none;
        }
        
        .menu-item-handle:active {
            cursor: grabbing;
        }
        
        .menu-item-fields {
            display: flex;
            gap: 15px;
            flex: 1;
            align-items: center;
        }
        
        .menu-item-fields input[type="text"] {
            flex: 1;
            padding: 8px 12px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-primary);
        }
        
        .menu-item-fields input[type="text"]:focus {
            border-color: var(--accent-primary);
            outline: none;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
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
                        <div class="admin-username"><?= e($currentAdmin['username']) ?></div>
                        <div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div>
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
                
                <!-- Theme Preview Section -->
                <div class="theme-section">
                    <h2>Theme Preview</h2>
                    <p>Preview how the site looks with current theme settings:</p>
                    
                    <?php
                    // Build initial preview URL with current colors
                    $previewParams = [
                        'preview' => '1',
                        'primary' => substr($customColors['primary_color'], 1),
                        'secondary' => substr($customColors['secondary_color'], 1),
                        'background' => substr($customColors['background_color'], 1),
                        'text' => substr($customColors['text_color'], 1),
                        'link' => substr($customColors['link_color'], 1),
                        'border' => substr($customColors['border_color'], 1),
                        'navlink' => substr($customColors['nav_link_color'], 1),
                        'navhoverbg' => substr($customColors['nav_link_hover_bg'], 1),
                        'navhover' => substr($customColors['nav_link_hover_color'], 1),
                    ];
                    // Build URL to parent directory
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                    $host = $_SERVER['HTTP_HOST'];
                    $currentPath = dirname($_SERVER['SCRIPT_NAME']);
                    $parentPath = dirname($currentPath);
                    $previewUrl = $protocol . $host . ($parentPath === '/' ? '' : $parentPath) . '/index.php?' . http_build_query($previewParams);
                    ?>
                    <iframe src="<?= $previewUrl ?>" class="preview-frame" id="preview-frame"></iframe>
                    
                    <div style="margin-top: 10px;">
                        <span id="preview-status" style="color: var(--text-muted); font-size: 0.9em;"></span>
                    </div>
                </div>
                
                <!-- Theme Tabs -->
                <div class="theme-tabs">
                    <button class="theme-tab active" onclick="switchTab('simple')">Simple Customization</button>
                    <button class="theme-tab" onclick="switchTab('menu')">Menu Configuration</button>
                    <?php if ($isAirBoss): ?>
                    <button class="theme-tab" onclick="switchTab('advanced')">Advanced CSS Upload</button>
                    <button class="theme-tab" onclick="switchTab('backups')">Backup & Restore</button>
                    <?php endif; ?>
                </div>
                
                <!-- Simple Customization Tab -->
                <div id="simple-tab" class="tab-content active">
                    <div class="theme-section">
                        <h2>Color Customization</h2>
                        <p>Customize the main colors used throughout the site. Changes will be applied immediately.</p>
                        <p style="font-size: 0.9em; color: var(--text-muted); margin-top: 10px;">
                            💡 Tip: Click on a color box to open the color picker. Hover over labels for more details.
                        </p>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="update_colors">
                            
                            <div class="color-inputs">
                                <div class="color-input-group">
                                    <label for="primary_color" title="Navigation bar background">Primary Color (Nav Bar):</label>
                                    <input type="color" id="primary_color" name="primary_color" 
                                           value="<?= htmlspecialchars($customColors['primary_color']) ?>">
                                </div>
                                
                                <div class="color-input-group">
                                    <label for="secondary_color" title="Footer and card backgrounds">Secondary Color (Footer):</label>
                                    <input type="color" id="secondary_color" name="secondary_color" 
                                           value="<?= htmlspecialchars($customColors['secondary_color']) ?>">
                                </div>
                                
                                <div class="color-input-group">
                                    <label for="background_color" title="Main page background">Background Color (Page):</label>
                                    <input type="color" id="background_color" name="background_color" 
                                           value="<?= htmlspecialchars($customColors['background_color']) ?>">
                                </div>
                                
                                <div class="color-input-group">
                                    <label for="text_color" title="General text color">Text Color:</label>
                                    <input type="color" id="text_color" name="text_color" 
                                           value="<?= htmlspecialchars($customColors['text_color']) ?>">
                                </div>
                                
                                <div class="color-input-group">
                                    <label for="link_color" title="Hyperlink color">Link Color:</label>
                                    <input type="color" id="link_color" name="link_color" 
                                           value="<?= htmlspecialchars($customColors['link_color']) ?>">
                                </div>
                                
                                <div class="color-input-group">
                                    <label for="border_color" title="Border and accent color">Border/Accent Color:</label>
                                    <input type="color" id="border_color" name="border_color"
                                           value="<?= htmlspecialchars($customColors['border_color']) ?>">
                                </div>

                                <div class="color-input-group">
                                    <label for="nav_link_color" title="Navigation text color">Navigation Text Color:</label>
                                    <input type="color" id="nav_link_color" name="nav_link_color"
                                           value="<?= htmlspecialchars($customColors['nav_link_color']) ?>">
                                </div>

                                <div class="color-input-group">
                                    <label for="nav_link_hover_bg" title="Navigation hover background">Nav Hover Background:</label>
                                    <input type="color" id="nav_link_hover_bg" name="nav_link_hover_bg"
                                           value="<?= htmlspecialchars($customColors['nav_link_hover_bg']) ?>">
                                </div>

                                <div class="color-input-group">
                                    <label for="nav_link_hover_color" title="Navigation hover text color">Nav Hover Text:</label>
                                    <input type="color" id="nav_link_hover_color" name="nav_link_hover_color"
                                           value="<?= htmlspecialchars($customColors['nav_link_hover_color']) ?>">
                                </div>
                            </div>
                            
                            <div style="margin-top: 20px; display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary">Update Colors</button>
                                <button type="button" class="btn btn-secondary" onclick="restoreDefaultColors()">Restore Defaults</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Menu Configuration Tab -->
                <div id="menu-tab" class="tab-content">
                    <div class="theme-section">
                        <h2>Navigation Menu Configuration</h2>
                        <p>Customize the navigation menu by renaming items, changing their order, or hiding them.</p>
                        
                        <form method="POST" action="" id="menu-form">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="update_menu">
                            
                            <div class="menu-items" id="menu-items">
                                <?php foreach ($menuItems as $index => $item): ?>
                                <div class="menu-item" data-index="<?= $index ?>">
                                    <div class="menu-item-handle">☰</div>
                                    <input type="hidden" name="menu_order[]" value="<?= $index ?>">
                                    <input type="hidden" name="menu_types[<?= $index ?>]" value="<?= htmlspecialchars($item['type'] ?? 'page') ?>">
                                    <div class="menu-item-fields">
                                        <input type="text" name="menu_names[<?= $index ?>]" value="<?= htmlspecialchars($item['name']) ?>" placeholder="Menu Name" required>
                                        <?php if (in_array($item['type'] ?? 'page', ['discord', 'squadron_homepage'])): ?>
                                            <input type="text" name="menu_urls[<?= $index ?>]" value="<?= htmlspecialchars($item['url']) ?>" placeholder="URL" required title="External URL">
                                        <?php else: ?>
                                            <input type="text" name="menu_urls[<?= $index ?>]" value="<?= htmlspecialchars($item['url']) ?>" placeholder="URL" required readonly>
                                        <?php endif; ?>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="menu_enabled[<?= $index ?>]" <?= $item['enabled'] ? 'checked' : '' ?>>
                                            <span>Enabled</span>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Save Menu Configuration</button>
                            <button type="button" class="btn btn-secondary" onclick="resetMenu()" style="margin-top: 20px;">Reset to Default</button>
                        </form>
                    </div>
                </div>
                
                <?php if ($isAirBoss): ?>
                <!-- Advanced CSS Upload Tab -->
                <div id="advanced-tab" class="tab-content">
                    <div class="theme-section">
                        <h2>Upload Custom CSS</h2>
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> Uploading a new CSS file will completely replace the current site styling. 
                            Make sure to backup the current theme first!
                        </div>
                        
                        <form method="POST" action="" enctype="multipart/form-data" class="upload-section">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="upload_css">
                            
                            <div class="file-input-wrapper">
                                <input type="file" name="css_file" id="css_file" accept=".css">
                                <label for="css_file" class="file-input-button">Choose CSS File</label>
                            </div>
                            <span id="file-name" style="margin-left: 10px;">No file selected</span>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-primary">Upload CSS</button>
                            </div>
                        </form>
                        
                        <div style="margin-top: 30px;">
                            <h3>CSS Guidelines</h3>
                            <ul>
                                <li>Maximum file size: 1MB</li>
                                <li>Use CSS variables for easy color management</li>
                                <li>Test thoroughly before uploading</li>
                                <li>Ensure mobile responsiveness</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Backup & Restore Tab -->
                <div id="backups-tab" class="tab-content">
                    <div class="theme-section">
                        <h2>Theme Backups</h2>
                        
                        <?php if (empty($backups)): ?>
                            <p>No backups found.</p>
                        <?php else: ?>
                            <div class="backup-list">
                                <?php foreach ($backups as $backup): ?>
                                    <div class="backup-item">
                                        <div class="backup-info">
                                            <strong><?= htmlspecialchars($backup['filename']) ?></strong><br>
                                            <small>
                                                Created: <?= date('Y-m-d H:i:s', $backup['date']) ?> | 
                                                Size: <?= number_format($backup['size'] / 1024, 2) ?> KB
                                            </small>
                                        </div>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="action" value="restore_backup">
                                            <input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['filename']) ?>">
                                            <button type="submit" class="btn btn-sm" 
                                                    onclick="return confirm('Are you sure you want to restore this backup?')">
                                                Restore
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Tab switching
        function switchTab(tabName) {
            // Remove active class from all tabs and contents
            document.querySelectorAll('.theme-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to selected tab and content
            event.target.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        }
        
        // File input handling
        document.getElementById('css_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file selected';
            document.getElementById('file-name').textContent = fileName;
        });
        
        
        // Debounce function to prevent too many updates
        let updateTimeout;
        function debounce(func, wait) {
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(updateTimeout);
                    func(...args);
                };
                clearTimeout(updateTimeout);
                updateTimeout = setTimeout(later, wait);
            };
        }
        
        // Live color preview
        function updatePreviewColors() {
            const iframe = document.getElementById('preview-frame');
            
            // Get current color values (remove # from hex colors for URL)
            const colors = {
                'primary': document.getElementById('primary_color').value.replace('#', ''),
                'secondary': document.getElementById('secondary_color').value.replace('#', ''),
                'background': document.getElementById('background_color').value.replace('#', ''),
                'text': document.getElementById('text_color').value.replace('#', ''),
                'link': document.getElementById('link_color').value.replace('#', ''),
                'border': document.getElementById('border_color').value.replace('#', ''),
                'navlink': document.getElementById('nav_link_color').value.replace('#', ''),
                'navhoverbg': document.getElementById('nav_link_hover_bg').value.replace('#', ''),
                'navhover': document.getElementById('nav_link_hover_color').value.replace('#', '')
            };
            
            // Build URL with preview parameters
            const params = new URLSearchParams();
            params.set('preview', '1');
            for (const [key, value] of Object.entries(colors)) {
                params.set(key, value);
            }
            
            // Update iframe source with preview parameters
            // Build URL to parent directory  
            const protocol = window.location.protocol;
            const host = window.location.host;
            const currentPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
            const parentPath = currentPath.substring(0, currentPath.lastIndexOf('/'));
            const baseUrl = protocol + '//' + host + parentPath + '/index.php';
            iframe.src = baseUrl + '?' + params.toString();
        }
        
        // Debounced version of updatePreviewColors
        const debouncedUpdate = debounce(updatePreviewColors, 500);
        
        // Add event listeners for real-time updates
        document.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener('input', function() {
                // Show status message immediately
                const status = document.getElementById('preview-status');
                status.textContent = '⏳ Updating preview...';
                status.style.color = '#ff9800';
                
                // Update preview with debounce
                debouncedUpdate();
            });
            
            input.addEventListener('change', function() {
                // Show completed message
                const status = document.getElementById('preview-status');
                status.textContent = '✨ Preview updated';
                status.style.color = '#4CAF50';
                setTimeout(() => {
                    status.textContent = '';
                }, 2000);
            });
        });
        
        // Menu drag and drop functionality
        let draggedElement = null;
        
        function initMenuDragDrop() {
            const menuItems = document.querySelectorAll('.menu-item');
            
            menuItems.forEach(item => {
                item.draggable = true;
                
                item.addEventListener('dragstart', function(e) {
                    draggedElement = this;
                    this.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', this.innerHTML);
                });
                
                item.addEventListener('dragend', function() {
                    this.classList.remove('dragging');
                });
                
                item.addEventListener('dragover', function(e) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    }
                    e.dataTransfer.dropEffect = 'move';
                    this.classList.add('drag-over');
                    return false;
                });
                
                item.addEventListener('dragleave', function() {
                    this.classList.remove('drag-over');
                });
                
                item.addEventListener('drop', function(e) {
                    if (e.stopPropagation) {
                        e.stopPropagation();
                    }
                    
                    this.classList.remove('drag-over');
                    
                    if (draggedElement !== this) {
                        const container = document.getElementById('menu-items');
                        const allItems = Array.from(container.querySelectorAll('.menu-item'));
                        const draggedIndex = allItems.indexOf(draggedElement);
                        const targetIndex = allItems.indexOf(this);
                        
                        if (draggedIndex < targetIndex) {
                            this.parentNode.insertBefore(draggedElement, this.nextSibling);
                        } else {
                            this.parentNode.insertBefore(draggedElement, this);
                        }
                        
                        // Update order inputs
                        updateMenuOrder();
                    }
                    
                    return false;
                });
            });
        }
        
        function updateMenuOrder() {
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach((item, index) => {
                const orderInput = item.querySelector('input[name="menu_order[]"]');
                orderInput.value = index;
                
                // Update field names to match new order
                const nameInput = item.querySelector('input[name^="menu_names"]');
                const urlInput = item.querySelector('input[name^="menu_urls"]');
                const enabledInput = item.querySelector('input[name^="menu_enabled"]');
                const typeInput = item.querySelector('input[name^="menu_types"]');
                
                nameInput.name = `menu_names[${index}]`;
                urlInput.name = `menu_urls[${index}]`;
                enabledInput.name = `menu_enabled[${index}]`;
                typeInput.name = `menu_types[${index}]`;
            });
        }
        
        function resetMenu() {
            if (confirm('Are you sure you want to reset the menu to default settings?')) {
                // Create a form to submit reset action
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="update_menu">
                    <?php 
                    // Rebuild default menu items for reset
                    $resetMenuItems = [
                        ['name' => 'Home', 'url' => 'index.php', 'enabled' => true, 'type' => 'page'],
                        ['name' => 'Leaderboard', 'url' => 'leaderboard.php', 'enabled' => true, 'type' => 'page'],
                        ['name' => 'Pilot Statistics', 'url' => 'pilot_statistics.php', 'enabled' => true, 'type' => 'page'],
                        ['name' => 'Pilot Credits', 'url' => 'pilot_credits.php', 'enabled' => true, 'type' => 'page'],
                        ['name' => 'Squadrons', 'url' => 'squadrons.php', 'enabled' => true, 'type' => 'page'],
                        ['name' => 'Servers', 'url' => 'servers.php', 'enabled' => true, 'type' => 'page']
                    ];
                    if (isFeatureEnabled('show_discord_link')) {
                        $resetMenuItems[] = ['name' => 'Discord', 'url' => getFeatureValue('discord_link_url', 'https://discord.gg/DNENf6pUNX'), 'enabled' => true, 'type' => 'discord'];
                    }
                    if (isFeatureEnabled('show_squadron_homepage') && !empty(getFeatureValue('squadron_homepage_url'))) {
                        $resetMenuItems[] = ['name' => getFeatureValue('squadron_homepage_text', 'Squadron'), 'url' => getFeatureValue('squadron_homepage_url'), 'enabled' => true, 'type' => 'squadron_homepage'];
                    }
                    foreach ($resetMenuItems as $index => $item): ?>
                    <input type="hidden" name="menu_order[]" value="<?= $index ?>">
                    <input type="hidden" name="menu_names[<?= $index ?>]" value="<?= htmlspecialchars($item['name']) ?>">
                    <input type="hidden" name="menu_urls[<?= $index ?>]" value="<?= htmlspecialchars($item['url']) ?>">
                    <input type="hidden" name="menu_types[<?= $index ?>]" value="<?= htmlspecialchars($item['type']) ?>">
                    <?php if ($item['enabled']): ?>
                    <input type="hidden" name="menu_enabled[<?= $index ?>]" value="1">
                    <?php endif; ?>
                    <?php endforeach; ?>
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Restore default colors
        function restoreDefaultColors() {
            const defaults = {
                'primary_color': '#1a1a1a',
                'secondary_color': '#2a2a2a',
                'background_color': '#121212',
                'text_color': '#ffffff',
                'link_color': '#4a9eff',
                'border_color': '#556b2f',
                'nav_link_color': '#c2d4c9',
                'nav_link_hover_bg': '#2e3e47',
                'nav_link_hover_color': '#ffffff'
            };
            
            // Set the color inputs to default values
            for (const [id, value] of Object.entries(defaults)) {
                document.getElementById(id).value = value;
            }
            
            // Update preview immediately
            updatePreviewColors();
            
            // Show status message
            const status = document.getElementById('preview-status');
            status.textContent = '🔄 Colors restored to defaults';
            status.style.color = '#2196F3';
            setTimeout(() => {
                status.textContent = '';
            }, 3000);
        }
        
        // Initialize drag and drop when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initMenuDragDrop();
            
            // Initialize preview with current colors
            updatePreviewColors();
        });
    </script>
</body>
</html>