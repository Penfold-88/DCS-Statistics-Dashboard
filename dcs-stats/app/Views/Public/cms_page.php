<?php include DCS_APP_PATH . '/Views/Layout/header.php'; ?>
<?php include DCS_APP_PATH . '/Views/Layout/nav.php'; ?>

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
</style>

<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; ?>
