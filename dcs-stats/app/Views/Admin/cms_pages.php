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
            <div class="admin-user-menu">
                <div class="admin-user-info"><div class="admin-username"><?= e($currentAdmin['username']) ?></div><div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div></div>
                <?php require DCS_APP_PATH . '/Views/Admin/partials/logout_form.php'; ?>
            </div>
        </header>
        <div class="admin-content">
            <?php if ($message): ?><div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>"><?= e($message) ?></div><?php endif; ?>

            <div class="card">
                <div class="card-header"><h2 class="card-title"><?= e($editPage ? dcs_t('admin.cms.edit_page') : dcs_t('admin.cms.create_page')) ?></h2></div>
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="save_page">
                    <input type="hidden" name="page_id" value="<?= e($editPage['id'] ?? '') ?>">
                    <div class="form-group"><label for="cms_title"><?= e(dcs_t('admin.cms.title')) ?></label><input class="form-control" id="cms_title" name="title" maxlength="120" required value="<?= e($editPage['title'] ?? '') ?>"></div>
                    <div class="form-group"><label for="cms_slug"><?= e(dcs_t('admin.cms.slug')) ?></label><input class="form-control" id="cms_slug" name="slug" maxlength="80" pattern="[a-z0-9-]+" required value="<?= e($editPage['slug'] ?? '') ?>" placeholder="recruitment"></div>
                    <div class="form-group"><label for="cms_content"><?= e(dcs_t('admin.cms.content')) ?></label><textarea class="form-control" id="cms_content" name="content" rows="12" maxlength="100000" required><?= e($editPage['content'] ?? '') ?></textarea></div>
                    <div class="setting-item"><input type="checkbox" id="cms_published" name="published" value="1" <?= !empty($editPage['published']) ? 'checked' : '' ?>><label for="cms_published"><?= e(dcs_t('admin.cms.published')) ?></label></div>
                    <div class="setting-item"><input type="checkbox" id="cms_navigation" name="show_in_navigation" value="1" <?= !empty($editPage['show_in_navigation']) ? 'checked' : '' ?>><label for="cms_navigation"><?= e(dcs_t('admin.cms.show_navigation')) ?></label></div>
                    <div class="settings-actions"><button class="btn btn-primary" type="submit"><?= e(dcs_t('admin.cms.save_page')) ?></button><?php if ($editPage): ?><a class="btn btn-secondary" href="cms_pages.php"><?= e(dcs_t('admin.cms.cancel')) ?></a><?php endif; ?></div>
                </form>
            </div>

            <div class="card">
                <div class="card-header"><h2 class="card-title"><?= e(dcs_t('admin.cms.existing_pages')) ?></h2></div>
                <?php if (!$pages): ?><p class="text-muted"><?= e(dcs_t('admin.cms.no_pages')) ?></p><?php endif; ?>
                <?php foreach ($pages as $cmsPage): ?>
                    <div class="setting-item" style="justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--border-color); padding: 12px 0;">
                        <span><strong><?= e($cmsPage['title'] ?? '') ?></strong><br><small class="text-muted">page.php?slug=<?= e($cmsPage['slug'] ?? '') ?> · <?= !empty($cmsPage['published']) ? e(dcs_t('admin.cms.published')) : e(dcs_t('admin.cms.draft')) ?></small></span>
                        <span><a class="btn btn-secondary btn-small" href="cms_pages.php?edit=<?= e($cmsPage['id'] ?? '') ?>"><?= e(dcs_t('admin.cms.edit')) ?></a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('<?= e(dcs_t('admin.cms.confirm_delete')) ?>');"><?= csrfField() ?><input type="hidden" name="action" value="delete_page"><input type="hidden" name="page_id" value="<?= e($cmsPage['id'] ?? '') ?>"><button class="btn btn-danger btn-small" type="submit"><?= e(dcs_t('admin.cms.delete')) ?></button></form></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
