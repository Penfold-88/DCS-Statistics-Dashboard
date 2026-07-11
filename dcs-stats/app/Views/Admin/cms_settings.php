<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - DCS Statistics</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="../css/admin/cms.css">
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
            <form method="POST" class="cms-settings-form">
                <?= csrfField() ?>
                <div class="cms-settings-grid">
                    <section class="card cms-setting-card">
                        <div class="cms-setting-card-icon">⚡</div>
                        <div class="cms-setting-card-content">
                            <div class="card-header"><h2 class="card-title"><?= e(dcs_t('admin.cms.availability')) ?></h2></div>
                            <p class="text-muted"><?= e(dcs_t('admin.cms.settings_help')) ?></p>
                            <label class="cms-setting-toggle" for="cms_enabled">
                                <input type="checkbox" id="cms_enabled" name="cms_enabled" value="1" <?= $cmsEnabled ? 'checked' : '' ?>>
                                <span><strong><?= e(dcs_t('admin.nav.enable_cms')) ?></strong><small><?= e(dcs_t('admin.cms.enable_help')) ?></small></span>
                            </label>
                        </div>
                    </section>

                    <section class="card cms-setting-card">
                        <div class="cms-setting-card-icon">🏠</div>
                        <div class="cms-setting-card-content">
                            <div class="card-header"><h2 class="card-title"><?= e(dcs_t('admin.cms.homepage_label')) ?></h2></div>
                            <p class="text-muted"><?= e(dcs_t('admin.cms.homepage_help')) ?></p>
                            <div class="form-group cms-landing-select">
                                <label for="cms_homepage_page_id"><?= e(dcs_t('admin.cms.homepage_choice')) ?></label>
                                <select class="form-control" id="cms_homepage_page_id" name="cms_homepage_page_id">
                                    <option value=""><?= e(dcs_t('admin.cms.homepage_dashboard')) ?></option>
                                    <?php foreach ($publishedPages as $publishedPage): ?>
                                        <option value="<?= e($publishedPage['id']) ?>" <?= $homepagePageId === $publishedPage['id'] ? 'selected' : '' ?>><?= e($publishedPage['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="help-text"><?= e(dcs_t('admin.cms.homepage_menu_help')) ?></div>
                                <?php if ($homepageFallback): ?><div class="alert alert-warning mt-1"><?= e(dcs_t('admin.cms.homepage_fallback')) ?></div><?php endif; ?>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="card cms-settings-actions">
                    <div><strong><?= e(dcs_t('admin.cms.settings_actions')) ?></strong><small class="text-muted"><?= e(dcs_t('admin.cms.settings_actions_help')) ?></small></div>
                    <div class="settings-actions"><button class="btn btn-primary" type="submit"><?= e(dcs_t('admin.cms.save_settings')) ?></button><?php if ($cmsEnabled): ?><a class="btn btn-secondary" href="cms_pages.php"><?= e(dcs_t('admin.cms.manage_pages')) ?></a><?php endif; ?></div>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
