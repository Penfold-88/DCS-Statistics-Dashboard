                <!-- Backup & Restore Tab -->
                <div id="backups-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.theme_settings_backup')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.theme_settings_backup_help')) ?></p>

                        <div class="backup-list">
                            <div class="backup-item">
                                <div class="backup-info">
                                    <strong><?= e(dcs_t('admin.themes.current_theme_settings')) ?></strong><br>
                                    <small><?= e(dcs_t('admin.themes.current_theme_settings_help')) ?></small>
                                </div>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                    <input type="hidden" name="action" value="export_theme_settings">
                                    <button type="submit" class="btn btn-primary btn-sm"><?= e(dcs_t('admin.themes.download_backup')) ?></button>
                                </form>
                            </div>
                        </div>

                        <form method="POST" action="" enctype="multipart/form-data" class="upload-section">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="import_theme_settings">

                            <div class="file-input-wrapper">
                                <input type="file" name="theme_settings_file" id="theme_settings_file" accept=".json,application/json">
                                <label for="theme_settings_file" class="file-input-button"><?= e(dcs_t('admin.themes.choose_settings_backup')) ?></label>
                            </div>
                            <span id="theme-settings-file-name" style="margin-left: 10px;"><?= e(dcs_t('admin.themes.no_file_selected')) ?></span>

                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-primary"
                                        onclick='return confirm(<?= json_encode(dcs_t('admin.themes.confirm_restore_settings')) ?>)'>
                                    <?= e(dcs_t('admin.themes.upload_and_restore')) ?>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.advanced_css_backups')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.advanced_css_backups_help')) ?></p>

                        <?php if (empty($backups)): ?>
                            <p><?= e(dcs_t('admin.update.no_backups')) ?></p>
                        <?php else: ?>
                            <div class="backup-list">
                                <?php foreach ($backups as $backup): ?>
                                    <div class="backup-item">
                                        <div class="backup-info">
                                            <strong><?= htmlspecialchars($backup['filename']) ?></strong><br>
                                            <small>
                                                <?= e(dcs_t('admin.admins.created')) ?>: <?= date('Y-m-d H:i:s', $backup['date']) ?> |
                                                <?= e(dcs_t('admin.update.size')) ?>: <?= number_format($backup['size'] / 1024, 2) ?> KB
                                            </small>
                                        </div>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="action" value="restore_backup">
                                            <input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['filename']) ?>">
                                            <button type="submit" class="btn btn-sm"
                                                    onclick='return confirm(<?= json_encode(dcs_t('admin.update.confirm_restore')) ?>)'>
                                                <?= e(dcs_t('admin.update.restore')) ?>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
