<div class="language-actions" style="margin-top: 24px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= e(dcs_t('admin.language.default_language')) ?></h2>
        </div>

        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save_language">
            <div class="form-group">
                <label for="default_language"><?= e(dcs_t('admin.language.public_site_language')) ?></label>
                <select id="default_language" name="default_language" class="form-control">
                    <?php foreach ($supportedLanguages as $code => $label): ?>
                    <option value="<?= e($code) ?>" <?= $currentLanguage === $code ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date_format"><?= e(dcs_t('admin.language.date_format')) ?></label>
                <select id="date_format" name="date_format" class="form-control">
                    <?php foreach ($dateFormatOptions as $format => $example): ?>
                    <option value="<?= e($format) ?>" <?= $currentDateFormat === $format ? 'selected' : '' ?>>
                        <?= e($example) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="help-text"><?= e(dcs_t('admin.language.date_format_help')) ?></div>
            </div>

            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.language.save_language')) ?></button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= e(dcs_t('admin.language.upload_translation')) ?></h2>
        </div>

        <a class="template-link" href="../lang/translation-template.json" download><?= e(dcs_t('admin.language.download_template')) ?></a>

        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="upload_translation">
            <div class="form-group">
                <label for="translation_file"><?= e(dcs_t('admin.language.translation_file')) ?></label>
                <input id="translation_file" name="translation_file" type="file" class="form-control" accept=".json,application/json" required>
                <div class="help-text"><?= e(dcs_t('admin.language.upload_help')) ?></div>
            </div>

            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.language.upload_translation')) ?></button>
        </form>
    </div>
</div>
