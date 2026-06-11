<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.language.translated_languages')) ?></h2>
    </div>

    <div class="language-grid">
        <?php foreach ($supportedLanguages as $code => $label): ?>
            <?php $isBuiltIn = isset($builtInLanguages[$code]); ?>
            <div class="language-card">
                <strong><?= e($label) ?></strong>
                <div class="language-meta">
                    <?= e(strtoupper($code)) ?> · <?= e($isBuiltIn ? dcs_t('admin.language.built_in') : dcs_t('admin.language.uploaded')) ?> · <?= e(dcs_t('admin.language.translated_keys', ['count' => count(dcs_load_translations($code))])) ?>
                </div>
                <?php if ($currentLanguage === $code): ?>
                    <span class="language-badge"><?= e(dcs_t('admin.language.current')) ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
