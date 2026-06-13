<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/dashboard/styles.php'; ?>
</head>
<body>
    <div class="admin-wrapper">
        <?php require DCS_ROOT_PATH . '/app/Views/Admin/partials/nav.php'; ?>
        
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
                    <a href="logout.php" class="btn btn-secondary btn-small"><?= e(dcs_t('admin.common.logout')) ?></a>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content">
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/dashboard/alerts.php'; ?>
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/dashboard/overview.php'; ?>
                
                <?php require DCS_ROOT_PATH . '/app/Views/Admin/dashboard/lower_grid.php'; ?>
            </div>
        </main>
    </div>
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/dashboard/update_status_script.php'; ?>
</body>
</html>
