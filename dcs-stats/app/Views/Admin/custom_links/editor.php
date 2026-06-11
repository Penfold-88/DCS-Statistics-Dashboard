<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.custom_links.menu_title')) ?></h2>
    </div>

    <div class="custom-links-note">
        <?= e(dcs_t('admin.custom_links.note')) ?>
    </div>

    <form method="POST" id="customLinksForm">
        <?= csrfField() ?>

        <div class="form-group">
            <label for="custom_links_menu_text"><?= e(dcs_t('admin.custom_links.menu_item_name')) ?></label>
            <input type="text"
                   id="custom_links_menu_text"
                   name="custom_links_menu_text"
                   class="form-control"
                   value="<?= e($currentFeatures['custom_links_menu_text'] ?? 'Squadron Links') ?>"
                   placeholder="Squadron Links">
        </div>

        <div class="custom-links-editor" id="customLinksEditor">
            <?php foreach ($customLinks as $index => $link): ?>
                <div class="custom-link-row">
                    <div class="form-group">
                        <label><?= e(dcs_t('admin.custom_links.label')) ?></label>
                        <input type="text"
                               name="custom_links[<?= $index ?>][label]"
                               class="form-control"
                               value="<?= e($link['label'] ?? '') ?>"
                               placeholder="Tacview">
                    </div>
                    <div class="form-group">
                        <label><?= e(dcs_t('admin.custom_links.url')) ?></label>
                        <input type="text"
                               name="custom_links[<?= $index ?>][url]"
                               class="form-control"
                               value="<?= e($link['url'] ?? '') ?>"
                               placeholder="https://example.com">
                    </div>
                    <label class="custom-link-check">
                        <input type="checkbox"
                               name="custom_links[<?= $index ?>][enabled]"
                               value="1"
                               <?= ($link['enabled'] ?? true) ? 'checked' : '' ?>>
                        <?= e(dcs_t('admin.status.enabled')) ?>
                    </label>
                    <label class="custom-link-check">
                        <input type="checkbox"
                               name="custom_links[<?= $index ?>][new_tab]"
                               value="1"
                               <?= ($link['new_tab'] ?? true) ? 'checked' : '' ?>>
                        <?= e(dcs_t('admin.custom_links.new_tab')) ?>
                    </label>
                    <button type="button" class="btn btn-danger btn-small" onclick="removeCustomLink(this)"><?= e(dcs_t('admin.custom_links.remove')) ?></button>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="settings-actions">
            <button type="button" class="btn btn-secondary" onclick="addCustomLink()"><?= e(dcs_t('admin.custom_links.add_link')) ?></button>
            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.custom_links.save_custom_links')) ?></button>
        </div>
    </form>
</div>
