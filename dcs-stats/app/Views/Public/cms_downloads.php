<?php include DCS_APP_PATH . '/Views/Layout/header.php'; ?>
<?php include DCS_APP_PATH . '/Views/Layout/nav.php'; ?>

<main>
    <div class="dashboard-header">
        <h1><?= e($pageTitle) ?></h1>
    </div>
    <section class="cms-downloads-page">
        <?php if (!$enabled): ?>
            <p><?= e(dcs_t('cms.not_found_message')) ?></p>
        <?php elseif (!$downloads): ?>
            <p><?= e(dcs_t('cms.downloads.empty')) ?></p>
        <?php else: ?>
            <?php
            $grouped = [];
            $featured = [];
            $categories = [];
            foreach ($downloads as $download) {
                $category = trim((string)($download['category'] ?? '')) ?: dcs_t('cms.downloads.general');
                $grouped[$category][] = $download;
                $categories[$category] = true;
                if (!empty($download['featured'])) $featured[] = $download;
            }
            ?>
            <?php if ($featured): ?>
                <div class="cms-download-category cms-download-featured-section" data-download-category-section="__featured">
                    <h2><?= e(dcs_t('cms.downloads.featured')) ?></h2>
                    <div class="cms-download-grid">
                        <?php foreach ($featured as $download): ?>
                            <?php $category = trim((string)($download['category'] ?? '')) ?: dcs_t('cms.downloads.general'); ?>
                            <?php include DCS_APP_PATH . '/Views/Public/partials/download_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (count($categories) > 1): ?>
                <nav class="cms-download-filters" aria-label="<?= e(dcs_t('cms.downloads.filter_label')) ?>">
                    <button type="button" class="is-active" data-download-filter=""><?= e(dcs_t('cms.downloads.all_categories')) ?></button>
                    <?php foreach (array_keys($categories) as $category): ?>
                        <button type="button" data-download-filter="<?= e($category) ?>"><?= e($category) ?></button>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
            <?php foreach ($grouped as $category => $categoryDownloads): ?>
                <div class="cms-download-category" data-download-category-section="<?= e($category) ?>">
                    <h2><?= e($category) ?></h2>
                    <div class="cms-download-grid">
                        <?php foreach ($categoryDownloads as $download): ?>
                            <?php include DCS_APP_PATH . '/Views/Public/partials/download_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<style>
.cms-downloads-page { box-sizing: border-box; margin: 20px auto 40px; max-width: 1100px; padding: 0 12px; width: 100%; }
.cms-download-category { margin-bottom: 28px; }
.cms-download-category h2 { color: var(--accent_color, #ffd54f); margin: 0 0 14px; text-shadow: 0 0 12px rgba(255,193,7,.25); }
.cms-download-featured-section { background: linear-gradient(135deg, rgba(255,213,79,.08), rgba(76,175,80,.05)); border: 1px solid rgba(255,213,79,.22); border-radius: 16px; padding: 18px; }
.cms-download-filters { display: flex; flex-wrap: wrap; gap: 9px; margin: 0 0 24px; }
.cms-download-filters button { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.16); border-radius: 999px; color: var(--text_color, #fff); cursor: pointer; font: inherit; font-weight: 700; padding: 8px 13px; }
.cms-download-filters button:hover, .cms-download-filters button:focus, .cms-download-filters button.is-active { background: var(--accent_color, #4caf50); color: #07131f; outline: none; }
.cms-download-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
.cms-download-card { background: linear-gradient(135deg, rgba(13,54,94,.95), rgba(16,60,96,.88)); border: 1px solid var(--border_color, rgba(255,213,79,.35)); border-radius: 14px; box-shadow: 0 14px 28px rgba(0,0,0,.22), 0 0 22px rgba(33,150,243,.12); box-sizing: border-box; display: flex; flex-direction: column; gap: 16px; justify-content: space-between; min-width: 0; padding: 20px; }
.cms-download-card.is-featured { border-color: rgba(255,213,79,.62); box-shadow: 0 14px 28px rgba(0,0,0,.22), 0 0 26px rgba(255,213,79,.18); }
.cms-download-card h3 { margin: 0 0 8px; overflow-wrap: anywhere; }
.cms-download-featured-badge { background: rgba(255,213,79,.18); border: 1px solid rgba(255,213,79,.38); border-radius: 999px; color: #ffe082; display: inline-flex; font-size: .78em; font-weight: 800; margin-bottom: 10px; padding: 3px 8px; text-transform: uppercase; }
.cms-download-card p { color: var(--text_color, #fff); line-height: 1.6; margin: 0 0 12px; overflow-wrap: anywhere; }
.cms-download-meta { color: var(--text_muted, #c8d6e5); display: flex; flex-wrap: wrap; font-size: .92em; gap: 8px; }
.cms-download-meta span { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.1); border-radius: 999px; padding: 4px 9px; }
.cms-download-button { align-self: flex-start; background: var(--accent_color, #4caf50); border-radius: 8px; color: #07131f; font-weight: 700; padding: 10px 14px; text-decoration: none; }
.cms-download-button:hover { filter: brightness(1.08); text-decoration: none; }
@media (max-width: 700px) { .cms-downloads-page { padding: 0 14px; } .cms-download-grid { grid-template-columns: 1fr; } .cms-download-card { padding: 18px; } }
</style>

<script>
document.querySelectorAll('[data-download-filter]').forEach(button => {
    button.addEventListener('click', () => {
        const selected = button.dataset.downloadFilter || '';
        document.querySelectorAll('[data-download-filter]').forEach(item => item.classList.toggle('is-active', item === button));
        document.querySelectorAll('[data-download-category-section]').forEach(section => {
            const category = section.dataset.downloadCategorySection || '';
            if (category === '__featured') {
                let visibleCards = 0;
                section.querySelectorAll('[data-download-card-category]').forEach(card => {
                    const show = selected === '' || card.dataset.downloadCardCategory === selected;
                    card.hidden = !show;
                    if (show) visibleCards++;
                });
                section.hidden = visibleCards === 0;
            } else {
                section.hidden = selected !== '' && category !== selected;
            }
        });
    });
});
</script>

<?php include DCS_APP_PATH . '/Views/Layout/footer.php'; ?>
