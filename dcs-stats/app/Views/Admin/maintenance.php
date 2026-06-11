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
    <?php require DCS_ROOT_PATH . '/app/Views/Admin/partials/nav.php'; ?>
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
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update">
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="enabled" <?= $maintenance['enabled'] ? 'checked' : '' ?>>
                        <?= e(dcs_t('admin.maintenance.enable_mode')) ?>
                    </label>
                </div>
                <div class="form-group">
                    <label for="ip_address"><?= e(dcs_t('admin.maintenance.whitelist_ip')) ?></label>
                    <div class="d-flex gap-1">
                        <input type="text" name="ip_address" id="ip_address" class="form-control" placeholder="127.0.0.1">
                        <button type="button" class="btn btn-secondary" onclick="autofillIP()"><?= e(dcs_t('admin.maintenance.use_my_ip')) ?></button>
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.settings.save_settings')) ?></button>
                </div>
            </form>
            <?php if (!empty($maintenance['ip_whitelist'])): ?>
                <div class="card mt-2">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.maintenance.current_whitelist')) ?></h2>
                    </div>
                    <div class="card-content">
                        <ul class="maintenance-whitelist">
                            <?php foreach ($maintenance['ip_whitelist'] as $ip): ?>
                                <li class="maintenance-whitelist-item">
                                    <span><?= e($ip) ?></span>
                                    <form method="POST" class="maintenance-whitelist-remove">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="remove_ip">
                                        <input type="hidden" name="ip" value="<?= e($ip) ?>">
                                        <button type="submit" class="btn btn-danger btn-small"><?= e(dcs_t('admin.custom_links.remove')) ?></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
function autofillIP() {
    document.getElementById('ip_address').value = '<?= $currentIP ?>';
}
</script>
<style>
.maintenance-whitelist {
    list-style: none;
    margin: 0;
    padding: 0;
}

.maintenance-whitelist-item {
    align-items: center;
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    padding: 0.5rem 0;
}

.maintenance-whitelist-remove {
    margin: 0;
}
</style>
</body>
</html>
