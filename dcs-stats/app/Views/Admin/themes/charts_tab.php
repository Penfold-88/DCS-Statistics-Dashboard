                <!-- Chart Colours Tab -->
                <div id="charts-tab" class="tab-content">
                    <div class="theme-section">
                        <h2><?= e(dcs_t('admin.themes.chart_colours')) ?></h2>
                        <p>Customize the colours used by the leaderboard and homepage charts.</p>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="action" value="update_chart_colors">

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.leaderboard_chart')) ?></legend>
                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="chart_primary_color" title="<?= e(dcs_t('admin.themes.chart_primary_title')) ?>"><?= e(dcs_t('admin.themes.chart_primary')) ?>:</label>
                                        <input type="color" id="chart_primary_color" name="chart_primary_color"
                                               value="<?= htmlspecialchars($chartColors['chart_primary_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="chart_secondary_color" title="<?= e(dcs_t('admin.themes.chart_secondary_title')) ?>"><?= e(dcs_t('admin.themes.chart_secondary')) ?>:</label>
                                        <input type="color" id="chart_secondary_color" name="chart_secondary_color"
                                               value="<?= htmlspecialchars($chartColors['chart_secondary_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="chart_grid_color" title="<?= e(dcs_t('admin.themes.grid_lines_title')) ?>"><?= e(dcs_t('admin.themes.grid_lines')) ?>:</label>
                                        <input type="color" id="chart_grid_color" name="chart_grid_color"
                                               value="<?= htmlspecialchars($chartColors['chart_grid_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="chart_text_color" title="<?= e(dcs_t('admin.themes.chart_text_title')) ?>"><?= e(dcs_t('admin.themes.chart_text')) ?>:</label>
                                        <input type="color" id="chart_text_color" name="chart_text_color"
                                               value="<?= htmlspecialchars($chartColors['chart_text_color']) ?>">
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.home_top_pilots')) ?></legend>
                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="home_top_pilots_color" title="<?= e(dcs_t('admin.themes.bars_title')) ?>"><?= e(dcs_t('admin.themes.bars')) ?>:</label>
                                        <input type="color" id="home_top_pilots_color" name="home_top_pilots_color"
                                               value="<?= htmlspecialchars($chartColors['home_top_pilots_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_top_pilots_grid_color" title="<?= e(dcs_t('admin.themes.grid_lines_title')) ?>"><?= e(dcs_t('admin.themes.grid_lines')) ?>:</label>
                                        <input type="color" id="home_top_pilots_grid_color" name="home_top_pilots_grid_color"
                                               value="<?= htmlspecialchars($chartColors['home_top_pilots_grid_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_top_pilots_text_color" title="<?= e(dcs_t('admin.themes.chart_text_title')) ?>"><?= e(dcs_t('admin.themes.text')) ?>:</label>
                                        <input type="color" id="home_top_pilots_text_color" name="home_top_pilots_text_color"
                                               value="<?= htmlspecialchars($chartColors['home_top_pilots_text_color']) ?>">
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.home_combat_stats')) ?></legend>
                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="home_combat_kills_color" title="<?= e(dcs_t('admin.themes.combat_kills_title')) ?>"><?= e(dcs_t('home.kills')) ?>:</label>
                                        <input type="color" id="home_combat_kills_color" name="home_combat_kills_color"
                                               value="<?= htmlspecialchars($chartColors['home_combat_kills_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_combat_deaths_color" title="<?= e(dcs_t('admin.themes.combat_deaths_title')) ?>"><?= e(dcs_t('home.deaths')) ?>:</label>
                                        <input type="color" id="home_combat_deaths_color" name="home_combat_deaths_color"
                                               value="<?= htmlspecialchars($chartColors['home_combat_deaths_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_combat_text_color" title="<?= e(dcs_t('admin.themes.chart_text_title')) ?>"><?= e(dcs_t('admin.themes.text')) ?>:</label>
                                        <input type="color" id="home_combat_text_color" name="home_combat_text_color"
                                               value="<?= htmlspecialchars($chartColors['home_combat_text_color']) ?>">
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.home_top_squadrons')) ?></legend>
                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="home_squadrons_color" title="<?= e(dcs_t('admin.themes.bars_title')) ?>"><?= e(dcs_t('admin.themes.bars')) ?>:</label>
                                        <input type="color" id="home_squadrons_color" name="home_squadrons_color"
                                               value="<?= htmlspecialchars($chartColors['home_squadrons_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_squadrons_grid_color" title="<?= e(dcs_t('admin.themes.grid_lines_title')) ?>"><?= e(dcs_t('admin.themes.grid_lines')) ?>:</label>
                                        <input type="color" id="home_squadrons_grid_color" name="home_squadrons_grid_color"
                                               value="<?= htmlspecialchars($chartColors['home_squadrons_grid_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_squadrons_text_color" title="<?= e(dcs_t('admin.themes.chart_text_title')) ?>"><?= e(dcs_t('admin.themes.text')) ?>:</label>
                                        <input type="color" id="home_squadrons_text_color" name="home_squadrons_text_color"
                                               value="<?= htmlspecialchars($chartColors['home_squadrons_text_color']) ?>">
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="color-fieldset">
                                <legend><?= e(dcs_t('admin.themes.home_player_activity')) ?></legend>
                                <div class="color-inputs">
                                    <div class="color-input-group">
                                        <label for="home_activity_color" title="<?= e(dcs_t('admin.themes.line_fill_title')) ?>"><?= e(dcs_t('admin.themes.line_and_fill')) ?>:</label>
                                        <input type="color" id="home_activity_color" name="home_activity_color"
                                               value="<?= htmlspecialchars($chartColors['home_activity_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_activity_grid_color" title="<?= e(dcs_t('admin.themes.grid_lines_title')) ?>"><?= e(dcs_t('admin.themes.grid_lines')) ?>:</label>
                                        <input type="color" id="home_activity_grid_color" name="home_activity_grid_color"
                                               value="<?= htmlspecialchars($chartColors['home_activity_grid_color']) ?>">
                                    </div>

                                    <div class="color-input-group">
                                        <label for="home_activity_text_color" title="<?= e(dcs_t('admin.themes.chart_text_title')) ?>"><?= e(dcs_t('admin.themes.text')) ?>:</label>
                                        <input type="color" id="home_activity_text_color" name="home_activity_text_color"
                                               value="<?= htmlspecialchars($chartColors['home_activity_text_color']) ?>">
                                    </div>
                                </div>
                            </fieldset>

                            <div style="margin-top: 20px; display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.themes.update_chart_colours')) ?></button>
                                <button type="button" class="btn btn-secondary" onclick="restoreDefaultChartColors()"><?= e(dcs_t('admin.themes.restore_defaults')) ?></button>
                            </div>
                        </form>
                    </div>
                </div>
