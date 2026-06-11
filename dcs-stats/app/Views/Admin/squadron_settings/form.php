<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.squadron_settings.configuration')) ?></h2>
        <p class="text-muted"><?= e(dcs_t('admin.squadron_settings.configuration_help')) ?></p>
    </div>

    <form method="POST" action="">
        <?= csrfField() ?>

        <div class="form-group">
            <div class="setting-item">
                <input type="checkbox"
                       id="show_squadron_homepage"
                       name="show_squadron_homepage"
                       value="1"
                       <?= ($currentFeatures['show_squadron_homepage'] ?? false) ? 'checked' : '' ?>>
                <label for="show_squadron_homepage"><?= e(dcs_t('admin.squadron_settings.enable_link')) ?></label>
            </div>
            <small class="text-muted"><?= e(dcs_t('admin.squadron_settings.enable_help')) ?></small>
        </div>

        <div class="form-group">
            <label for="squadron_homepage_url"><?= e(dcs_t('admin.squadron_settings.homepage_url')) ?></label>
            <input type="url"
                   id="squadron_homepage_url"
                   name="squadron_homepage_url"
                   class="form-control"
                   value="<?= e($currentFeatures['squadron_homepage_url'] ?? '') ?>"
                   placeholder="https://your-squadron-website.com">
            <small class="text-muted"><?= e(dcs_t('admin.squadron_settings.url_help')) ?></small>
        </div>

        <div class="form-group">
            <label for="squadron_homepage_text"><?= e(dcs_t('admin.squadron_settings.link_text')) ?></label>
            <input type="text"
                   id="squadron_homepage_text"
                   name="squadron_homepage_text"
                   class="form-control"
                   value="<?= e($currentFeatures['squadron_homepage_text'] ?? 'Squadron') ?>"
                   placeholder="Squadron"
                   maxlength="50">
            <small class="text-muted"><?= e(dcs_t('admin.squadron_settings.link_text_help')) ?></small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.squadron_settings.save_button')) ?></button>
            <a href="settings.php" class="btn btn-secondary"><?= e(dcs_t('admin.discord.back_to_features')) ?></a>
        </div>
    </form>
</div>
