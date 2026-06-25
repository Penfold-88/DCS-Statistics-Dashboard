                <div id="presets-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.theme_presets')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.presets_help')) ?></p>

                        <div class="preset-grid">
                            <?php foreach ($builtInThemePresets as $presetId => $preset): ?>
                                <?php $presetColors = array_merge($defaultThemeColors, $preset['colors'] ?? []); ?>
                                <div class="preset-card">
                                    <div>
                                        <h3><?= htmlspecialchars($preset['name']) ?></h3>
                                        <p><?= htmlspecialchars($preset['description']) ?></p>
                                        <div class="preset-swatches" aria-hidden="true">
                                            <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['background_color']) ?>"></span>
                                            <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['card_color']) ?>"></span>
                                            <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['accent_color']) ?>"></span>
                                            <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['heading_color']) ?>"></span>
                                            <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['link_color']) ?>"></span>
                                        </div>
                                    </div>
                                    <form method="POST" action="" class="preset-actions">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="action" value="apply_theme_preset">
                                        <input type="hidden" name="preset_type" value="built_in">
                                        <input type="hidden" name="preset_id" value="<?= htmlspecialchars($presetId) ?>">
                                        <button type="submit" class="btn btn-primary btn-small"><?= e(dcs_t('admin.themes.apply_preset')) ?></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.custom_presets')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.custom_presets_help')) ?></p>

                        <form method="POST" action="" class="save-preset-row">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="save_theme_preset">
                            <div>
                                <label for="preset_name"><?= e(dcs_t('admin.themes.preset_name')) ?></label>
                                <input type="text" id="preset_name" name="preset_name" maxlength="60" placeholder="<?= e(dcs_t('admin.themes.preset_placeholder')) ?>" required>
                            </div>
                            <button type="submit" class="btn btn-secondary"><?= e(dcs_t('admin.themes.save_current_theme')) ?></button>
                        </form>

                        <?php if (!empty($customThemePresets)): ?>
                            <div class="preset-grid">
                                <?php foreach ($customThemePresets as $presetIndex => $preset): ?>
                                    <?php $presetColors = array_merge($defaultThemeColors, $preset['colors'] ?? []); ?>
                                    <div class="preset-card">
                                        <div>
                                            <h3><?= htmlspecialchars($preset['name'] ?? 'Custom Preset') ?></h3>
                                            <p><?= htmlspecialchars($preset['description'] ?? 'Saved custom squadron theme') ?></p>
                                            <?php if (!empty($preset['created_at'])): ?>
                                                <p>Saved <?= htmlspecialchars(date('Y-m-d H:i', strtotime($preset['created_at']))) ?></p>
                                            <?php endif; ?>
                                            <div class="preset-swatches" aria-hidden="true">
                                                <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['background_color']) ?>"></span>
                                                <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['card_color']) ?>"></span>
                                                <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['accent_color']) ?>"></span>
                                                <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['heading_color']) ?>"></span>
                                                <span class="preset-swatch" style="background: <?= htmlspecialchars($presetColors['link_color']) ?>"></span>
                                            </div>
                                        </div>
                                        <div class="preset-actions">
                                            <form method="POST" action="">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="action" value="apply_theme_preset">
                                                <input type="hidden" name="preset_type" value="custom">
                                                <input type="hidden" name="preset_id" value="<?= (int)$presetIndex ?>">
                                                <button type="submit" class="btn btn-primary btn-small"><?= e(dcs_t('admin.themes.apply')) ?></button>
                                            </form>
                                            <form method="POST" action="" onsubmit='return confirm(<?= json_encode(dcs_t('admin.themes.confirm_delete_preset')) ?>);'>
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="action" value="delete_theme_preset">
                                                <input type="hidden" name="preset_id" value="<?= (int)$presetIndex ?>">
                                                <button type="submit" class="btn btn-danger btn-small"><?= e(dcs_t('admin.themes.delete')) ?></button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted" style="margin-top: 16px;">No custom presets saved yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
