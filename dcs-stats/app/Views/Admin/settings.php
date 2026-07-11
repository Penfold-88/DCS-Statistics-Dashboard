<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/settings/styles.php'; ?>
</head>
<body>
    <div class="admin-wrapper">
        <?php require DCS_ROOT_PATH . '/app/Views/Admin/partials/nav.php'; ?>
        
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
                    <?php require DCS_APP_PATH . '/Views/Admin/partials/logout_form.php'; ?>
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
                
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/settings/feature_form.php'; ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/settings/impact_guide.php'; ?>
            </div>
        </main>
    </div>
    
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/settings/form_script.php'; ?>
</body>
</html>
