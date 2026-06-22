<?php include DCS_APP_PATH . '/Views/Layout/header.php'; ?>
<?php include DCS_APP_PATH . '/Views/Layout/nav.php'; ?>

<?php if (!empty($isPreview)): ?><div class="cms-preview-banner"><?= e(dcs_t('admin.cms.preview_banner')) ?></div><?php endif; ?>

<main>
    <div class="dashboard-header">
        <h1><?= e($page['title'] ?? dcs_t('cms.not_found')) ?></h1>
    </div>
    <section class="cms-public-page">
        <?php if ($page): ?>
            <?= $contentHtml ?>
        <?php else: ?>
            <p><?= e(dcs_t('cms.not_found_message')) ?></p>
        <?php endif; ?>
    </section>
</main>

<style>
.cms-public-page { background: var(--card_color, rgba(20,40,60,.88)); border: 1px solid var(--border_color, rgba(255,255,255,.15)); border-radius: 16px; line-height: 1.75; margin: 20px auto 40px; max-width: 1100px; padding: clamp(22px, 4vw, 48px); white-space: normal; }
.cms-public-page h2, .cms-public-page h3, .cms-public-page h4 { margin: 1.35em 0 .45em; }
.cms-public-page p { margin: 0 0 1em; }
.cms-public-page ul, .cms-public-page ol { margin: 0 0 1em 1.6em; }
.cms-public-page blockquote { border-left: 4px solid var(--accent_color, #4caf50); color: var(--text_muted, #aaa); margin: 1em 0; padding: .5em 1em; }
.cms-public-page a { color: var(--link_color, #64b5f6); text-decoration: underline; }
.cms-public-page .cms-text-left { text-align: left; }
.cms-public-page .cms-text-center { text-align: center; }
.cms-public-page .cms-text-right { text-align: right; }
.cms-public-page figure { box-sizing: border-box; clear: both; margin: 1.5em auto; max-width: 100%; }
.cms-public-page figure img { border-radius: 10px; display: block; height: auto; max-width: 100%; }
.cms-public-page figcaption { color: var(--text_muted, #aaa); font-size: .9em; margin-top: .55em; text-align: center; }
.cms-public-page .cms-image-center { width: fit-content; }
.cms-public-page .cms-image-left { float: left; margin: .5em 1.5em 1em 0; max-width: 48%; }
.cms-public-page .cms-image-right { float: right; margin: .5em 0 1em 1.5em; max-width: 48%; }
.cms-public-page .cms-image-wide { width: 100%; }
.cms-public-page .cms-image-wide img { width: 100%; }
@media (max-width: 700px) { .cms-public-page .cms-image-left, .cms-public-page .cms-image-right { float: none; margin: 1.5em auto; max-width: 100%; } }
.cms-preview-banner { background: #ff9800; color: #111; font-weight: 700; padding: 10px; text-align: center; }
</style>

<?php if (!empty($hasServerStatusWidget)): ?>
<link rel="stylesheet" href="<?= e(assetUrl('css/widgets/server-status.css')) ?>">
<script>window.DCS_SERVER_STATUS_WIDGET=<?= json_encode([
    'loading' => dcs_t('widget.server_status.loading'),
    'unavailable' => dcs_t('widget.server_status.unavailable'),
    'unknownServer' => dcs_t('servers.unknown_server'),
    'unknown' => dcs_t('servers.unknown'),
    'mission' => dcs_t('servers.mission'),
    'theatre' => dcs_t('servers.theatre'),
    'players' => dcs_t('widget.server_status.players'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="<?= e(assetUrl('js/widgets/server-status.js')) ?>"></script>
<?php endif; ?>

<?php if (!empty($hasImageGallery)): ?>
<link rel="stylesheet" href="<?= e(assetUrl('css/widgets/image-gallery.css')) ?>">
<script>window.DCS_GALLERY_TEXT=<?= json_encode(['close'=>dcs_t('cms.gallery.close')], JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= e(assetUrl('js/widgets/image-gallery.js')) ?>"></script>
<?php endif; ?>

<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; ?>
