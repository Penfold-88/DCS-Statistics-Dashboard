<?php
/**
 * Admin Panel Installation Script
 * Run this script to set up the admin panel for first use
 */

// Check if already configured
$dataDir = __DIR__ . '/data';
$usersFile = $dataDir . '/users.json';
$apiConfigFile = dirname(__DIR__) . '/api_config.json';
$siteConfigFile = dirname(__DIR__) . '/site_config.json';

// Check if this is the auto-created default installation
$isDefaultInstall = false;
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true);
    if (count($users) === 1 && $users[0]['username'] === 'admin' && 
        $users[0]['email'] === 'admin@example.com' && 
        password_verify('', $users[0]['password_hash'])) {
        $isDefaultInstall = true;
        // Remove the default files to allow proper installation
        @unlink($usersFile);
        @unlink($dataDir . '/logs.json');
        @unlink($dataDir . '/bans.json');
        @unlink($dataDir . '/sessions.json');
    }
}

if (!$isDefaultInstall && file_exists($usersFile) && file_exists($apiConfigFile)) {
    die("System appears to be already installed. Delete site-config/data/users.json and api_config.json to reinstall.\n");
}

// Check if running from CLI or web
$is_cli = (php_sapi_name() === 'cli');

if ($is_cli) {
    echo "DCS Statistics Admin Panel Installer\n";
    echo "====================================\n\n";
}

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die("Error: PHP 7.4 or higher is required. You have " . PHP_VERSION . "\n");
}

// Check required extensions
$required_extensions = ['json', 'session', 'openssl', 'mbstring', 'zip'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die("Error: Missing required PHP extensions: " . implode(', ', $missing_extensions) . "\n");
}

if ($is_cli) {
    echo "✓ PHP version and extensions OK\n";
}

// Create data directory
if (!is_dir($dataDir)) {
    if (!mkdir($dataDir, 0700, true)) {
        die("Error: Could not create data directory. Please create it manually with permissions 700.\n");
    }
}

if ($is_cli) {
    echo "✓ Data directory created\n";
}

// Include dev mode detection
require_once dirname(__DIR__) . '/dev_mode.php';
$isDev = isDevMode();

// For web installation, provide a form interface
if (!$is_cli) {
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? 'admin';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $api_url = $_POST['api_url'] ?? '';
        $site_name = $_POST['site_name'] ?? 'DCS Statistics';
        $discord_url = $_POST['discord_url'] ?? '';
        $update_branch = 'main'; // Always start with main branch
        
        // Validate inputs
        $errors = [];
        if (empty($username)) {
            $errors[] = "Username is required";
        }
        if (empty($email)) {
            $errors[] = "Email is required";
        }
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
        if (empty($api_url)) {
            $errors[] = "API URL is required";
        }
        
        // Test API connection with protocol auto-detection (skip in dev mode)
        if (empty($errors) && !empty($api_url) && !$isDev) {
            // Remove any protocol if user included it
            $api_url = preg_replace('#^https?://#', '', $api_url);
            
            // Try HTTPS first, then HTTP
            $protocols = ['https', 'http'];
            $connected = false;
            
            foreach ($protocols as $protocol) {
                $test_url = $protocol . '://' . $api_url . '/servers';
                $ch = curl_init($test_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Allow self-signed certs
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code === 200) {
                    $api_url = $protocol . '://' . $api_url;
                    $connected = true;
                    break;
                }
            }
            
            if (!$connected) {
                $errors[] = "Could not connect to DCSServerBot API at $api_url. Please ensure:<br>
                • DCSServerBot is running<br>
                • The REST API is enabled in DCSServerBot<br>
                • The address and port are correct (default port is 9876)<br>
                • Firewall allows connections to the API port";
            }
        } elseif ($isDev && !empty($api_url)) {
            // In dev mode, just add protocol if missing
            if (!preg_match('#^https?://#', $api_url)) {
                $api_url = 'https://' . $api_url;
            }
        }
        
        if (empty($errors)) {
            // Proceed with installation
            goto do_install;
        }
    }
    
    // Show installation form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Panel Installation - DCS Statistics</title>
        <link rel="stylesheet" href="css/admin.css">
        <style>
            /* Installation specific overrides */
            body {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 20px;
            }
            .install-container {
                width: 100%;
                max-width: 600px;
            }
        </style>
    </head>
    <body>
        <div class="install-container">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">🚀 Admin Panel Installation</h1>
                </div>
                <p class="text-center text-muted mb-3">DCS Statistics Management System</p>
            <div class="card mb-3">
                <h3 class="text-success">System Requirements</h3>
                <p class="<?= version_compare(PHP_VERSION, '7.4.0', '>=') ? 'success' : 'error' ?>">
                    <?= version_compare(PHP_VERSION, '7.4.0', '>=') ? '✓' : '✗' ?> PHP version <?= PHP_VERSION ?> (7.4+ required)
                </p>
                <?php
                $required_extensions = ['json', 'session', 'openssl', 'mbstring', 'zip'];
                foreach ($required_extensions as $ext) {
                    $loaded = extension_loaded($ext);
                    echo '<p class="' . ($loaded ? 'success' : 'error') . '">';
                    echo ($loaded ? '✓' : '✗') . ' ' . $ext . ' extension';
                    echo '</p>';
                }
                ?>
                <p class="<?= is_writable(dirname($dataDir)) ? 'success' : 'error' ?>">
                    <?= is_writable(dirname($dataDir)) ? '✓' : '✗' ?> Write permissions
                </p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error mb-2">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($errors)): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Admin Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <input type="text" id="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Admin Password (min 8 characters)</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="8">
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">API Configuration</h3>
                    </div>
                    <p class="text-muted">Configure the connection to your DCSServerBot REST API</p>
                
                <div class="form-group">
                    <label for="api_url">DCSServerBot API URL</label>
                    <input type="text" id="api_url" name="api_url" class="form-control" placeholder="your-server:9876" required>
                    <?php if ($isDev): ?>
                    <small class="text-warning">⚠️ Dev Mode: API connection test will be skipped</small>
                    <?php else: ?>
                    <small class="text-muted">Example: 192.168.1.100:9876 or dcs.example.com:9876 (protocol will be auto-detected)</small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="site_name">Site Name</label>
                    <input type="text" id="site_name" name="site_name" class="form-control" value="<?= htmlspecialchars($_POST['site_name'] ?? 'DCS Statistics') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="discord_url">Discord Invite URL (optional)</label>
                    <input type="url" id="discord_url" name="discord_url" class="form-control" placeholder="https://discord.gg/your-invite">
                </div>
                
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Install Admin Panel</button>
            </form>
            
            <div class="alert alert-info">
                <strong>Note:</strong> You can also run this installer from the command line:<br>
                <code>php install.php</code>
            </div>
            <?php endif; ?>
            </div>
        </div>
        <?= getDevModeIndicator() ?>
    </body>
    </html>
    <?php
    exit;
}

