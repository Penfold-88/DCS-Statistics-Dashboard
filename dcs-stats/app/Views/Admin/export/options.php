<div class="card">
    <div class="card-header">
        <h2 class="card-title">Export Options</h2>
    </div>

    <div class="export-option">
        <h3>Player Data</h3>
        <p>Export player names and statistics without private identifiers.</p>

        <form method="POST" action="api/export_data.php">
            <?= csrfField() ?>
            <input type="hidden" name="type" value="players">

            <div class="export-fields">
                <div class="form-group">
                    <label for="players_format">Format</label>
                    <select name="format" id="players_format" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="players_date_from">From Date (Optional)</label>
                    <input type="date" name="date_from" id="players_date_from" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                </div>

                <div class="form-group">
                    <label for="players_date_to">To Date (Optional)</label>
                    <input type="date" name="date_to" id="players_date_to" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                </div>
            </div>

            <button type="submit" name="export" class="btn btn-primary" <?= $demoRestricted ? 'disabled' : '' ?>>
                Export Player Data
            </button>
        </form>
    </div>

    <div class="export-option">
        <h3>Mission Statistics</h3>
        <p>Export detailed mission events including kills, deaths, and flight data.</p>

        <form method="POST" action="api/export_data.php">
            <?= csrfField() ?>
            <input type="hidden" name="type" value="missions">

            <div class="export-fields">
                <div class="form-group">
                    <label for="missions_format">Format</label>
                    <select name="format" id="missions_format" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="missions_date_from">From Date</label>
                    <input type="date" name="date_from" id="missions_date_from" class="form-control" required <?= $demoRestricted ? 'disabled' : '' ?>>
                </div>

                <div class="form-group">
                    <label for="missions_date_to">To Date</label>
                    <input type="date" name="date_to" id="missions_date_to" class="form-control" required <?= $demoRestricted ? 'disabled' : '' ?>>
                </div>
            </div>

            <button type="submit" name="export" class="btn btn-primary" <?= $demoRestricted ? 'disabled' : '' ?>>
                Export Mission Data
            </button>
        </form>
    </div>

    <div class="export-option">
        <h3>Admin Activity Logs</h3>
        <p>Export admin panel activity logs for audit purposes.</p>

        <form method="POST" action="api/export_data.php">
            <?= csrfField() ?>
            <input type="hidden" name="type" value="admin_logs">

            <div class="export-fields">
                <div class="form-group">
                    <label for="logs_format">Format</label>
                    <select name="format" id="logs_format" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="logs_date_from">From Date</label>
                    <input type="date" name="date_from" id="logs_date_from" class="form-control" required <?= $demoRestricted ? 'disabled' : '' ?>>
                </div>

                <div class="form-group">
                    <label for="logs_date_to">To Date</label>
                    <input type="date" name="date_to" id="logs_date_to" class="form-control" required <?= $demoRestricted ? 'disabled' : '' ?>>
                </div>
            </div>

            <button type="submit" name="export" class="btn btn-primary" <?= $demoRestricted ? 'disabled' : '' ?>>
                Export Activity Logs
            </button>
        </form>
    </div>

    <?php if ($currentAdmin['role'] == ROLE_AIR_BOSS): ?>
        <div class="export-option" style="border: 2px solid var(--accent-danger);">
            <h3 style="color: var(--accent-danger);">Full Data Export</h3>
            <p style="color: var(--accent-warning);">
                <strong>Warning:</strong> This will export ALL data from the system.
                It may include player identifiers and other administrative records. Large exports may take significant time.
            </p>

            <form method="POST" action="api/export_data.php" onsubmit="return confirm('Are you sure you want to export ALL data?');">
                <?= csrfField() ?>
                <input type="hidden" name="type" value="full">

                <div class="export-fields">
                    <div class="form-group">
                        <label for="full_format">Format</label>
                        <select name="format" id="full_format" class="form-control" <?= $demoRestricted ? 'disabled' : '' ?>>
                            <option value="json">JSON (Recommended)</option>
                            <option value="csv">CSV (Multiple Files)</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="export" class="btn btn-danger" <?= $demoRestricted ? 'disabled' : '' ?>>
                    Export All Data
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>
