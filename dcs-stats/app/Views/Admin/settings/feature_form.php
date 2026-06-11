<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.settings.site_features')) ?></h2>
    </div>

    <form method="POST" action="" id="settingsForm">
        <?= csrfField() ?>

        <div class="bulk-actions">
            <button type="button" class="btn btn-secondary" onclick="toggleAll(true)"><?= e(dcs_t('admin.settings.enable_all')) ?></button>
            <button type="button" class="btn btn-secondary" onclick="toggleAll(false)"><?= e(dcs_t('admin.settings.disable_all')) ?></button>
            <button type="button" class="btn btn-secondary" onclick="toggleGroup('Navigation', false)"><?= e(dcs_t('admin.settings.minimal_mode')) ?></button>
        </div>

        <div class="settings-grid">
            <?php foreach ($featureGroups as $groupName => $features): ?>
                <div class="settings-group collapsible-group">
                    <h3 class="group-header" data-group="<?= e(strtolower(str_replace(' ', '_', $groupName))) ?>">
                        <span class="group-title"><?= e($featureGroupLabels[$groupName] ?? $groupName) ?></span>
                        <span class="collapse-arrow">▼</span>
                    </h3>
                    <div class="group-content" id="group_<?= e(strtolower(str_replace(' ', '_', $groupName))) ?>">
                        <?php $serverSubheadingShown = false; ?>
                        <?php foreach ($features as $key => $label): ?>
                            <?php
                            $isDependent = false;
                            $parentKey = null;
                            $isLocked = isset($lockedFeatures[$key]);
                            $isDynamicServer = isset($dynamicServerFeatures[$key]);
                            foreach ($dependencies as $parent => $children) {
                                if (in_array($key, $children)) {
                                    $isDependent = true;
                                    $parentKey = $parent;
                                    break;
                                }
                            }
                            ?>
                            <?php if ($isDynamicServer && !$serverSubheadingShown): ?>
                                <div class="settings-subheading"><?= e(dcs_t('admin.settings.detected_servers')) ?></div>
                                <?php $serverSubheadingShown = true; ?>
                            <?php endif; ?>
                            <div class="setting-item <?= $isDependent ? 'dependent' : '' ?> <?= $isLocked ? 'disabled locked-feature' : '' ?> <?= $isDynamicServer ? 'dynamic-server' : '' ?>"
                                 data-feature="<?= e($key) ?>"
                                 <?= $parentKey ? 'data-parent="' . e($parentKey) . '"' : '' ?>
                                 <?= $isLocked ? 'data-locked="true" title="' . e($lockedFeatures[$key]) . '"' : '' ?>>
                                <input type="checkbox"
                                       id="feature_<?= e($key) ?>"
                                       name="features[<?= e($key) ?>]"
                                       value="1"
                                       <?= (!$isLocked && ($currentFeatures[$key] ?? true)) ? 'checked' : '' ?>
                                       <?= ($isLocked || ($isDependent && !($currentFeatures[$parentKey] ?? true))) ? 'disabled' : '' ?>>
                                <label for="feature_<?= e($key) ?>">
                                    <?= e($featureLabels[$key] ?? $label) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="settings-actions">
            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.settings.save_settings')) ?></button>
            <span class="text-muted"><?= e(dcs_t('admin.settings.changes_immediate')) ?></span>
        </div>

        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <h3 class="card-title"><?= e(dcs_t('admin.settings.additional_settings')) ?></h3>
            </div>

            <p class="text-muted"><?= e(dcs_t('admin.settings.additional_text')) ?></p>

            <div class="btn-group">
                <a href="discord_settings.php" class="btn btn-secondary">
                    <span class="nav-icon">💬</span>
                    <?= e(dcs_t('admin.settings.discord_link_settings')) ?>
                </a>
                <a href="squadron_settings.php" class="btn btn-secondary">
                    <span class="nav-icon">🏆</span>
                    <?= e(dcs_t('admin.settings.squadron_homepage_settings')) ?>
                </a>
                <a href="custom_links.php" class="btn btn-secondary">
                    <span class="nav-icon">🔗</span>
                    <?= e(dcs_t('admin.nav.custom_links')) ?>
                </a>
                <a href="themes.php" class="btn btn-secondary">
                    <span class="nav-icon">🎨</span>
                    <?= e(dcs_t('admin.settings.theme_settings')) ?>
                </a>
                <a href="api_settings.php" class="btn btn-secondary">
                    <span class="nav-icon">🔌</span>
                    <?= e(dcs_t('admin.nav.api_settings')) ?>
                </a>
            </div>
        </div>
    </form>
</div>