do_install:

if ($is_cli) {
    // CLI installation
    echo "\nSetting up admin account...\n";
    echo "Username [admin]: ";
    $username = trim(fgets(STDIN)) ?: 'admin';
    
    echo "Email: ";
    $email = trim(fgets(STDIN));
    while (empty($email)) {
        echo "Please enter an email address: ";
        $email = trim(fgets(STDIN));
    }
    
    echo "Password: ";
    system('stty -echo');
    $password = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
    
    while (strlen($password) < 8) {
        echo "Password must be at least 8 characters. Try again: ";
        system('stty -echo');
        $password = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
    }
    
    echo "\nConfiguring API connection...\n";
    echo "DCSServerBot API URL (e.g., 192.168.1.100:9876): ";
    $api_url = trim(fgets(STDIN));
    while (empty($api_url)) {
        echo "Please enter the API address (host:port): ";
        $api_url = trim(fgets(STDIN));
    }
    
    // Auto-detect protocol
    $api_url = preg_replace('#^https?://#', '', $api_url);
    
    if (!$isDev) {
        echo "Testing connection...\n";
        
        $protocols = ['https', 'http'];
        $connected = false;
        
        foreach ($protocols as $protocol) {
            $test_url = $protocol . '://' . $api_url . '/servers';
            $ch = curl_init($test_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                $api_url = $protocol . '://' . $api_url;
                echo "✓ Connected successfully using $protocol\n";
                $connected = true;
                break;
            }
        }
        
        if (!$connected) {
            die("Error: Could not connect to API. Please check the address and ensure DCSServerBot is running.\n");
        }
    } else {
        // In dev mode, just add protocol
        $api_url = 'https://' . $api_url;
        echo "✓ Dev mode - skipping API connection test\n";
    }
    
    echo "Site Name [DCS Statistics]: ";
    $site_name = trim(fgets(STDIN)) ?: 'DCS Statistics';
    
    echo "Discord Invite URL (optional, press Enter to skip): ";
    $discord_url = trim(fgets(STDIN));
    
    $update_branch = 'main'; // Always start with main branch
}

// Create initial admin user
$admin = [
    'id' => 1,
    'username' => $username,
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
    'role' => 2, // Air Boss (highest role)
    'created_at' => date('Y-m-d H:i:s'),
    'last_login' => null,
    'is_active' => true,
    'failed_attempts' => 0,
    'locked_until' => null
];

// Create data files
$files = [
    'users.json' => [$admin],
    'logs.json' => [],
    'bans.json' => [],
    'sessions.json' => []
];

foreach ($files as $filename => $content) {
    $filepath = $dataDir . '/' . $filename;
    if (file_put_contents($filepath, json_encode($content, JSON_PRETTY_PRINT)) === false) {
        die("Error: Could not create $filename\n");
    }
    chmod($filepath, 0600);
}

