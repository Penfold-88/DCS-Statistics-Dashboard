                <!-- Simple Customization Tab -->
                <div id="simple-tab" class="tab-content active">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.colour_customization')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.colour_help')) ?></p>
                        <p style="font-size: 0.9em; color: var(--text-muted); margin-top: 10px;">
                            <?= e(dcs_t('admin.themes.colour_tip')) ?>
                        </p>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="update_colors">

                        <?php foreach ($themeColorGroups as $groupName => $fields): ?>
                            <fieldset class="color-fieldset">
                                <legend><?= htmlspecialchars($groupName) ?></legend>
                                <?php if ($groupName === dcs_t('admin.themes.group_page_text')): ?>
                                    <div class="color-input-group" style="margin-bottom: 14px;">
                                        <label for="page_background_gradient_enabled"><?= e(dcs_t('admin.themes.page_background_gradient')) ?>:</label>
                                        <input type="checkbox" id="page_background_gradient_enabled" name="page_background_gradient_enabled"
                                               <?= !empty($themeOptions['page_background_gradient_enabled']) ? 'checked' : '' ?>>
                                    </div>
                                <?php endif; ?>
                                <?php if ($groupName === dcs_t('admin.themes.group_header_nav')): ?>
                                    <div class="color-input-group" style="margin-bottom: 14px;">
                                        <label for="header_title_gradient_enabled"><?= e(dcs_t('admin.themes.header_soft_gradient')) ?>:</label>
                                        <input type="checkbox" id="header_title_gradient_enabled" name="header_title_gradient_enabled"
                                               <?= !empty($themeOptions['header_title_gradient_enabled']) ? 'checked' : '' ?>>
                                    </div>
                                <?php endif; ?>
                                <div class="color-inputs">
                                        <?php foreach ($fields as $fieldKey => $label): ?>
                                            <div class="color-input-group">
                                                <label for="<?= htmlspecialchars($fieldKey) ?>"><?= htmlspecialchars($label) ?>:</label>
                                                <input type="color" id="<?= htmlspecialchars($fieldKey) ?>" name="<?= htmlspecialchars($fieldKey) ?>"
                                                       value="<?= htmlspecialchars($customColors[$fieldKey]) ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </fieldset>
                            <?php endforeach; ?>

                            <div style="margin-top: 20px; display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.themes.update_colours')) ?></button>
                                <button type="button" class="btn btn-secondary" data-theme-action="restore-default-colors"><?= e(dcs_t('admin.themes.restore_defaults')) ?></button>
                            </div>
                        </form>
                    </div>
                </div>
