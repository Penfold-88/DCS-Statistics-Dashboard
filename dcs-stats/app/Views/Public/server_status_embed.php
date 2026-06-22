<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title><?= e(dcs_t('widget.server_status.title')) ?></title>
    <link rel="stylesheet" href="<?= e(assetUrl('styles.php')) ?>">
    <link rel="stylesheet" href="<?= e(assetUrl('css/widgets/server-status.css')) ?>">
    <style>html,body{background:transparent!important;margin:0;min-height:0}body{padding:8px}.dcs-server-status-widget{margin:0}</style>
    <script>window.DCS_CONFIG=<?= getJsConfig() ?>;window.DCS_SERVER_STATUS_WIDGET=<?= json_encode([
        'loading' => dcs_t('widget.server_status.loading'),
        'unavailable' => dcs_t('widget.server_status.unavailable'),
        'unknownServer' => dcs_t('servers.unknown_server'),
        'unknown' => dcs_t('servers.unknown'),
        'mission' => dcs_t('servers.mission'),
        'theatre' => dcs_t('servers.theatre'),
        'players' => dcs_t('widget.server_status.players'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
    <script src="<?= e(assetUrl('js/api-client.js')) ?>" defer></script>
    <script src="<?= e(assetUrl('js/widgets/server-status.js')) ?>" defer></script>
</head>
<body>
    <section class="dcs-server-status-widget" data-server-status-widget data-server-filter="<?= e($serverFilter ?? '') ?>" aria-live="polite">
        <p class="dcs-widget-loading"><?= e(dcs_t('widget.server_status.loading')) ?></p>
    </section>
</body>
</html>
