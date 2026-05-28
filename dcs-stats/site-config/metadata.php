<?php
/**
 * Privacy and SEO Settings
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once dirname(__DIR__) . '/language.php';
require_once dirname(__DIR__) . '/site_metadata.php';

requireAdmin();
requirePermission('manage_features');

$currentAdmin = getCurrentAdmin();
$demoRestricted = isDemoRestricted($currentAdmin);
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = dcs_t('admin.metadata.csrf_invalid');
        $messageType = 'error';
    } elseif ($demoRestricted) {
        $message = demoRestrictionMessage();
        $messageType = 'error';
    } else {
        $metadata = [
            'description' => trim($_POST['description'] ?? ''),
            'keywords' => trim($_POST['keywords'] ?? ''),
            'block_search_engines' => isset($_POST['block_search_engines']),
            'show_privacy_link' => isset($_POST['show_privacy_link']),
            'privacy_notice' => trim($_POST['privacy_notice'] ?? '')
        ];

        if (strlen($metadata['description']) > 320) {
            $message = dcs_t('admin.metadata.error_description_length');
            $messageType = 'error';
        } elseif (strlen($metadata['keywords']) > 500) {
            $message = dcs_t('admin.metadata.error_keywords_length');
            $messageType = 'error';
        } elseif (strlen($metadata['privacy_notice']) > 5000) {
            $message = dcs_t('admin.metadata.error_privacy_length');
            $messageType = 'error';
        } elseif (saveSiteMetadata($metadata)) {
            logAdminActivity('METADATA_UPDATE', $_SESSION['admin_id'], 'settings', 'metadata', [
                'description_length' => strlen($metadata['description']),
                'keywords_length' => strlen($metadata['keywords']),
                'block_search_engines' => $metadata['block_search_engines'],
                'show_privacy_link' => $metadata['show_privacy_link'],
                'privacy_notice_length' => strlen($metadata['privacy_notice'])
            ]);
            $message = dcs_t('admin.metadata.save_success');
            $messageType = 'success';
        } else {
            $message = dcs_t('admin.metadata.save_failed');
            $messageType = 'error';
        }
    }
}

$metadata = loadSiteMetadata();
$pageTitle = dcs_t('admin.metadata.title');
?>
<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .metadata-help {
            background-color: rgba(76, 175, 80, 0.08);
            border: 1px solid rgba(76, 175, 80, 0.28);
            border-radius: 6px;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 20px;
            padding: 15px;
        }

        .character-count {
            color: var(--text-muted);
            display: block;
            font-size: 12px;
            margin-top: 6px;
        }

        textarea.form-control {
            min-height: 110px;
            resize: vertical;
        }

        .usage-data-disclaimer {
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.5;
            margin: 18px 0 0;
        }
    </style>
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

            <?php if ($demoRestricted): ?>
                <div class="alert alert-warning">
                    <?= e(demoRestrictionMessage()) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?= e(dcs_t('admin.metadata.search_metadata')) ?></h2>
                </div>

                <div class="metadata-help">
                    <?= e(dcs_t('admin.metadata.help')) ?>
                </div>

                <form method="POST">
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label for="description"><?= e(dcs_t('admin.metadata.description')) ?></label>
                        <textarea id="description" name="description" class="form-control" maxlength="320" <?= $demoRestricted ? 'disabled' : '' ?>><?= e($metadata['description'] ?? '') ?></textarea>
                        <span class="character-count"><?= e(dcs_t('admin.metadata.description_help')) ?></span>
                    </div>

                    <div class="form-group">
                        <label for="keywords"><?= e(dcs_t('admin.metadata.keywords')) ?></label>
                        <textarea id="keywords" name="keywords" class="form-control" maxlength="500" <?= $demoRestricted ? 'disabled' : '' ?>><?= e($metadata['keywords'] ?? '') ?></textarea>
                        <span class="character-count"><?= e(dcs_t('admin.metadata.keywords_help')) ?></span>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="block_search_engines" value="1" <?= !empty($metadata['block_search_engines']) ? 'checked' : '' ?> <?= $demoRestricted ? 'disabled' : '' ?>>
                            <?= e(dcs_t('admin.metadata.block_search_engines')) ?>
                        </label>
                    </div>

                    <div class="card" style="margin-top: 24px;">
                        <div class="card-header">
                            <h3 class="card-title"><?= e(dcs_t('admin.metadata.privacy_notice')) ?></h3>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="show_privacy_link" value="1" <?= !empty($metadata['show_privacy_link']) ? 'checked' : '' ?> <?= $demoRestricted ? 'disabled' : '' ?>>
                                <?= e(dcs_t('admin.metadata.show_privacy_link')) ?>
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="privacy_notice"><?= e(dcs_t('admin.metadata.privacy_notice_text')) ?></label>
                            <textarea id="privacy_notice" name="privacy_notice" class="form-control" maxlength="5000" <?= $demoRestricted ? 'disabled' : '' ?>><?= e($metadata['privacy_notice'] ?? '') ?></textarea>
                            <span class="character-count"><?= e(dcs_t('admin.metadata.privacy_notice_help')) ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" <?= $demoRestricted ? 'disabled' : '' ?>><?= e(dcs_t('admin.metadata.save_button')) ?></button>
                </form>

                <p class="usage-data-disclaimer"><?= e(dcs_t('admin.metadata.usage_data_disclaimer')) ?></p>
            </div>
        </div>
    </main>
</div>
</body>
</html>
