<?php
$field = static function (string $name) use ($prefix): string {
    return $prefix === '' ? $name : $prefix . '[' . $name . ']';
};
?>
<div class="cms-download-fields">
    <div class="form-group"><label><?= e(dcs_t('admin.cms.download_title')) ?></label><input class="form-control" name="<?= e($field('title')) ?>" maxlength="160" value="<?= e($row['title'] ?? '') ?>"></div>
    <div class="form-group cms-download-url-field"><label><?= e(dcs_t('admin.cms.download_url')) ?></label><input class="form-control" name="<?= e($field('url')) ?>" type="url" maxlength="700" placeholder="https://example.com/file.zip" value="<?= e($row['url'] ?? '') ?>"></div>
    <div class="form-group"><label><?= e(dcs_t('admin.cms.download_category')) ?></label><input class="form-control" name="<?= e($field('category')) ?>" maxlength="80" value="<?= e($row['category'] ?? '') ?>"></div>
    <div class="form-group"><label><?= e(dcs_t('admin.cms.download_version')) ?></label><input class="form-control" name="<?= e($field('version')) ?>" maxlength="40" value="<?= e($row['version'] ?? '') ?>"></div>
    <div class="form-group"><label><?= e(dcs_t('admin.cms.download_file_size')) ?></label><input class="form-control" name="<?= e($field('file_size')) ?>" maxlength="40" placeholder="25 MB" value="<?= e($row['file_size'] ?? '') ?>"></div>
    <div class="form-group"><label><?= e(dcs_t('admin.cms.download_button_label')) ?></label><input class="form-control" name="<?= e($field('button_label')) ?>" maxlength="40" placeholder="<?= e(dcs_t('cms.downloads.download')) ?>" value="<?= e($row['button_label'] ?? '') ?>"></div>
    <div class="form-group"><label><?= e(dcs_t('admin.cms.download_sort_order')) ?></label><input class="form-control" name="<?= e($field('sort_order')) ?>" type="number" min="0" max="9999" value="<?= e((string)($row['sort_order'] ?? 100)) ?>"></div>
    <label class="cms-setting-toggle cms-download-enabled"><input type="checkbox" name="<?= e($field('enabled')) ?>" value="1" <?= !isset($row['enabled']) || !empty($row['enabled']) ? 'checked' : '' ?>><span><strong><?= e(dcs_t('admin.cms.download_published')) ?></strong></span></label>
    <label class="cms-setting-toggle cms-download-featured"><input type="checkbox" name="<?= e($field('featured')) ?>" value="1" <?= !empty($row['featured']) ? 'checked' : '' ?>><span><strong><?= e(dcs_t('admin.cms.download_featured')) ?></strong><small><?= e(dcs_t('admin.cms.download_featured_help')) ?></small></span></label>
    <div class="form-group cms-download-description"><label><?= e(dcs_t('admin.cms.download_description')) ?></label><textarea class="form-control" name="<?= e($field('description')) ?>" maxlength="700" rows="3"><?= e($row['description'] ?? '') ?></textarea></div>
</div>
