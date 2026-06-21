<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= e($pageTitle) ?> - DCS Statistics</title><link rel="stylesheet" href="css/admin.css"></head>
<body><div class="admin-wrapper">
<?php require DCS_APP_PATH . '/Views/Admin/partials/nav.php'; ?>
<main class="admin-main">
<header class="admin-header"><h1><?= e($pageTitle) ?></h1><div class="admin-user-menu"><div class="admin-user-info"><div class="admin-username"><?= e($currentAdmin['username']) ?></div><div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div></div><?php require DCS_APP_PATH . '/Views/Admin/partials/logout_form.php'; ?></div></header>
<div class="admin-content">
<?php if ($message): ?><div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>"><?= e($message) ?></div><?php endif; ?>
<div class="card">
<div class="card-header cms-pages-header"><div><h2 class="card-title"><?= e(dcs_t('admin.cms.existing_pages')) ?></h2><p class="text-muted"><?= e(dcs_t('admin.cms.pages_help')) ?></p></div><a class="btn btn-primary" href="cms_page_edit.php"><?= e(dcs_t('admin.cms.add_new_page')) ?></a></div>
<?php if (!$pages): ?><div class="cms-empty-state"><p><?= e(dcs_t('admin.cms.no_pages')) ?></p><a class="btn btn-primary" href="cms_page_edit.php"><?= e(dcs_t('admin.cms.create_page')) ?></a></div><?php endif; ?>
<div class="cms-page-list">
<?php foreach ($pages as $cmsPage): ?>
<article class="cms-page-row">
<div class="cms-page-summary"><strong><?= e($cmsPage['title'] ?? '') ?></strong><code>page.php?slug=<?= e($cmsPage['slug'] ?? '') ?></code><small class="text-muted"><?= e(dcs_t('admin.cms.updated')) ?> <?= e(isset($cmsPage['updated_at']) ? formatDate($cmsPage['updated_at']) : '') ?></small></div>
<div class="cms-page-badges">
<span class="cms-badge <?= !empty($cmsPage['published']) ? 'is-published' : 'is-draft' ?>"><?= e(dcs_t(!empty($cmsPage['published']) ? 'admin.cms.published' : 'admin.cms.draft')) ?></span>
<?php if (!empty($cmsPage['show_in_navigation'])): ?><span class="cms-badge is-navigation"><?= e(dcs_t('admin.cms.navigation_badge')) ?></span><?php endif; ?>
<?php if (($cmsPage['id'] ?? '') === $landingPageId): ?><span class="cms-badge is-homepage"><?= e(dcs_t('admin.cms.landing_badge')) ?></span><?php endif; ?>
</div>
<div class="cms-page-actions">
<a class="btn btn-secondary btn-small" href="../page_preview.php?id=<?= e($cmsPage['id']) ?>" target="_blank" rel="noopener"><?= e(dcs_t('admin.cms.preview')) ?></a>
<a class="btn btn-secondary btn-small" href="cms_page_edit.php?edit=<?= e($cmsPage['id']) ?>"><?= e(dcs_t('admin.cms.edit')) ?></a>
<form method="POST"><?= csrfField() ?><input type="hidden" name="action" value="duplicate_page"><input type="hidden" name="page_id" value="<?= e($cmsPage['id']) ?>"><button class="btn btn-secondary btn-small" type="submit"><?= e(dcs_t('admin.cms.duplicate')) ?></button></form>
<form method="POST" onsubmit="return confirm(<?= e(json_encode(dcs_t('admin.cms.confirm_delete'))) ?>);">
<?= csrfField() ?><input type="hidden" name="action" value="delete_page"><input type="hidden" name="page_id" value="<?= e($cmsPage['id']) ?>"><button class="btn btn-danger btn-small" type="submit"><?= e(dcs_t('admin.cms.delete')) ?></button></form>
</div></article>
<?php endforeach; ?>
</div></div></div></main></div></body></html>
