<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= e($pageTitle) ?> - DCS Statistics</title><link rel="stylesheet" href="css/admin.css"><link rel="stylesheet" href="../css/admin/cms.css"></head>
<body><div class="admin-wrapper"><?php require DCS_APP_PATH . '/Views/Admin/partials/nav.php'; ?><main class="admin-main">
<header class="admin-header"><h1><?= e($pageTitle) ?></h1><div class="admin-user-menu"><div class="admin-user-info"><div class="admin-username"><?= e($currentAdmin['username']) ?></div><div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div></div><?php require DCS_APP_PATH . '/Views/Admin/partials/logout_form.php'; ?></div></header>
<div class="admin-content"><?php if ($message): ?><div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>"><?= e($message) ?></div><?php endif; ?>

<section class="card cms-gallery-settings"><div class="card-header"><div><h2 class="card-title"><?= e(dcs_t('admin.cms.download_availability')) ?></h2><p class="text-muted"><?= e(dcs_t('admin.cms.download_enable_help')) ?></p></div></div>
<form method="POST"><?= csrfField() ?><input type="hidden" name="action" value="save_settings"><label class="cms-setting-toggle"><input type="checkbox" name="cms_downloads_enabled" value="1" <?= $downloadsEnabled ? 'checked' : '' ?>><span><strong><?= e(dcs_t('admin.cms.enable_downloads')) ?></strong></span></label><div class="settings-actions"><button class="btn btn-primary" type="submit"><?= e(dcs_t('admin.cms.save_settings')) ?></button></div></form></section>

<?php if ($downloadsEnabled): ?>
<div class="cms-download-admin-grid">
<section class="card"><div class="card-header"><div><h2 class="card-title"><?= e(dcs_t('admin.cms.existing_downloads')) ?></h2><p class="text-muted"><?= e(dcs_t('admin.cms.downloads_help')) ?></p></div><a class="btn btn-primary" href="cms_downloads.php"><?= e(dcs_t('admin.cms.add_downloads')) ?></a></div>
<?php if (!$downloads): ?><p class="cms-empty-state"><?= e(dcs_t('admin.cms.no_downloads')) ?></p><?php endif; ?>
<div class="cms-download-list"><?php foreach ($downloads as $download): ?><article><div><strong><?= e($download['title'] ?? '') ?></strong><small class="text-muted"><?= e(trim((string)($download['category'] ?? '')) !== '' ? (string)$download['category'] : dcs_t('admin.cms.download_uncategorised')) ?><?php if (!empty($download['version'])): ?> · <?= e($download['version']) ?><?php endif; ?> · <?= e(dcs_t(!empty($download['enabled']) ? 'admin.cms.published' : 'admin.cms.draft')) ?></small><span class="cms-download-url"><?= e($download['url'] ?? '') ?></span></div><div class="cms-page-actions"><a class="btn btn-secondary btn-small" href="cms_downloads.php?edit=<?= e($download['id']) ?>"><?= e(dcs_t('admin.cms.edit')) ?></a><form method="POST" onsubmit="return confirm(<?= e(json_encode(dcs_t('admin.cms.confirm_delete_download'))) ?>);"><?= csrfField() ?><input type="hidden" name="action" value="delete_download"><input type="hidden" name="download_id" value="<?= e($download['id']) ?>"><button class="btn btn-danger btn-small" type="submit"><?= e(dcs_t('admin.cms.delete')) ?></button></form></div></article><?php endforeach; ?></div></section>

<section class="card"><div class="card-header"><div><h2 class="card-title"><?= e(dcs_t($editDownload ? 'admin.cms.edit_download' : 'admin.cms.add_download')) ?></h2><p class="text-muted"><?= e(dcs_t('admin.cms.download_form_help')) ?></p></div></div>
<?php if ($editDownload): ?>
<form method="POST" class="cms-download-form"><?= csrfField() ?><input type="hidden" name="action" value="save_download"><input type="hidden" name="download_id" value="<?= e($editDownload['id']) ?>">
<?php $row = $editDownload; $prefix = ''; require DCS_APP_PATH . '/Views/Admin/partials/download_fields.php'; ?>
<div class="settings-actions"><button class="btn btn-primary" type="submit"><?= e(dcs_t('admin.cms.save_download')) ?></button><a class="btn btn-secondary" href="cms_downloads.php"><?= e(dcs_t('admin.cms.cancel_edit')) ?></a></div></form>
<?php else: ?>
<form method="POST" class="cms-download-form"><?= csrfField() ?><input type="hidden" name="action" value="save_download">
<?php $row = ['enabled' => true, 'sort_order' => 100]; $prefix = ''; require DCS_APP_PATH . '/Views/Admin/partials/download_fields.php'; ?>
<div class="settings-actions"><button class="btn btn-primary" type="submit"><?= e(dcs_t('admin.cms.save_download')) ?></button><button class="btn btn-secondary" type="button" data-show-download-batch><?= e(dcs_t('admin.cms.add_multiple_downloads')) ?></button></div></form>
<div class="cms-download-batch-panel" data-download-batch hidden>
<hr><div class="card-header"><div><h3 class="card-title"><?= e(dcs_t('admin.cms.add_multiple_downloads')) ?></h3><p class="text-muted"><?= e(dcs_t('admin.cms.download_batch_help')) ?></p></div></div>
<form method="POST" class="cms-download-form"><?= csrfField() ?><input type="hidden" name="action" value="add_downloads">
<?php for ($i = 0; $i < 5; $i++): ?><fieldset class="cms-download-row"><legend><?= e(dcs_t('admin.cms.download_entry')) ?> <?= $i + 1 ?></legend><?php $row = ['enabled' => true, 'sort_order' => 100 + ($i * 10)]; $prefix = 'downloads[' . $i . ']'; require DCS_APP_PATH . '/Views/Admin/partials/download_fields.php'; ?></fieldset><?php endfor; ?>
<p class="text-muted"><?= e(dcs_t('admin.cms.download_batch_note')) ?></p><div class="settings-actions"><button class="btn btn-primary" type="submit"><?= e(dcs_t('admin.cms.save_downloads')) ?></button><button class="btn btn-secondary" type="button" data-hide-download-batch><?= e(dcs_t('admin.cms.cancel_batch')) ?></button></div></form></div>
<script>document.querySelector('[data-show-download-batch]')?.addEventListener('click',()=>{const p=document.querySelector('[data-download-batch]'); if(p){p.hidden=false; p.scrollIntoView({behavior:'smooth',block:'start'});}});document.querySelector('[data-hide-download-batch]')?.addEventListener('click',()=>{const p=document.querySelector('[data-download-batch]'); if(p) p.hidden=true;});</script>
<?php endif; ?>
</section></div>
<?php endif; ?>
</div></main></div></body></html>
