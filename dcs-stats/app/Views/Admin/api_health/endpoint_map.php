<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h2 class="card-title"><?= e(dcs_t('admin.api_health.configured_endpoint_map')) ?></h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th><?= e(dcs_t('admin.api_health.dashboard_key')) ?></th>
                    <th><?= e(dcs_t('admin.api_health.api_route')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($apiConfig['endpoints'] ?? []) as $key => $route): ?>
                <tr>
                    <td><?= e($key) ?></td>
                    <td><code><?= e($route) ?></code></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
