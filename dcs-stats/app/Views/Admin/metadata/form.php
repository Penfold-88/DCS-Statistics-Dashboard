<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.metadata.search_metadata')) ?></h2>
    </div>

    <div class="metadata-help">
        <?= e(dcs_t('admin.metadata.help')) ?>
    </div>

    <form method="POST">
        <?= csrfField() ?>

        <div class="form-group">
            <label for="description"><?= e(dcs_t('admin.metadata.description')) ?></label>
            <textarea id="description" name="description" class="form-control" maxlength="320" <?= $demoRestricted ? 'disabled' : '' ?>><?= e($metadata['description'] ?? '') ?></textarea>
            <span class="character-count"><?= e(dcs_t('admin.metadata.description_help')) ?></span>
        </div>

        <div class="form-group">
            <label for="keywords"><?= e(dcs_t('admin.metadata.keywords')) ?></label>
            <textarea id="keywords" name="keywords" class="form-control" maxlength="500" <?= $demoRestricted ? 'disabled' : '' ?>><?= e($metadata['keywords'] ?? '') ?></textarea>
            <span class="character-count"><?= e(dcs_t('admin.metadata.keywords_help')) ?></span>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="block_search_engines" value="1" <?= !empty($metadata['block_search_engines']) ? 'checked' : '' ?> <?= $demoRestricted ? 'disabled' : '' ?>>
                <?= e(dcs_t('admin.metadata.block_search_engines')) ?>
            </label>
        </div>

        <div class="card" style="margin-top: 24px;">
            <div class="card-header">
                <h3 class="card-title"><?= e(dcs_t('admin.metadata.privacy_notice')) ?></h3>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="show_privacy_link" value="1" <?= !empty($metadata['show_privacy_link']) ? 'checked' : '' ?> <?= $demoRestricted ? 'disabled' : '' ?>>
                    <?= e(dcs_t('admin.metadata.show_privacy_link')) ?>
                </label>
            </div>

            <div class="form-group">
                <label for="privacy_notice"><?= e(dcs_t('admin.metadata.privacy_notice_text')) ?></label>
                <textarea id="privacy_notice" name="privacy_notice" class="form-control" maxlength="5000" <?= $demoRestricted ? 'disabled' : '' ?>><?= e($metadata['privacy_notice'] ?? '') ?></textarea>
                <span class="character-count"><?= e(dcs_t('admin.metadata.privacy_notice_help')) ?></span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" <?= $demoRestricted ? 'disabled' : '' ?>><?= e(dcs_t('admin.metadata.save_button')) ?></button>
    </form>

    <p class="usage-data-disclaimer"><?= e(dcs_t('admin.metadata.usage_data_disclaimer')) ?></p>
</div>
