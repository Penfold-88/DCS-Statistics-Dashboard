<?php
/**
 * Discord Link Settings Page
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once dirname(__DIR__) . '/language.php';
require_once dirname(__DIR__) . '/site_features.php';

// Require admin login and permission
requireAdmin();
requirePermission('manage_discord');

// Get current admin
$currentAdmin = getCurrentAdmin();
$demoRestricted = isDemoRestricted($currentAdmin);

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = dcs_t('admin.discord.csrf_invalid');
        $messageType = 'error';
    } elseif ($demoRestricted) {
        $message = demoWriteLockMessage();
        $messageType = 'error';
    } else {
        // Load current features
        $currentFeatures = loadSiteFeatures();
        
        // Update Discord settings
        $currentFeatures['discord_link_url'] = trim($_POST['discord_link_url'] ?? '');
        $currentFeatures['show_discord_link'] = isset($_POST['show_discord_link']);
        
        // Validate URL
        if ($currentFeatures['show_discord_link'] && !empty($currentFeatures['discord_link_url'])) {
            if (!filter_var($currentFeatures['discord_link_url'], FILTER_VALIDATE_URL)) {
                $message = dcs_t('admin.discord.invalid_url');
                $messageType = 'error';
            }
        }
        
        if (empty($message)) {
            // Save settings
            if (saveSiteFeatures($currentFeatures)) {
                logAdminActivity('SETTINGS_CHANGE', $_SESSION['admin_id'], 'settings', 'discord_link', $currentFeatures);
                $message = dcs_t('admin.discord.save_success');
                $messageType = 'success';
            } else {
                $message = dcs_t('admin.discord.save_failed');
                $messageType = 'error';
            }
        }
    }
}

// Load current settings
$currentFeatures = loadSiteFeatures();

// Page title
$pageTitle = dcs_t('admin.discord.title');
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
                
                <!-- Discord Settings Form -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.discord.configuration')) ?></h2>
                    </div>
                    
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        
                        <div class="form-group">
                            <div class="setting-item">
                                <input type="checkbox" 
                                       id="show_discord_link" 
                                       name="show_discord_link" 
                                       value="1"
                                       <?= ($currentFeatures['show_discord_link'] ?? false) ? 'checked' : '' ?>>
                                <label for="show_discord_link"><?= e(dcs_t('admin.discord.show_in_navigation')) ?></label>
                            </div>
                            <small class="text-muted"><?= e(dcs_t('admin.discord.show_help')) ?></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="discord_link_url"><?= e(dcs_t('admin.discord.invite_url')) ?></label>
                            <input type="url" 
                                   id="discord_link_url" 
                                   name="discord_link_url" 
                                   class="form-control" 
                                   value="<?= e($currentFeatures['discord_link_url'] ?? 'https://discord.gg/DNENf6pUNX') ?>"
                                   placeholder="https://discord.gg/YourInvite"
                                   required>
                            <small class="text-muted">
                                <?= e(dcs_t('admin.discord.invite_help')) ?>
                                <strong><?= e(dcs_t('admin.discord.tip_label')) ?></strong> <?= e(dcs_t('admin.discord.tip_text')) ?>
                            </small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.discord.save_button')) ?></button>
                            <a href="settings.php" class="btn btn-secondary"><?= e(dcs_t('admin.discord.back_to_features')) ?></a>
                        </div>
                    </form>
                </div>
                
                <!-- Help Section -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= e(dcs_t('admin.discord.help_title')) ?></h3>
                    </div>
                    
                    <div class="help-content">
                        <h4><?= e(dcs_t('admin.discord.create_invite_title')) ?></h4>
                        <ol>
                            <li><?= e(dcs_t('admin.discord.step_open_server')) ?></li>
                            <li><?= e(dcs_t('admin.discord.step_right_click_channel')) ?></li>
                            <li><?= e(dcs_t('admin.discord.step_invite_people')) ?></li>
                            <li><?= e(dcs_t('admin.discord.step_edit_invite')) ?></li>
                            <li><?= e(dcs_t('admin.discord.step_never_expire')) ?></li>
                            <li><?= e(dcs_t('admin.discord.step_copy_link')) ?></li>
                        </ol>
                        
                        <h4><?= e(dcs_t('admin.discord.best_practices')) ?></h4>
                        <ul>
                            <li><?= e(dcs_t('admin.discord.practice_permanent')) ?></li>
                            <li><?= e(dcs_t('admin.discord.practice_welcome_channel')) ?></li>
                            <li><?= e(dcs_t('admin.discord.practice_test_regularly')) ?></li>
                            <li><?= e(dcs_t('admin.discord.practice_vanity_url')) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
