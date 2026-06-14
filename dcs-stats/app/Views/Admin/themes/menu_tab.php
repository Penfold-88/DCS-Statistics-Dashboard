                <!-- Menu Configuration Tab -->
                <div id="menu-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.navigation_menu_configuration')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.navigation_menu_help')) ?></p>

                        <form method="POST" action="" id="menu-form">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="update_menu">

                            <div class="menu-items" id="menu-items">
                                <?php foreach ($menuItems as $index => $item): ?>
                                <div class="menu-item" data-index="<?= $index ?>">
                                    <div class="menu-item-handle">☰</div>
                                    <input type="hidden" name="menu_order[]" value="<?= $index ?>">
                                    <input type="hidden" name="menu_types[<?= $index ?>]" value="<?= htmlspecialchars($item['type'] ?? 'page') ?>">
                                    <div class="menu-item-fields">
                                        <input type="text" name="menu_names[<?= $index ?>]" value="<?= htmlspecialchars($item['name']) ?>" placeholder="<?= e(dcs_t('admin.themes.menu_name')) ?>" required>
                                        <?php if (in_array($item['type'] ?? 'page', ['discord', 'squadron_homepage'])): ?>
                                            <input type="text" name="menu_urls[<?= $index ?>]" value="<?= htmlspecialchars($item['url']) ?>" placeholder="URL" required title="External URL">
                                        <?php else: ?>
                                            <input type="text" name="menu_urls[<?= $index ?>]" value="<?= htmlspecialchars($item['url']) ?>" placeholder="URL" required readonly>
                                        <?php endif; ?>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="menu_enabled[<?= $index ?>]" <?= $item['enabled'] ? 'checked' : '' ?>>
                                            <span><?= e(dcs_t('admin.status.enabled')) ?></span>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <button type="submit" class="btn btn-primary" style="margin-top: 20px;"><?= e(dcs_t('admin.themes.save_menu_configuration')) ?></button>
                            <button type="button" class="btn btn-secondary" data-theme-action="reset-menu" style="margin-top: 20px;"><?= e(dcs_t('admin.themes.reset_to_default')) ?></button>
                        </form>
                    </div>
                </div>
