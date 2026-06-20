<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - DCS Statistics</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php
$storedContent = (string)($editPage['content'] ?? '');
$editorContent = ($editPage['content_format'] ?? 'plain_text') === 'rich_html'
    ? (new \DcsStats\Services\Cms\CmsHtmlSanitizer())->sanitize($storedContent)
    : ($storedContent !== '' ? '<p>' . nl2br(e($storedContent)) . '</p>' : '<p><br></p>');
?>
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
                <form method="POST" id="cms-page-form">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="save_page">
                    <input type="hidden" name="page_id" value="<?= e($editPage['id'] ?? '') ?>">
                    <div class="form-group"><label for="cms_title"><?= e(dcs_t('admin.cms.title')) ?></label><input class="form-control" id="cms_title" name="title" maxlength="120" required value="<?= e($editPage['title'] ?? '') ?>"></div>
                    <div class="form-group"><label for="cms_slug"><?= e(dcs_t('admin.cms.slug')) ?></label><input class="form-control" id="cms_slug" name="slug" maxlength="80" pattern="[a-z0-9-]+" required value="<?= e($editPage['slug'] ?? '') ?>" placeholder="recruitment"></div>
                    <div class="form-group">
                        <label for="cms_content_editor"><?= e(dcs_t('admin.cms.content')) ?></label>
                        <div class="cms-editor" data-cms-editor>
                            <div class="cms-editor-toolbar" role="toolbar" aria-label="<?= e(dcs_t('admin.cms.editor_toolbar')) ?>">
                                <select class="cms-editor-format" data-editor-format aria-label="<?= e(dcs_t('admin.cms.text_style')) ?>">
                                    <option value="p"><?= e(dcs_t('admin.cms.style_paragraph')) ?></option>
                                    <option value="h2"><?= e(dcs_t('admin.cms.style_heading_2')) ?></option>
                                    <option value="h3"><?= e(dcs_t('admin.cms.style_heading_3')) ?></option>
                                    <option value="h4"><?= e(dcs_t('admin.cms.style_heading_4')) ?></option>
                                    <option value="blockquote"><?= e(dcs_t('admin.cms.style_quote')) ?></option>
                                </select>
                                <span class="cms-editor-divider"></span>
                                <button type="button" data-editor-command="bold" title="<?= e(dcs_t('admin.cms.bold')) ?>" aria-label="<?= e(dcs_t('admin.cms.bold')) ?>"><strong>B</strong></button>
                                <button type="button" data-editor-command="italic" title="<?= e(dcs_t('admin.cms.italic')) ?>" aria-label="<?= e(dcs_t('admin.cms.italic')) ?>"><em>I</em></button>
                                <button type="button" data-editor-command="underline" title="<?= e(dcs_t('admin.cms.underline')) ?>" aria-label="<?= e(dcs_t('admin.cms.underline')) ?>"><u>U</u></button>
                                <span class="cms-editor-divider"></span>
                                <button type="button" data-editor-command="insertUnorderedList" title="<?= e(dcs_t('admin.cms.bullet_list')) ?>" aria-label="<?= e(dcs_t('admin.cms.bullet_list')) ?>">• ≡</button>
                                <button type="button" data-editor-command="insertOrderedList" title="<?= e(dcs_t('admin.cms.numbered_list')) ?>" aria-label="<?= e(dcs_t('admin.cms.numbered_list')) ?>">1. ≡</button>
                                <button type="button" data-editor-link title="<?= e(dcs_t('admin.cms.add_link')) ?>" aria-label="<?= e(dcs_t('admin.cms.add_link')) ?>">🔗</button>
                                <button type="button" data-editor-command="removeFormat" title="<?= e(dcs_t('admin.cms.clear_formatting')) ?>" aria-label="<?= e(dcs_t('admin.cms.clear_formatting')) ?>">Tx</button>
                                <span class="cms-editor-divider"></span>
                                <button type="button" data-editor-command="undo" title="<?= e(dcs_t('admin.cms.undo')) ?>" aria-label="<?= e(dcs_t('admin.cms.undo')) ?>">↶</button>
                                <button type="button" data-editor-command="redo" title="<?= e(dcs_t('admin.cms.redo')) ?>" aria-label="<?= e(dcs_t('admin.cms.redo')) ?>">↷</button>
                            </div>
                            <div class="cms-editor-content" id="cms_content_editor" contenteditable="true" role="textbox" aria-multiline="true"><?= $editorContent ?></div>
                            <textarea id="cms_content" name="content" maxlength="200000" hidden><?= e($storedContent) ?></textarea>
                        </div>
                        <small class="text-muted"><?= e(dcs_t('admin.cms.editor_help')) ?></small>
                    </div>
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
<script>
window.DCS_CMS_EDITOR_TEXT = <?= json_encode([
    'linkPrompt' => dcs_t('admin.cms.link_prompt'),
    'contentRequired' => dcs_t('admin.cms.content_required'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="../js/admin/cms-editor.js"></script>
</body>
</html>
