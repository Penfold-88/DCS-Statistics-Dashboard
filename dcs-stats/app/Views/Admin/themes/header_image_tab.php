                <div id="header-image-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.header_image')) ?></h2>
                        <p><?= e(dcs_t('admin.themes.header_image_help')) ?></p>
                        <p style="font-size: 0.9em; color: var(--text-muted); margin-top: 10px;">
                            Best fit: 2400 x 500 pixels or wider, JPG/PNG/WebP, under 5MB.
                        </p>

                        <div class="header-image-preview"
                             style="background-image: url('<?= htmlspecialchars($headerPreviewImage) ?>'); background-position: <?= (int)$headerImageSettings['position_x'] ?>% <?= (int)$headerImageSettings['position_y'] ?>%;">
                            <span><?= e(dcs_t('admin.themes.current_header_framing')) ?></span>
                        </div>

                        <form method="POST" action="" enctype="multipart/form-data" class="upload-section">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="update_header_image">

                            <div class="file-input-wrapper">
                                <input type="file" name="header_image" id="header_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <label for="header_image" class="file-input-button"><?= e(dcs_t('admin.themes.choose_header_image')) ?></label>
                            </div>
                            <span id="header-image-file-name" style="margin-left: 10px;">No new image selected</span>

                            <div class="color-input-group" style="margin-top: 18px; max-width: 420px;">
                                <label for="use_default_header_image"><?= e(dcs_t('admin.themes.use_default_header_image')) ?>:</label>
                                <input type="checkbox" id="use_default_header_image" name="use_default_header_image">
                            </div>

                            <div class="header-position-controls">
                                <label for="position_x">
                                    <?= e(dcs_t('admin.themes.horizontal_position')) ?>
                                    <input type="range" id="position_x" name="position_x" min="0" max="100" value="<?= (int)$headerImageSettings['position_x'] ?>">
                                </label>
                                <label for="position_y">
                                    <?= e(dcs_t('admin.themes.vertical_position')) ?>
                                    <input type="range" id="position_y" name="position_y" min="0" max="100" value="<?= (int)$headerImageSettings['position_y'] ?>">
                                </label>
                            </div>

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.page_background_image')) ?></legend>
                                <p style="font-size: 0.9em; color: var(--text-muted); margin-top: 0;">
                                    <?= e(dcs_t('admin.themes.page_background_image_help')) ?>
                                </p>
                                <p style="font-size: 0.9em; color: var(--text-muted); margin-top: 10px;">
                                    <?= e(dcs_t('admin.themes.page_background_image_size')) ?>
                                </p>
                                <p style="font-size: 0.9em; color: var(--warning, #ffc107); margin-top: 10px;">
                                    <?= e(dcs_t('admin.themes.page_background_image_desktop_only')) ?>
                                </p>

                                <div class="page-background-preview"
                                     style="<?= $backgroundPreviewImage ? "background-image: url('" . htmlspecialchars($backgroundPreviewImage) . "'); background-position: " . (int)$headerImageSettings['background_position_x'] . "% " . (int)$headerImageSettings['background_position_y'] . "%; background-size: " . (int)$headerImageSettings['background_zoom'] . "% auto;" : '' ?>">
                                    <span><?= e($backgroundPreviewImage ? dcs_t('admin.themes.current_background_framing') : dcs_t('admin.themes.no_background_image')) ?></span>
                                </div>

                                <div class="file-input-wrapper">
                                    <input type="file" name="background_image" id="background_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                    <label for="background_image" class="file-input-button"><?= e(dcs_t('admin.themes.choose_background_image')) ?></label>
                                </div>
                                <span id="background-image-file-name" style="margin-left: 10px;"><?= e(dcs_t('admin.themes.no_new_background_selected')) ?></span>

                                <div class="color-input-group" style="margin-top: 18px; max-width: 420px;">
                                    <label for="remove_background_image"><?= e(dcs_t('admin.themes.remove_background_image')) ?>:</label>
                                    <input type="checkbox" id="remove_background_image" name="remove_background_image">
                                </div>

                                <div class="header-position-controls">
                                    <label for="background_position_x">
                                        <?= e(dcs_t('admin.themes.background_horizontal_position')) ?>
                                        <input type="range" id="background_position_x" name="background_position_x" min="0" max="100" value="<?= (int)$headerImageSettings['background_position_x'] ?>">
                                    </label>
                                    <label for="background_position_y">
                                        <?= e(dcs_t('admin.themes.background_vertical_position')) ?>
                                        <input type="range" id="background_position_y" name="background_position_y" min="0" max="100" value="<?= (int)$headerImageSettings['background_position_y'] ?>">
                                    </label>
                                    <label for="background_zoom">
                                        <?= e(dcs_t('admin.themes.background_zoom')) ?>
                                        <input type="range" id="background_zoom" name="background_zoom" min="100" max="180" value="<?= (int)$headerImageSettings['background_zoom'] ?>">
                                    </label>
                                </div>
                            </fieldset>

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.header_branding')) ?></legend>
                                <p style="font-size: 0.9em; color: var(--text-muted); margin-top: 0;">
                                    Recommended logo size: transparent PNG/WebP around 360 x 96 pixels. Keep it under 2MB so the header stays the same height.
                                </p>

                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="branding_mode"><?= e(dcs_t('admin.themes.header_branding')) ?>:</label>
                                        <select id="branding_mode" name="branding_mode" class="form-control">
                                            <option value="text" <?= $headerImageSettings['branding_mode'] === 'text' ? 'selected' : '' ?>><?= e(dcs_t('admin.themes.text_only')) ?></option>
                                            <option value="both" <?= $headerImageSettings['branding_mode'] === 'both' ? 'selected' : '' ?>><?= e(dcs_t('admin.themes.logo_and_text')) ?></option>
                                            <option value="logo" <?= $headerImageSettings['branding_mode'] === 'logo' ? 'selected' : '' ?>><?= e(dcs_t('admin.themes.logo_only')) ?></option>
                                        </select>
                                    </div>

                                    <div class="color-input-group">
                                        <label for="logo_height"><?= e(dcs_t('admin.themes.logo_height')) ?>:</label>
                                        <input type="range" id="logo_height" name="logo_height" min="32" max="96" value="<?= (int)$headerImageSettings['logo_height'] ?>">
                                    </div>
                                </div>

                                <div class="header-logo-preview" style="--preview_logo_height: <?= (int)$headerImageSettings['logo_height'] ?>px;">
                                    <?php if ($headerLogoPreview): ?>
                                        <img src="<?= htmlspecialchars($headerLogoPreview) ?>" alt="Header logo preview">
                                    <?php else: ?>
                                        <span class="header-logo-placeholder">No logo uploaded</span>
                                    <?php endif; ?>
                                </div>

                                <div class="file-input-wrapper">
                                    <input type="file" name="header_logo" id="header_logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                    <label for="header_logo" class="file-input-button"><?= e(dcs_t('admin.themes.choose_header_logo')) ?></label>
                                </div>
                                <span id="header-logo-file-name" style="margin-left: 10px;">No new logo selected</span>

                                <div class="color-input-group" style="margin-top: 18px; max-width: 420px;">
                                    <label for="remove_header_logo"><?= e(dcs_t('admin.themes.remove_header_logo')) ?>:</label>
                                    <input type="checkbox" id="remove_header_logo" name="remove_header_logo">
                                </div>
                            </fieldset>

                            <button type="submit" class="btn btn-primary" style="margin-top: 20px;"><?= e(dcs_t('admin.themes.update_header_image')) ?></button>
                        </form>
                    </div>
                </div>
