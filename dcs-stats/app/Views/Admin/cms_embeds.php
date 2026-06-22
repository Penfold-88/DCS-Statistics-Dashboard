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
            <div class="card cms-embeds-intro">
                <div class="card-header"><div><h2 class="card-title"><?= e(dcs_t('admin.cms.embed_tools')) ?></h2><p class="text-muted"><?= e(dcs_t('admin.cms.embeds_help')) ?></p></div></div>
            </div>
            <section class="card cms-embed-card">
                <div class="card-header"><div><h2 class="card-title"><?= e(dcs_t('widget.server_status.title')) ?></h2><p class="text-muted"><?= e(dcs_t('admin.cms.server_embed_help')) ?></p></div></div>
                <div class="cms-embed-layout">
                    <div class="cms-embed-code">
                        <label for="server-status-embed-server"><?= e(dcs_t('admin.cms.select_server')) ?></label>
                        <select class="form-control cms-embed-server-select" id="server-status-embed-server" data-embed-server><option value=""><?= e(dcs_t('admin.cms.all_servers')) ?></option></select>
                        <label for="server-status-embed-code"><?= e(dcs_t('admin.cms.embed_code')) ?></label>
                        <textarea class="form-control" id="server-status-embed-code" rows="7" readonly data-embed-code><?= e($embedCode) ?></textarea>
                        <div class="settings-actions"><button class="btn btn-primary" type="button" data-copy-embed><?= e(dcs_t('admin.cms.copy_embed')) ?></button><a class="btn btn-secondary" href="<?= e($embedUrl) ?>" target="_blank" rel="noopener" data-open-embed><?= e(dcs_t('admin.cms.open_embed')) ?></a><span class="text-success" data-copy-status aria-live="polite"></span></div>
                    </div>
                    <div class="cms-embed-preview">
                        <strong><?= e(dcs_t('admin.cms.embed_preview')) ?></strong>
                        <iframe src="<?= e($embedUrl) ?>" title="<?= e(dcs_t('widget.server_status.title')) ?>" loading="lazy" data-embed-preview></iframe>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>
<script>window.DCS_EMBED_CONFIG=<?= json_encode(['copied'=>dcs_t('admin.cms.embed_copied'),'serversUnavailable'=>dcs_t('admin.cms.servers_unavailable'),'title'=>dcs_t('widget.server_status.title'),'embedUrl'=>$embedUrl,'serversEndpoint'=>'../get_servers.php'], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="../js/admin/cms-embeds.js"></script>
</body>
</html>
