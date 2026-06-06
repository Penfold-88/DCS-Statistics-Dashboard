<?php
/**
 * Custom Links Settings Page
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once dirname(__DIR__) . '/language.php';
require_once dirname(__DIR__) . '/site_features.php';

requireAdmin();
requirePermission('manage_features');

$currentAdmin = getCurrentAdmin();
$demoRestricted = isDemoRestricted($currentAdmin);
$message = '';
$messageType = '';

function normalizeCustomLinksFromPost($postedLinks, &$message, &$messageType) {
    $customLinks = [];

    foreach ($postedLinks ?? [] as $link) {
        $label = trim($link['label'] ?? '');
        $url = trim($link['url'] ?? '');

        if ($label === '' && $url === '') {
            continue;
        }

        if ($label === '' || $url === '') {
            $message = dcs_t('admin.custom_links.error_label_url_required');
            $messageType = 'error';
            break;
        }

        $isValidUrl = preg_match('#^https?://#i', $url) || strpos($url, '/') === 0;
        if (!$isValidUrl) {
            $message = dcs_t('admin.custom_links.error_invalid_url');
            $messageType = 'error';
            break;
        }

        $customLinks[] = [
            'label' => $label,
            'url' => $url,
            'enabled' => isset($link['enabled']),
            'new_tab' => isset($link['new_tab'])
        ];
    }

    return $customLinks;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = dcs_t('admin.custom_links.csrf_invalid');
        $messageType = 'error';
    } elseif ($demoRestricted) {
        $message = demoWriteLockMessage();
        $messageType = 'error';
    } else {
        $allFeatures = loadSiteFeatures();
        $menuText = trim($_POST['custom_links_menu_text'] ?? 'Squadron Links');
        $allFeatures['custom_links_menu_text'] = $menuText !== '' ? $menuText : 'Squadron Links';
        $allFeatures['custom_links'] = normalizeCustomLinksFromPost($_POST['custom_links'] ?? [], $message, $messageType);

        if ($messageType !== 'error') {
            if (saveSiteFeatures($allFeatures)) {
                logAdminActivity('CUSTOM_LINKS_UPDATE', $_SESSION['admin_id'], 'settings', 'custom_links', [
                    'menu_text' => $allFeatures['custom_links_menu_text'],
                    'link_count' => count($allFeatures['custom_links'])
                ]);
                $message = dcs_t('admin.custom_links.save_success');
                $messageType = 'success';
            } else {
                $message = dcs_t('admin.custom_links.save_failed');
                $messageType = 'error';
            }
        }
    }
}

$currentFeatures = loadSiteFeatures();
$customLinks = $currentFeatures['custom_links'] ?? [];
if (empty($customLinks)) {
    $customLinks = [['label' => '', 'url' => '', 'enabled' => true, 'new_tab' => true]];
}

$pageTitle = dcs_t('admin.custom_links.title');
?>
<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .custom-links-editor {
            display: grid;
            gap: 14px;
            margin-top: 16px;
        }

        .custom-link-row {
            align-items: end;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(150px, 1fr) minmax(220px, 1.4fr) auto auto auto;
            padding: 14px;
        }

        .custom-link-row .form-group {
            margin: 0;
        }

        .custom-link-check {
            align-items: center;
            display: flex;
            gap: 8px;
            min-height: 38px;
            white-space: nowrap;
        }

        .custom-links-note {
            background-color: rgba(76, 175, 80, 0.08);
            border: 1px solid rgba(76, 175, 80, 0.28);
            border-radius: 6px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 20px;
            padding: 15px;
        }

        @media screen and (max-width: 900px) {
            .custom-link-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'nav.php'; ?>

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

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.custom_links.menu_title')) ?></h2>
                    </div>

                    <div class="custom-links-note">
                        <?= e(dcs_t('admin.custom_links.note')) ?>
                    </div>

                    <form method="POST" id="customLinksForm">
                        <?= csrfField() ?>

                        <div class="form-group">
                            <label for="custom_links_menu_text"><?= e(dcs_t('admin.custom_links.menu_item_name')) ?></label>
                            <input type="text"
                                   id="custom_links_menu_text"
                                   name="custom_links_menu_text"
                                   class="form-control"
                                   value="<?= e($currentFeatures['custom_links_menu_text'] ?? 'Squadron Links') ?>"
                                   placeholder="Squadron Links">
                        </div>

                        <div class="custom-links-editor" id="customLinksEditor">
                            <?php foreach ($customLinks as $index => $link): ?>
                                <div class="custom-link-row">
                                    <div class="form-group">
                                        <label><?= e(dcs_t('admin.custom_links.label')) ?></label>
                                        <input type="text"
                                               name="custom_links[<?= $index ?>][label]"
                                               class="form-control"
                                               value="<?= e($link['label'] ?? '') ?>"
                                               placeholder="Tacview">
                                    </div>
                                    <div class="form-group">
                                        <label><?= e(dcs_t('admin.custom_links.url')) ?></label>
                                        <input type="text"
                                               name="custom_links[<?= $index ?>][url]"
                                               class="form-control"
                                               value="<?= e($link['url'] ?? '') ?>"
                                               placeholder="https://example.com">
                                    </div>
                                    <label class="custom-link-check">
                                        <input type="checkbox"
                                               name="custom_links[<?= $index ?>][enabled]"
                                               value="1"
                                               <?= ($link['enabled'] ?? true) ? 'checked' : '' ?>>
                                        <?= e(dcs_t('admin.status.enabled')) ?>
                                    </label>
                                    <label class="custom-link-check">
                                        <input type="checkbox"
                                               name="custom_links[<?= $index ?>][new_tab]"
                                               value="1"
                                               <?= ($link['new_tab'] ?? true) ? 'checked' : '' ?>>
                                        <?= e(dcs_t('admin.custom_links.new_tab')) ?>
                                    </label>
                                    <button type="button" class="btn btn-danger btn-small" onclick="removeCustomLink(this)"><?= e(dcs_t('admin.custom_links.remove')) ?></button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="settings-actions">
                            <button type="button" class="btn btn-secondary" onclick="addCustomLink()"><?= e(dcs_t('admin.custom_links.add_link')) ?></button>
                            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.custom_links.save_custom_links')) ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        let customLinkIndex = <?= max(1, count($customLinks)) ?>;
        const customLinksText = <?= json_encode([
            'label' => dcs_t('admin.custom_links.label'),
            'url' => dcs_t('admin.custom_links.url'),
            'enabled' => dcs_t('admin.status.enabled'),
            'newTab' => dcs_t('admin.custom_links.new_tab'),
            'remove' => dcs_t('admin.custom_links.remove')
        ]) ?>;

        function addCustomLink() {
            const editor = document.getElementById('customLinksEditor');
            const row = document.createElement('div');
            row.className = 'custom-link-row';
            row.innerHTML = `
                <div class="form-group">
                    <label>${customLinksText.label}</label>
                    <input type="text" name="custom_links[${customLinkIndex}][label]" class="form-control" placeholder="Tacview">
                </div>
                <div class="form-group">
                    <label>${customLinksText.url}</label>
                    <input type="text" name="custom_links[${customLinkIndex}][url]" class="form-control" placeholder="https://example.com">
                </div>
                <label class="custom-link-check">
                    <input type="checkbox" name="custom_links[${customLinkIndex}][enabled]" value="1" checked>
                    ${customLinksText.enabled}
                </label>
                <label class="custom-link-check">
                    <input type="checkbox" name="custom_links[${customLinkIndex}][new_tab]" value="1" checked>
                    ${customLinksText.newTab}
                </label>
                <button type="button" class="btn btn-danger btn-small" onclick="removeCustomLink(this)">${customLinksText.remove}</button>
            `;
            editor.appendChild(row);
            customLinkIndex++;
        }

        function removeCustomLink(button) {
            const row = button.closest('.custom-link-row');
            const editor = document.getElementById('customLinksEditor');

            if (row) {
                row.remove();
            }

            if (editor && editor.children.length === 0) {
                addCustomLink();
            }
        }
    </script>
</body>
</html>
