<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.discord.configuration')) ?></h2>
    </div>

    <form method="POST" action="">
        <?= csrfField() ?>

        <div class="form-group">
            <div class="setting-item">
                <input type="checkbox"
                       id="show_discord_link"
                       name="show_discord_link"
                       value="1"
                       <?= ($currentFeatures['show_discord_link'] ?? false) ? 'checked' : '' ?>>
                <label for="show_discord_link"><?= e(dcs_t('admin.discord.show_in_navigation')) ?></label>
            </div>
            <small class="text-muted"><?= e(dcs_t('admin.discord.show_help')) ?></small>
        </div>

        <div class="form-group">
            <label for="discord_link_url"><?= e(dcs_t('admin.discord.invite_url')) ?></label>
            <input type="url"
                   id="discord_link_url"
                   name="discord_link_url"
                   class="form-control"
                   value="<?= e($currentFeatures['discord_link_url'] ?? 'https://discord.gg/DNENf6pUNX') ?>"
                   placeholder="https://discord.gg/YourInvite"
                   required>
            <small class="text-muted">
                <?= e(dcs_t('admin.discord.invite_help')) ?>
                <strong><?= e(dcs_t('admin.discord.tip_label')) ?></strong> <?= e(dcs_t('admin.discord.tip_text')) ?>
            </small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.discord.save_button')) ?></button>
            <a href="settings.php" class="btn btn-secondary"><?= e(dcs_t('admin.discord.back_to_features')) ?></a>
        </div>
    </form>
</div>
