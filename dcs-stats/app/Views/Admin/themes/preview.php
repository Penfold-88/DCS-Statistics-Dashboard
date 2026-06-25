<!-- Theme Preview Section -->
<div class="theme-section">
    <h2><?= e(dcs_t('admin.themes.preview')) ?></h2>
    <p><?= e(dcs_t('admin.themes.preview_help')) ?></p>

    <iframe src="<?= $previewUrl ?>" class="preview-frame" id="preview-frame"></iframe>

    <div style="margin-top: 10px;">
        <span id="preview-status" style="color: var(--text-muted); font-size: 0.9em;"></span>
    </div>
</div>
