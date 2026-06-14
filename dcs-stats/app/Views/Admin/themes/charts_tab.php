                <!-- Chart Colours Tab -->
                <div id="charts-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.chart_colours')) ?></h2>
                        <p>Customize the colours used by the leaderboard and homepage charts.</p>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="update_chart_colors">

                            <?php foreach ($chartThemeFieldGroups as $legend => $fields): ?>
                                <fieldset class="color-fieldset">
                                    <legend><?= e($legend) ?></legend>
                                    <div class="color-inputs">
                                        <?php foreach ($fields as $field): ?>
                                            <div class="color-input-group">
                                                <label for="<?= e($field['key']) ?>" title="<?= e($field['title']) ?>"><?= e($field['label']) ?>:</label>
                                                <input type="color" id="<?= e($field['key']) ?>" name="<?= e($field['key']) ?>"
                                                       value="<?= htmlspecialchars($chartColors[$field['key']]) ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </fieldset>
                            <?php endforeach; ?>

                            <div style="margin-top: 20px; display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.themes.update_chart_colours')) ?></button>
                                <button type="button" class="btn btn-secondary" data-theme-action="restore-default-chart-colors"><?= e(dcs_t('admin.themes.restore_defaults')) ?></button>
                            </div>
                        </form>
                    </div>
                </div>