if ($is_cli) {
    echo "✓ Data files created\n";
}

// Create .htaccess if not exists
$htaccess = $dataDir . '/.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "Order deny,allow\nDeny from all");
}

if ($is_cli) {
    echo "✓ Security files created\n";
}

// Test write permissions
$testFile = $dataDir . '/test.tmp';
if (file_put_contents($testFile, 'test') === false) {
    die("\nError: Data directory is not writable. Please check permissions.\n");
}
unlink($testFile);

if ($is_cli) {
    echo "✓ Write permissions OK\n";
}

// Create API configuration
$apiConfig = [
    'api_base_url' => rtrim($api_url ?? '', '/'),
    'api_key' => null,
    'timeout' => 30,
    'cache_ttl' => 300,
    'fallback_to_json' => false,
    'use_api' => true,
    'enabled_endpoints' => [
        'get_server_statistics.php',
        'get_leaderboard.php',
        'get_pilot_credits.php',
        'get_pilot_statistics.php',
        'get_player_stats.php',
        'get_squadrons.php',
        'get_servers.php',
        'get_active_players.php',
        'search_players.php'
    ],
    'endpoints' => [
        'getuser' => '/getuser',
        'player_info' => '/player_info',
        'topkills' => '/topkills',
        'topkdr' => '/topkdr',
        'weaponpk' => '/weaponpk'
    ]
];

if (file_put_contents($apiConfigFile, json_encode($apiConfig, JSON_PRETTY_PRINT)) === false) {
    die("Error: Could not create api_config.json\n");
}

if ($is_cli) {
    echo "✓ API configuration created\n";
}

// Create site configuration
$siteConfig = [
    'site_name' => $site_name ?? 'DCS Statistics',
    'discord_invite_url' => $discord_url ?? '',
    'theme' => 'dark',
    'maintenance_mode' => false,
    'allow_player_search' => true,
    'show_squadron_tab' => true,
    'show_servers_tab' => true
];

if (file_put_contents($siteConfigFile, json_encode($siteConfig, JSON_PRETTY_PRINT)) === false) {
    die("Error: Could not create site_config.json\n");
}

if ($is_cli) {
    echo "✓ Site configuration created\n";
}

// Create version metadata
require_once __DIR__ . '/version_tracker.php';
// Define ADMIN_PANEL constant if not already defined
if (!defined('ADMIN_PANEL')) {
    define('ADMIN_PANEL', true);
}
require_once __DIR__ . '/config.php';
updateVersionMetadata(ADMIN_PANEL_VERSION, 'main', 'installer');

if ($is_cli) {
    echo "✓ Version tracking initialized\n";
}

if ($is_cli) {
    echo "\n";
    echo "========================================\n";
    echo "Installation completed successfully!\n";
    echo "========================================\n\n";
    echo "You can now access the admin panel at:\n";
    echo "https://yoursite.com/dcs-stats/site-config/\n\n";
    echo "Login with:\n";
    echo "Username: $username\n";
    echo "Password: [the password you entered]\n";
    echo "\nNext steps:\n";
    echo "1. Login to the admin panel\n";
    echo "2. Change your password immediately\n";
    echo "3. Create additional admin users as needed\n";
    echo "4. Configure your settings\n";
    echo "\nFor security, delete or rename this install.php file.\n";
} else {
    // Web installation success page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Installation Complete - DCS Statistics</title>
        <link rel="stylesheet" href="css/admin.css">
        <style>
            body {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 20px;
            }
            .install-container {
                width: 100%;
                max-width: 500px;
            }
            .success-icon {
                font-size: 64px;
                color: var(--accent-primary);
                text-align: center;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="install-container">
            <div class="card text-center">
                <div class="success-icon">✓</div>
                <h1 class="text-success mb-3">Installation Complete!</h1>
                
                <div class="card mb-3">
                    <h3 class="card-title">Your admin account has been created:</h3>
                    <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
                    <p class="text-muted"><strong>Password:</strong> [the password you entered]</p>
                </div>
                
                <div class="card mb-3">
                    <h3 class="card-title">API Configuration:</h3>
                    <p><strong>API Endpoint:</strong> <?= htmlspecialchars($api_url) ?></p>
                    <p><strong>Site Name:</strong> <?= htmlspecialchars($site_name) ?></p>
                    <?php if (!empty($discord_url)): ?>
                    <p><strong>Discord:</strong> <?= htmlspecialchars($discord_url) ?></p>
                    <?php endif; ?>
                </div>
                
                <a href="login.php" class="btn btn-primary" style="width: 100%;">Go to Admin Login</a>
                
                <div class="alert alert-warning mt-3">
                    <strong>⚠️ Security Notice:</strong><br>
                    Please delete or rename this install.php file after logging in.
                </div>
            </div>
        </div>
        <?= getDevModeIndicator() ?>
    </body>
    </html>
    <?php
}