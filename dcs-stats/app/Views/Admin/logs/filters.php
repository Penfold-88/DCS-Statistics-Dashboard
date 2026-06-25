<div class="card">
    <div class="card-header">
        <h2 class="card-title">Filters</h2>
    </div>
    <form method="GET" action="" class="filter-form">
        <div class="form-group">
            <label for="date_from">From Date</label>
            <input type="date"
                   id="date_from"
                   name="date_from"
                   class="form-control"
                   value="<?= e($filterDateFrom) ?>">
        </div>

        <div class="form-group">
            <label for="date_to">To Date</label>
            <input type="date"
                   id="date_to"
                   name="date_to"
                   class="form-control"
                   value="<?= e($filterDateTo) ?>">
        </div>

        <div class="form-group">
            <label for="action">Action Type</label>
            <select id="action" name="action" class="form-control">
                <option value="">All Actions</option>
                <?php foreach ($uniqueActions as $action): ?>
                    <option value="<?= e($action) ?>" <?= $action === $filterAction ? 'selected' : '' ?>>
                        <?= e(LOG_ACTIONS[$action] ?? $action) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="admin">Admin User</label>
            <select id="admin" name="admin" class="form-control">
                <option value="">All Users</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= e($user['id']) ?>" <?= $user['id'] == $filterAdmin ? 'selected' : '' ?>>
                        <?= e($user['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="display: flex; align-items: flex-end; gap: 10px;">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="logs.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>
