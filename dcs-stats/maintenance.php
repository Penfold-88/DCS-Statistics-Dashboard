<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include path configuration for URL helper
require_once __DIR__ . '/config_path.php';

// Load maintenance configuration
$maintenanceFile = __DIR__ . '/site-config/data/maintenance.json';
$maintenance = ['enabled' => false, 'ip_whitelist' => []];
if (file_exists($maintenanceFile)) {
    $data = json_decode(file_get_contents($maintenanceFile), true);
    if (is_array($data)) {
        $maintenance = array_merge($maintenance, $data);
    }
}

// Redirect to home if maintenance is disabled or IP is whitelisted
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (empty($maintenance['enabled']) || in_array($ip, $maintenance['ip_whitelist'])) {
    header('Location: index.php');
    exit;
}

// Return proper maintenance status code
http_response_code(503);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCS Statistics Dashboard</title>
    <link rel="stylesheet" href="<?php echo url('styles.php'); ?>">
    <link rel="stylesheet" href="<?php echo url('styles-mobile.css'); ?>">
    <?php if (file_exists(__DIR__ . '/custom_theme.css')): ?>
    <link rel="stylesheet" href="<?php echo url('custom_theme.css'); ?>">
    <?php endif; ?>
</head>
<body>
    <main>
        <div class="dashboard-header">
            <h1>DCS Server Statistics Dashboard</h1>
            <p class="dashboard-subtitle">Real-time server performance and player metrics</p>
        </div>
        <div class="maintenance-page">
            <div class="maintenance-icon">✖</div>
            <p>The DCS Statistics Dashboard is currently undergoing maintenance. Please try again later.</p>
        </div>
    </main>
</body>
</html>
