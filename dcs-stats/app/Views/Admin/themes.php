<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/styles.php'; ?>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php require DCS_ROOT_PATH . '/app/Views/Admin/partials/nav.php'; ?>
        
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <h1><?= $pageTitle ?></h1>
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
                <div class="card">
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/alerts.php'; ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/preview.php'; ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/tabs.php'; ?>
                
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/simple_tab.php'; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/presets_tab.php'; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/header_image_tab.php'; ?>
                
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/charts_tab.php'; ?>

                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/menu_tab.php'; ?>
                
                <?php if ($isAirBoss): ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/advanced_tab.php'; ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/backups_tab.php'; ?>
                <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/themes/script.php'; ?>
</body>
</html>
