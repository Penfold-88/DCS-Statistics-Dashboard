<div class="card">
    <div class="card-header">
        <h2 class="card-title">Recent Exports</h2>
    </div>

    <?php if (empty($recentExports)): ?>
        <p class="text-muted">No exports in the last 30 days.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Admin</th>
                    <th>Type</th>
                    <th>Format</th>
                    <th>Date Range</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentExports as $export): ?>
                    <tr>
                        <td><?= formatDate($export['created_at']) ?></td>
                        <td><?= e($export['admin']) ?></td>
                        <td><?= e($export['type']) ?></td>
                        <td><?= e($export['format']) ?></td>
                        <td>
                            <?php if (!empty($export['date_from']) && !empty($export['date_to'])): ?>
                                <?= e($export['date_from']) ?> to <?= e($export['date_to']) ?>
                            <?php else: ?>
                                All dates
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
