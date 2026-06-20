<?php include DCS_APP_PATH . '/Views/Layout/header.php'; ?>
<?php include DCS_APP_PATH . '/Views/Layout/nav.php'; ?>

<main>
    <div class="dashboard-header">
        <h1><?= e($page['title'] ?? dcs_t('cms.not_found')) ?></h1>
    </div>
    <section class="cms-public-page">
        <?php if ($page): ?>
            <?= nl2br(e($page['content'] ?? '')) ?>
        <?php else: ?>
            <p><?= e(dcs_t('cms.not_found_message')) ?></p>
        <?php endif; ?>
    </section>
</main>

<style>
.cms-public-page { background: var(--card_color, rgba(20,40,60,.88)); border: 1px solid var(--border_color, rgba(255,255,255,.15)); border-radius: 16px; line-height: 1.75; margin: 20px auto 40px; max-width: 1100px; padding: clamp(22px, 4vw, 48px); white-space: normal; }
</style>

<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; ?>
