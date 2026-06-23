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
                <div class="card-header"><h2 class="card-title"><?= e(dcs_t('admin.menu_manager.public_navigation')) ?></h2></div>
                <p class="text-muted"><?= e(dcs_t('admin.menu_manager.help')) ?></p>
                <form method="POST" id="menu-manager-form">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <div class="menu-manager-items" id="menu-manager-items">
                    <?php foreach ($items as $index => $item): ?>
                        <?php
                            $typeLabels = [
                                'page' => 'admin.menu_manager.type_statistics',
                                'cms_page' => 'admin.menu_manager.type_cms_page',
                                'cms_downloads' => 'admin.menu_manager.type_cms_downloads',
                                'discord' => 'admin.menu_manager.type_discord',
                                'squadron_homepage' => 'admin.menu_manager.type_squadron',
                                'group' => 'admin.menu_manager.type_group',
                                'external' => 'admin.menu_manager.type_custom_link',
                            ];
                            $typeKey = $typeLabels[$item['type'] ?? 'page'] ?? 'admin.menu_manager.type_statistics';
                            $sourceText = trim((string)($item['url'] ?? ''));
                            if ($sourceText === '') {
                                $sourceText = dcs_t(($item['type'] ?? '') === 'group' ? 'admin.menu_manager.dropdown_group' : 'admin.menu_manager.not_configured');
                            }
                        ?>
                        <div class="menu-manager-item<?= empty($item['available']) ? ' menu-manager-unavailable' : '' ?>" draggable="true">
                            <input type="hidden" data-field="id" name="items[<?= $index ?>][id]" value="<?= e($item['id']) ?>">
                            <div class="menu-manager-item-header">
                                <span class="menu-manager-handle" title="<?= e(dcs_t('admin.menu_manager.drag')) ?>" aria-label="<?= e(dcs_t('admin.menu_manager.drag')) ?>">⋮⋮</span>
                                <span class="menu-manager-type"><?= e(dcs_t($typeKey)) ?></span>
                                <strong class="menu-manager-current-name"><?= e($item['name']) ?></strong>
                                <span class="menu-manager-status <?= !empty($item['available']) ? 'is-available' : 'is-inactive' ?>">
                                    <span class="menu-manager-status-dot"></span>
                                    <?= e(dcs_t(!empty($item['available']) ? 'admin.menu_manager.available' : 'admin.menu_manager.inactive_source')) ?>
                                </span>
                            </div>
                            <div class="menu-manager-fields">
                                <label class="menu-manager-field"><span><?= e(dcs_t('admin.menu_manager.label')) ?></span><input class="form-control" type="text" data-field="name" name="items[<?= $index ?>][name]" value="<?= e($item['name']) ?>" required></label>
                                <label class="menu-manager-field"><span><?= e(dcs_t('admin.menu_manager.parent')) ?></span>
                                    <select class="form-control" data-field="parent_id" name="items[<?= $index ?>][parent_id]">
                                        <option value=""><?= e(dcs_t('admin.menu_manager.top_level')) ?></option>
                                        <?php foreach ($items as $parent): if ($parent['id'] === $item['id']) continue; ?>
                                            <option value="<?= e($parent['id']) ?>" <?= ($item['parent_id'] ?? '') === $parent['id'] ? 'selected' : '' ?>><?= e($parent['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>
                            <div class="menu-manager-item-footer">
                                <span class="menu-manager-source" title="<?= e($sourceText) ?>"><span>↗</span> <?= e($sourceText) ?></span>
                                <div class="menu-manager-toggles">
                                <label class="menu-manager-toggle"><input type="checkbox" data-field="enabled" name="items[<?= $index ?>][enabled]" value="1" <?= !empty($item['enabled']) ? 'checked' : '' ?>><span><?= e(dcs_t('admin.status.enabled')) ?></span></label>
                                <?php if (in_array($item['type'], ['external', 'discord', 'squadron_homepage'], true)): ?>
                                    <label class="menu-manager-toggle"><input type="checkbox" data-field="new_tab" name="items[<?= $index ?>][new_tab]" value="1" <?= !empty($item['new_tab']) ? 'checked' : '' ?>><span><?= e(dcs_t('admin.menu_manager.new_tab')) ?></span></label>
                                <?php else: ?><input type="hidden" data-field="new_tab" name="items[<?= $index ?>][new_tab]" value=""><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <div class="settings-actions"><button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.menu_manager.save')) ?></button></div>
                </form>
            </div>
        </div>
    </main>
</div>
<script src="../js/admin/menu-manager.js"></script>
</body>
</html>
