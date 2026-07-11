                <!-- Advanced CSS Upload Tab -->
                <div id="advanced-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.upload_custom_css')) ?></h2>
                        <div class="alert alert-warning">
                            <strong><?= e(dcs_t('admin.themes.warning')) ?>:</strong> <?= e(dcs_t('admin.themes.css_warning')) ?>
                        </div>

                        <form method="POST" action="" enctype="multipart/form-data" class="upload-section">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="upload_css">

                            <div class="file-input-wrapper">
                                <input type="file" name="css_file" id="css_file" accept=".css">
                                <label for="css_file" class="file-input-button"><?= e(dcs_t('admin.themes.choose_css_file')) ?></label>
                            </div>
                            <span id="file-name" style="margin-left: 10px;"><?= e(dcs_t('admin.themes.no_file_selected')) ?></span>

                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.themes.upload_css')) ?></button>
                            </div>
                        </form>

                        <div style="margin-top: 30px;">
                            <h3><?= e(dcs_t('admin.themes.css_guidelines')) ?></h3>
                            <ul>
                                <li><?= e(dcs_t('admin.themes.css_guideline_size')) ?></li>
                                <li><?= e(dcs_t('admin.themes.css_guideline_variables')) ?></li>
                                <li><?= e(dcs_t('admin.themes.css_guideline_test')) ?></li>
                                <li><?= e(dcs_t('admin.themes.css_guideline_mobile')) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
