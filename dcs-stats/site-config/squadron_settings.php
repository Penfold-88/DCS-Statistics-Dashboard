<?php
/**
 * Squadron Homepage Settings Page
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once dirname(__DIR__) . '/language.php';
require_once dirname(__DIR__) . '/site_features.php';

// Require admin login and permission
requireAdmin();
requirePermission('manage_squadrons');

// Get current admin
$currentAdmin = getCurrentAdmin();
$demoRestricted = isDemoRestricted($currentAdmin);

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = dcs_t('admin.squadron_settings.csrf_invalid');
        $messageType = 'error';
    } elseif ($demoRestricted) {
        $message = demoWriteLockMessage();
        $messageType = 'error';
    } else {
        // Load current features
        $currentFeatures = loadSiteFeatures();
        
        // Update Squadron settings
        $currentFeatures['show_squadron_homepage'] = isset($_POST['show_squadron_homepage']);
        $currentFeatures['squadron_homepage_url'] = trim($_POST['squadron_homepage_url'] ?? '');
        $currentFeatures['squadron_homepage_text'] = trim($_POST['squadron_homepage_text'] ?? 'Squadron');
        
        // Validate inputs
        if ($currentFeatures['show_squadron_homepage']) {
            if (empty($currentFeatures['squadron_homepage_url'])) {
                $message = dcs_t('admin.squadron_settings.url_required');
                $messageType = 'error';
            } elseif (!filter_var($currentFeatures['squadron_homepage_url'], FILTER_VALIDATE_URL)) {
                $message = dcs_t('admin.squadron_settings.invalid_url');
                $messageType = 'error';
            }
            
            if (empty($currentFeatures['squadron_homepage_text'])) {
                $currentFeatures['squadron_homepage_text'] = 'Squadron';
            } elseif (strlen($currentFeatures['squadron_homepage_text']) > 50) {
                $message = dcs_t('admin.squadron_settings.link_text_too_long');
                $messageType = 'error';
            }
        }
        
        if (empty($message)) {
            // Save settings
            if (saveSiteFeatures($currentFeatures)) {
                logAdminActivity('SETTINGS_CHANGE', $_SESSION['admin_id'], 'settings', 'squadron_homepage', $currentFeatures);
                $message = dcs_t('admin.squadron_settings.save_success');
                $messageType = 'success';
            } else {
                $message = dcs_t('admin.squadron_settings.save_failed');
                $messageType = 'error';
            }
        }
    }
}

// Load current settings
$currentFeatures = loadSiteFeatures();

// Page title
$pageTitle = dcs_t('admin.squadron_settings.title');
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
                
                <!-- Squadron Settings Form -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.squadron_settings.configuration')) ?></h2>
                        <p class="text-muted"><?= e(dcs_t('admin.squadron_settings.configuration_help')) ?></p>
                    </div>
                    
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        
                        <div class="form-group">
                            <div class="setting-item">
                                <input type="checkbox" 
                                       id="show_squadron_homepage" 
                                       name="show_squadron_homepage" 
                                       value="1"
                                       <?= ($currentFeatures['show_squadron_homepage'] ?? false) ? 'checked' : '' ?>>
                                <label for="show_squadron_homepage"><?= e(dcs_t('admin.squadron_settings.enable_link')) ?></label>
                            </div>
                            <small class="text-muted"><?= e(dcs_t('admin.squadron_settings.enable_help')) ?></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="squadron_homepage_url"><?= e(dcs_t('admin.squadron_settings.homepage_url')) ?></label>
                            <input type="url" 
                                   id="squadron_homepage_url" 
                                   name="squadron_homepage_url" 
                                   class="form-control" 
                                   value="<?= e($currentFeatures['squadron_homepage_url'] ?? '') ?>"
                                   placeholder="https://your-squadron-website.com">
                            <small class="text-muted"><?= e(dcs_t('admin.squadron_settings.url_help')) ?></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="squadron_homepage_text"><?= e(dcs_t('admin.squadron_settings.link_text')) ?></label>
                            <input type="text" 
                                   id="squadron_homepage_text" 
                                   name="squadron_homepage_text" 
                                   class="form-control" 
                                   value="<?= e($currentFeatures['squadron_homepage_text'] ?? 'Squadron') ?>"
                                   placeholder="Squadron"
                                   maxlength="50">
                            <small class="text-muted"><?= e(dcs_t('admin.squadron_settings.link_text_help')) ?></small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.squadron_settings.save_button')) ?></button>
                            <a href="settings.php" class="btn btn-secondary"><?= e(dcs_t('admin.discord.back_to_features')) ?></a>
                        </div>
                    </form>
                </div>
                
                <!-- Preview Section -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= e(dcs_t('admin.squadron_settings.preview_title')) ?></h3>
                    </div>
                    
                    <p class="text-muted"><?= e(dcs_t('admin.squadron_settings.preview_help')) ?></p>
                    
                    <div style="background: #2a2a2a; padding: 15px; border-radius: 5px; margin: 15px 0;">
                        <nav style="display: flex; gap: 20px; flex-wrap: wrap;">
                            <span style="color: #4CAF50;"><?= e(dcs_t('nav.home')) ?></span>
                            <span style="color: #4CAF50;"><?= e(dcs_t('nav.leaderboard')) ?></span>
                            <span style="color: #4CAF50;"><?= e(dcs_t('nav.pilot_statistics')) ?></span>
                            <?php if (getFeatureValue('show_discord_link', true)): ?>
                            <span style="color: #4CAF50;">Discord</span>
                            <?php endif; ?>
                            <?php if ($currentFeatures['show_squadron_homepage'] ?? false): ?>
                            <span style="color: #4CAF50; font-weight: bold;">
                                <?= e($currentFeatures['squadron_homepage_text'] ?? 'Squadron') ?>
                            </span>
                            <?php else: ?>
                            <span style="color: #666; font-style: italic;"><?= e(dcs_t('admin.squadron_settings.preview_disabled')) ?></span>
                            <?php endif; ?>
                            <span style="color: #4CAF50;"><?= e(dcs_t('nav.servers')) ?></span>
                        </nav>
                    </div>
                </div>
                
                <!-- Help Section -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= e(dcs_t('admin.squadron_settings.ideas_title')) ?></h3>
                    </div>
                    
                    <div class="help-content">
                        <h4><?= e(dcs_t('admin.squadron_settings.common_links')) ?></h4>
                        <ul>
                            <li><strong><?= e(dcs_t('admin.squadron_settings.official_website')) ?></strong> <?= e(dcs_t('admin.squadron_settings.official_website_desc')) ?></li>
                            <li><strong><?= e(dcs_t('admin.squadron_settings.forum')) ?></strong> <?= e(dcs_t('admin.squadron_settings.forum_desc')) ?></li>
                            <li><strong><?= e(dcs_t('admin.squadron_settings.training_portal')) ?></strong> <?= e(dcs_t('admin.squadron_settings.training_portal_desc')) ?></li>
                            <li><strong><?= e(dcs_t('admin.squadron_settings.operations_board')) ?></strong> <?= e(dcs_t('admin.squadron_settings.operations_board_desc')) ?></li>
                            <li><strong><?= e(dcs_t('admin.squadron_settings.squadron_tools')) ?></strong> <?= e(dcs_t('admin.squadron_settings.squadron_tools_desc')) ?></li>
                        </ul>
                        
                        <h4><?= e(dcs_t('admin.squadron_settings.link_text_suggestions')) ?></h4>
                        <ul>
                            <li><?= e(dcs_t('admin.squadron_settings.suggestion_generic')) ?></li>
                            <li><?= e(dcs_t('admin.squadron_settings.suggestion_designation')) ?></li>
                            <li><?= e(dcs_t('admin.squadron_settings.suggestion_name')) ?></li>
                            <li><?= e(dcs_t('admin.squadron_settings.suggestion_operations')) ?></li>
                            <li><?= e(dcs_t('admin.squadron_settings.suggestion_training')) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
