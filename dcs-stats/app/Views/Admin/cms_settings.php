<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - DCS Statistics</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-wrapper">
    <?php require DCS_APP_PATH . '/Views/Admin/partials/nav.php'; ?>
    <main class="admin-main">
        <header class="admin-header">
            <h1><?= e($pageTitle) ?></h1>
            <div class="admin-user-menu"><div class="admin-user-info"><div class="admin-username"><?= e($currentAdmin['username']) ?></div><div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div></div><?php require DCS_APP_PATH . '/Views/Admin/partials/logout_form.php'; ?></div>
        </header>
        <div class="admin-content">
            <?php if ($message): ?><div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>"><?= e($message) ?></div><?php endif; ?>
            <div class="card">
                <div class="card-header"><h2 class="card-title"><?= e(dcs_t('admin.cms.settings')) ?></h2></div>
                <p class="text-muted"><?= e(dcs_t('admin.cms.settings_help')) ?></p>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="setting-item"><input type="checkbox" id="cms_enabled" name="cms_enabled" value="1" <?= $cmsEnabled ? 'checked' : '' ?>><label for="cms_enabled"><?= e(dcs_t('admin.nav.enable_cms')) ?></label></div>
                    <div class="form-group">
                        <label for="cms_homepage_page_id"><?= e(dcs_t('admin.cms.homepage_label')) ?></label>
                        <select class="form-control" id="cms_homepage_page_id" name="cms_homepage_page_id">
                            <option value=""><?= e(dcs_t('admin.cms.homepage_dashboard')) ?></option>
                            <?php foreach ($publishedPages as $publishedPage): ?>
                                <option value="<?= e($publishedPage['id']) ?>" <?= $homepagePageId === $publishedPage['id'] ? 'selected' : '' ?>><?= e($publishedPage['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="help-text"><?= e(dcs_t('admin.cms.homepage_help')) ?></div>
                        <?php if ($homepageFallback): ?><div class="alert alert-warning mt-1"><?= e(dcs_t('admin.cms.homepage_fallback')) ?></div><?php endif; ?>
                    </div>
                    <div class="settings-actions"><button class="btn btn-primary" type="submit"><?= e(dcs_t('admin.cms.save_settings')) ?></button><?php if ($cmsEnabled): ?><a class="btn btn-secondary" href="cms_pages.php"><?= e(dcs_t('admin.cms.manage_pages')) ?></a><?php endif; ?></div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>
