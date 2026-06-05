<?php
/**
 * Admin API Settings Page - Configure DCSServerBot API
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once __DIR__ . '/demo_helpers.php';
require_once __DIR__ . '/../api_config_helper.php';
require_once __DIR__ . '/../api_cache.php';
require_once __DIR__ . '/../language.php';

// Require admin login and permission
requireAdmin();
requirePermission('manage_api');

// Get current admin
$currentAdmin = getCurrentAdmin();
$demoRestricted = isDemoRestricted($currentAdmin);

// Load current API configuration with auto-fixing
$configResult = loadApiConfigWithFix();
$apiConfig = $configResult['config'];
$configFile = $configResult['config_path'];
$autoFixMessage = '';
$envApiKeyActive = !empty($apiConfig['api_key_env_override']);

// Show any auto-fix messages
if (isset($configResult['fixed']) && $configResult['fixed'] && !empty($configResult['changes'])) {
    $autoFixMessage = dcs_t('admin.api.auto_fixed') . ': ' . implode(', ', $configResult['changes']);
}

// Show config location if not standard. Hide server paths on the public demo.
if (!$demoRestricted && $configFile !== dirname(__DIR__) . '/api_config.json') {
    $autoFixMessage .= ($autoFixMessage ? ' | ' : '') . dcs_t('admin.api.config_location') . ': ' . $configFile;
}

// Handle form submission
$message = '';
$messageType = '';
$testResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = ERROR_MESSAGES['csrf_invalid'];
        $messageType = 'error';
    } elseif ($demoRestricted) {
        $message = demoWriteLockMessage();
        $messageType = 'error';
    } else {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'save':
                    // Get form inputs
                    $apiHost = trim($_POST['api_host'] ?? '');
                    $apiKeyInput = trim($_POST['api_key'] ?? '');
                    $existingApiKey = $envApiKeyActive ? null : ($apiConfig['api_key'] ?? null);
                    $timeout = intval($_POST['timeout'] ?? 30);
                    $cacheTtl = intval($_POST['cache_ttl'] ?? 300);
                    $refreshInterval = intval($_POST['refresh_interval'] ?? 300);
                    $useApi = isset($_POST['use_api']);
                    
                    // Use helper to create complete config
                    $saveResult = createApiConfigFromHost($apiHost, $configFile);
                    
                    if ($saveResult['success']) {
                        // Update with form values
                        $apiConfig = $saveResult['config'];
                        $apiConfig['timeout'] = $timeout;
                        $apiConfig['cache_ttl'] = $cacheTtl;
                        $apiConfig['refresh_interval'] = in_array($refreshInterval, [300, 600, 1800, 3600], true) ? $refreshInterval : 300;
                        $apiConfig['use_api'] = $useApi;
                        if ($apiKeyInput !== '') {
                            $apiConfig['api_key'] = $apiKeyInput;
                        } elseif (!empty($existingApiKey)) {
                            $apiConfig['api_key'] = $existingApiKey;
                        }
                        unset($apiConfig['api_key_source'], $apiConfig['api_key_env_override'], $apiConfig['stored_api_key_present']);
                        
                        // Save again with all settings
                        if (file_put_contents($configFile, json_encode($apiConfig, JSON_PRETTY_PRINT))) {
                            logAdminActivity('API_CONFIG_CHANGE', $_SESSION['admin_id'], 'settings', 'api_config', $apiConfig);
                            $message = dcs_t('admin.api.save_success');
                            $messageType = 'success';
                        }
                    } else {
                        $message = $saveResult['message'];
                        $messageType = 'error';
                    }
                    break;
                    
                case 'test':
                    // Test API connection using enhanced client with auto-detection
                    require_once dirname(__DIR__) . '/api_client_enhanced.php';
                    
                    try {
                        // Create enhanced client
                        $client = createEnhancedAPIClient();
                        
                        // Try to make a simple request
                        $result = $client->request('/servers', null, 'GET');
                        
                        if ($result !== null) {
                            $protocol = $client->getDetectedProtocol();
                            $detectedUrl = $client->getApiBaseUrl();
                            
                            // Update config with detected protocol
                            $apiHost = $apiConfig['api_host'] ?? preg_replace('#^https?://#', '', $apiConfig['api_base_url']);
                            $apiConfig['api_host'] = $apiHost;
                            $apiConfig['api_base_url'] = $detectedUrl;
                            
                            // Save the configuration with detected protocol
                            $saveConfig = $apiConfig;
                            $saveConfig['api_base_url'] = $detectedUrl;
                            
                            if (@file_put_contents($configFile, json_encode($saveConfig, JSON_PRETTY_PRINT))) {
                                $testResult = [
                                    'success' => true, 
                                    'message' => dcs_t('admin.api.test_success_saved', ['protocol' => $protocol]),
                                    'protocol' => $protocol
                                ];
                            } else {
                                $testResult = [
                                    'success' => true, 
                                    'message' => dcs_t('admin.api.test_success', ['protocol' => $protocol]),
                                    'protocol' => $protocol
                                ];
                            }
                        } else {
                            $testResult = ['success' => false, 'message' => dcs_t('admin.api.no_response')];
                        }
                    } catch (Exception $e) {
                        $errorMsg = $e->getMessage();
                        
                        // The enhanced client should have handled protocol detection
                        // If we still get an error, it's a real connection issue
                        $testResult = [
                            'success' => false, 
                            'message' => dcs_t('admin.api.connect_failed') . ': ' . $errorMsg
                        ];
                    }
                    break;

                case 'clear_cache':
                    $removed = apiCacheClear();
                    logAdminActivity('API_CACHE_CLEAR', $_SESSION['admin_id'], 'settings', 'api_cache', ['removed' => $removed]);
                    $message = dcs_t('admin.api.cache_clear_success', ['count' => number_format($removed)]);
                    $messageType = 'success';
                    break;
            }
        }
    }
}

// Page title
$pageTitle = dcs_t('admin.api.title');
$apiHostValue = $apiConfig['api_host'] ?? preg_replace('#^https?://#', '', $apiConfig['api_base_url']);
$displayApiHost = $demoRestricted ? maskDemoValue($apiHostValue) : $apiHostValue;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .api-form {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--accent-primary);
            font-weight: bold;
        }
        
        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 10px;
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: 4px;
            color: var(--text-primary);
            font-size: 14px;
        }
        
        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus,
        .form-group input[type="number"]:focus,
        .form-group select:focus {
            border-color: var(--accent-primary);
            outline: none;
        }
        
        .form-group .help-text {
            margin-top: 5px;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }
        
        .test-section {
            background-color: var(--bg-tertiary);
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .test-result {
            margin-top: 15px;
            padding: 15px;
            border-radius: 4px;
        }
        
        .test-result.success {
            background-color: rgba(76, 175, 80, 0.2);
            border: 1px solid var(--accent-success);
            color: var(--accent-success);
        }
        
        .test-result.error {
            background-color: rgba(244, 67, 54, 0.2);
            border: 1px solid var(--accent-danger);
            color: var(--accent-danger);
        }
        
        .endpoints-info {
            background-color: var(--bg-tertiary);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .endpoints-info h4 {
            margin-bottom: 15px;
            color: var(--accent-primary);
        }
        
        .endpoints-list {
            font-family: monospace;
            font-size: 13px;
            line-height: 1.8;
        }

        .endpoints-list ul {
            margin: 8px 0 18px;
            padding-left: 20px;
        }

        .endpoints-list code {
            color: var(--accent-primary);
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        /* Critical inline CSS for layout */
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 250px; flex-shrink: 0; background: #2a2a2a; }
        .admin-main { flex: 1; min-width: 0; overflow-x: hidden; }
        .admin-content { padding: 30px; max-width: 100%; overflow-x: hidden; }
        .card { max-width: 100%; overflow-x: auto; }
    </style>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php include __DIR__ . '/nav.php'; ?>
        
        <main class="admin-main">
            <div class="admin-content">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= e($message) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($autoFixMessage): ?>
                    <div class="alert alert-info">
                        <?= e($autoFixMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if ($demoRestricted): ?>
                    <div class="alert alert-info">
                        <?= e(demoRestrictionMessage()) ?>
                    </div>
                <?php endif; ?>

                <?php if ($envApiKeyActive): ?>
                    <div class="alert alert-info">
                        <?= e(dcs_t('admin.api.env_key_active')) ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.api.configuration_title')) ?></h2>
                    </div>
                    
                    <form method="POST" action="" class="api-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="save">
                        
                        <div class="form-group">
                            <label for="api_host"><?= e(dcs_t('admin.api.host')) ?></label>
                            <input type="text" 
                                   id="api_host" 
                                   name="api_host" 
                                   value="<?= e($displayApiHost) ?>"
                                   placeholder="localhost:8080"
                                   pattern="[a-zA-Z0-9.-]+:[0-9]+"
                                   <?= $demoRestricted ? 'disabled' : '' ?>>
                            <div class="help-text"><?= e(dcs_t('admin.api.host_help')) ?></div>
                        </div>

                        <div class="form-group">
                            <label for="api_key">
                                <?= e(dcs_t('admin.api.api_key')) ?>
                                <span class="text-muted"><?= e(dcs_t('admin.api.api_key_optional')) ?></span>
                            </label>
                            <input type="password"
                                   id="api_key"
                                   name="api_key"
                                   value=""
                                   autocomplete="new-password"
                                   placeholder="<?= $demoRestricted || $envApiKeyActive ? '••••••••' : (!empty($apiConfig['api_key']) ? e(dcs_t('admin.api.api_key_saved')) : e(dcs_t('admin.api.api_key_placeholder'))) ?>"
                                   <?= $demoRestricted ? 'disabled' : '' ?>>
                            <div class="help-text"><?= e($envApiKeyActive ? dcs_t('admin.api.api_key_env_help') : dcs_t('admin.api.api_key_help')) ?></div>
                        </div>
                        
                        
                        <div class="form-group">
                            <label for="timeout"><?= e(dcs_t('admin.api.timeout')) ?></label>
                            <input type="number" 
                                   id="timeout" 
                                   name="timeout" 
                                   value="<?= $apiConfig['timeout'] ?>"
                                   min="5" 
                                   max="300"
                                   <?= $demoRestricted ? 'disabled' : '' ?>>
                            <div class="help-text"><?= e(dcs_t('admin.api.timeout_help')) ?></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="refresh_interval"><?= e(dcs_t('admin.api.refresh_rate')) ?></label>
                            <?php $refreshInterval = (int)($apiConfig['refresh_interval'] ?? 300); ?>
                            <select id="refresh_interval" name="refresh_interval" <?= $demoRestricted ? 'disabled' : '' ?>>
                                <option value="300" <?= $refreshInterval === 300 ? 'selected' : '' ?>><?= e(dcs_t('admin.api.refresh_5')) ?></option>
                                <option value="600" <?= $refreshInterval === 600 ? 'selected' : '' ?>><?= e(dcs_t('admin.api.refresh_10')) ?></option>
                                <option value="1800" <?= $refreshInterval === 1800 ? 'selected' : '' ?>><?= e(dcs_t('admin.api.refresh_30')) ?></option>
                                <option value="3600" <?= $refreshInterval === 3600 ? 'selected' : '' ?>><?= e(dcs_t('admin.api.refresh_60')) ?></option>
                            </select>
                            <div class="help-text"><?= e(dcs_t('admin.api.refresh_help')) ?></div>
                        </div>

                        <div class="form-group">
                            <label for="cache_ttl"><?= e(dcs_t('admin.api.cache_ttl')) ?></label>
                            <input type="number" 
                                   id="cache_ttl" 
                                   name="cache_ttl" 
                                   value="<?= $apiConfig['cache_ttl'] ?>"
                                   min="0" 
                                   max="3600"
                                   <?= $demoRestricted ? 'disabled' : '' ?>>
                            <div class="help-text"><?= e(dcs_t('admin.api.cache_help')) ?></div>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" 
                                   id="use_api" 
                                   name="use_api" 
                                   value="1"
                                   <?= $apiConfig['use_api'] ? 'checked' : '' ?>
                                   <?= $demoRestricted ? 'disabled' : '' ?>>
                            <label for="use_api"><?= e(dcs_t('admin.api.enable_integration')) ?></label>
                        </div>
                        
                        
                        <div class="button-group">
                            <button type="submit" class="btn btn-primary" <?= $demoRestricted ? 'disabled' : '' ?>><?= e(dcs_t('admin.api.save_configuration')) ?></button>
                            <button type="submit" class="btn btn-secondary" name="action" value="test" <?= $demoRestricted ? 'disabled' : '' ?>><?= e(dcs_t('admin.api.test_connection')) ?></button>
                            <button type="submit" class="btn btn-secondary" formnovalidate onclick="this.form.querySelector('input[name=action]').value='clear_cache';"><?= e(dcs_t('admin.api.clear_cache')) ?></button>
                            <a href="api_health.php" class="btn btn-secondary"><?= e(dcs_t('admin.api.health_debug')) ?></a>
                        </div>
                    </form>
                </div>
                
                <?php if ($testResult): ?>
                    <div class="test-section">
                        <h3><?= e(dcs_t('admin.api.connection_test_result')) ?></h3>
                        <div class="test-result <?= $testResult['success'] ? 'success' : 'error' ?>">
                            <?= e($testResult['message']) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="endpoints-info">
                    <h4><?= e(dcs_t('admin.api.available_endpoints')) ?></h4>
                    <div class="endpoints-list">
                        <strong><?= e(dcs_t('admin.api.rest_endpoints')) ?>:</strong>
                        <ul>
                            <li><code>GET /servers</code> - <?= e(dcs_t('admin.api.endpoint_servers')) ?></li>
                            <li><code>GET /serverstats</code> - <?= e(dcs_t('admin.api.endpoint_serverstats')) ?></li>
                            <li><code>GET /server_attendance</code> - <?= e(dcs_t('admin.api.endpoint_attendance')) ?></li>
                            <li><code>GET /leaderboard</code> - <?= e(dcs_t('admin.api.endpoint_leaderboard')) ?></li>
                            <li><code>GET /highscore</code> - <?= e(dcs_t('admin.api.endpoint_highscore')) ?></li>
                            <li><code>GET /trueskill</code> - <?= e(dcs_t('admin.api.endpoint_trueskill')) ?></li>
                            <li><code>POST /player_info</code> - <?= e(dcs_t('admin.api.endpoint_player_info')) ?></li>
                            <li><code>POST /stats</code> - <?= e(dcs_t('admin.api.endpoint_stats')) ?></li>
                            <li><code>POST /getuser</code> - <?= e(dcs_t('admin.api.endpoint_getuser')) ?></li>
                            <li><code>POST /credits</code> - <?= e(dcs_t('admin.api.endpoint_credits')) ?></li>
                            <li><code>POST /modulestats</code> - <?= e(dcs_t('admin.api.endpoint_modulestats')) ?></li>
                            <li><code>POST /traps</code> - <?= e(dcs_t('admin.api.endpoint_traps')) ?></li>
                            <li><code>POST /weaponpk</code> - <?= e(dcs_t('admin.api.endpoint_weaponpk')) ?></li>
                            <li><code>GET /squadrons</code> - <?= e(dcs_t('admin.api.endpoint_squadrons')) ?></li>
                            <li><code>POST /player_squadrons</code> - <?= e(dcs_t('admin.api.endpoint_player_squadrons')) ?></li>
                            <li><code>POST /squadron_members</code> - <?= e(dcs_t('admin.api.endpoint_squadron_members')) ?></li>
                            <li><code>POST /squadron_credits</code> - <?= e(dcs_t('admin.api.endpoint_squadron_credits')) ?></li>
                            <li><code>GET /current_server</code> - <?= e(dcs_t('admin.api.endpoint_current_server')) ?></li>
                            <li><code>GET /airbases</code>, <code>GET /airbase</code>, <code>GET /airbase/atis</code>, <code>GET /airbase/warehouse</code> - <?= e(dcs_t('admin.api.endpoint_airbases')) ?></li>
                            <li><code>GET /convertCoordinates</code> - <?= e(dcs_t('admin.api.endpoint_coordinates')) ?></li>
                            <li><code>GET /mission/group/waypoints</code> - <?= e(dcs_t('admin.api.endpoint_waypoints')) ?></li>
                        </ul>

                        <strong><?= e(dcs_t('admin.api.dashboard_endpoints')) ?>:</strong>
                        <ul>
                            <li><code>get_servers_api.php</code> / <code>get_servers.php</code> - <?= e(dcs_t('admin.api.dashboard_servers')) ?></li>
                            <li><code>get_server_stats.php</code> - <?= e(dcs_t('admin.api.dashboard_server_stats')) ?></li>
                            <li><code>get_leaderboard_api.php</code> / <code>get_leaderboard.php</code> - <?= e(dcs_t('admin.api.dashboard_leaderboard')) ?></li>
                            <li><code>get_player_stats_api.php</code> / <code>get_player_stats.php</code> - <?= e(dcs_t('admin.api.dashboard_player_stats')) ?></li>
                            <li><code>get_credits_api.php</code> / <code>get_credits.php</code> - <?= e(dcs_t('admin.api.dashboard_credits')) ?></li>
                            <li><code>get_missionstats_api.php</code> / <code>get_missionstats.php</code> - <?= e(dcs_t('admin.api.dashboard_missionstats')) ?></li>
                            <li><code>get_squadrons_api.php</code> / <code>get_squadrons.php</code> - <?= e(dcs_t('admin.api.dashboard_squadrons')) ?></li>
                            <li><code>get_squadron_members_api.php</code> / <code>get_squadron_members.php</code> - <?= e(dcs_t('admin.api.dashboard_squadron_members')) ?></li>
                            <li><code>get_squadron_credits_api.php</code> / <code>get_squadron_credits.php</code> - <?= e(dcs_t('admin.api.dashboard_squadron_credits')) ?></li>
                            <li><code>get_api_config.php</code> - <?= e(dcs_t('admin.api.dashboard_api_config')) ?></li>
                            <li><code>get_leaderboard_client.php</code> - <?= e(dcs_t('admin.api.dashboard_leaderboard_client')) ?></li>
                        </ul>

                        <strong><?= e(dcs_t('admin.common.note')) ?>:</strong> <?= e(dcs_t('admin.api.expanded_note')) ?>
                    </div>
                </div>

<script>
    // Handle test button - remove the complex AJAX functionality since test_api_connection.php doesn't exist
    // The test is handled server-side when the form is submitted with action=test
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form.api-form');
        const actionInput = form.querySelector('input[name="action"]');
        const testBtn = document.querySelector('button[value="test"]');
        
        if (testBtn) {
            testBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const apiHostInput = document.getElementById('api_host');
                if (!apiHostInput.value.trim()) {
                    alert(<?= json_encode(dcs_t('admin.api.enter_host_first')) ?>);
                    return;
                }
                
                // Set the action to test and submit the form
                actionInput.value = 'test';
                form.submit();
            });
        }
    });
</script>
            </div>
        </main>
    </div>
</body>
</html>
