<article class="cms-download-card<?= !empty($download['featured']) ? ' is-featured' : '' ?>" data-download-card-category="<?= e($category ?? '') ?>">
    <div>
        <?php if (!empty($download['featured'])): ?><span class="cms-download-featured-badge"><?= e(dcs_t('cms.downloads.featured_badge')) ?></span><?php endif; ?>
        <h3><?= e($download['title'] ?? '') ?></h3>
        <?php if (!empty($download['description'])): ?><p><?= nl2br(e($download['description'])) ?></p><?php endif; ?>
        <div class="cms-download-meta">
            <?php if (!empty($download['version'])): ?><span><?= e(dcs_t('cms.downloads.version')) ?> <?= e($download['version']) ?></span><?php endif; ?>
            <?php if (!empty($download['file_size'])): ?><span><?= e($download['file_size']) ?></span><?php endif; ?>
        </div>
    </div>
    <a class="cms-download-button" href="<?= e($download['url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer"><?= e(trim((string)($download['button_label'] ?? '')) !== '' ? (string)$download['button_label'] : dcs_t('cms.downloads.download')) ?></a>
</article>
