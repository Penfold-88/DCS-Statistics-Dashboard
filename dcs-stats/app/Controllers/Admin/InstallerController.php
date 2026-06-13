<?php
/**
 * Admin Panel Installation Script
 * Run this script to set up the admin panel for first use
 */

// Check if already configured
$dataDir = DCS_ROOT_PATH . '/site-config/data';
$usersFile = $dataDir . '/users.json';
$apiConfigFile = $dataDir . '/api_config.json';
$legacyApiConfigFile = DCS_ROOT_PATH . '/api_config.json';
$siteConfigFile = DCS_ROOT_PATH . '/site_config.json';
$is_cli = (php_sapi_name() === 'cli');
\DcsStats\Core\SupportBootstrap::language();
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
$installerSupport = new \DcsStats\Services\Admin\InstallerSupportService();

function showInstallerLockedPage() {
    http_response_code(403);
    require DCS_APP_PATH . '/Views/Admin/installer/locked.php';
    exit;
}
$installerLanguage = dcs_language_code($_POST['install_language'] ?? $_GET['lang'] ?? 'en');
dcs_set_language_override($installerLanguage);

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

if (!$isDefaultInstall && file_exists($usersFile) && (file_exists($apiConfigFile) || file_exists($legacyApiConfigFile))) {
    if ($is_cli) {
        die("System appears to be already installed. Delete site-config/data/users.json and api_config.json to reinstall.\n");
    }

    \DcsStats\Core\AdminBootstrap::auth();
    requireAdmin();
    showInstallerLockedPage();
}

if ($is_cli) {
    echo "DCS Statistics Admin Panel Installer\n";
    echo "====================================\n\n";
}

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die("Error: PHP 7.4 or higher is required. You have " . PHP_VERSION . "\n");
}

// Check required extensions
$required_extensions = ['json', 'session', 'openssl', 'mbstring'];
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
\DcsStats\Core\SupportBootstrap::devMode();
$isDev = isDevMode();

// For web installation, provide a form interface
if (!$is_cli) {
    $readyToInstall = false;

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? 'admin';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $api_url = $_POST['api_url'] ?? '';
        $api_key = trim($_POST['api_key'] ?? '');
        $site_name = $_POST['site_name'] ?? 'DCS Statistics';
        $discord_url = $_POST['discord_url'] ?? '';
        $default_language = dcs_language_code($_POST['install_language'] ?? 'en');
        $update_branch = 'main'; // Always start with main branch
        
        // Validate inputs
        $errors = $installerSupport->validateInputs([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'api_url' => $api_url,
            'api_key' => $api_key,
        ]);
        
        // Test API connection with protocol auto-detection (skip in dev mode)
        if (empty($errors) && !empty($api_url)) {
            $apiResult = $installerSupport->resolveApiUrl($api_url, $api_key, $isDev);
            $api_url = $apiResult['url'];
            if (!$apiResult['connected']) {
                $errors[] = $apiResult['error'];
            }
        }
        
        if (empty($errors)) {
            $readyToInstall = true;
        }
    }
    
    // Show installation form
    if (!$readyToInstall) {
        $permissionFolders = [
            'dcs-stats/site-config/data/' => DCS_ROOT_PATH . '/site-config/data',
            'dcs-stats/uploads/' => DCS_ROOT_PATH . '/uploads',
            'dcs-stats/custom/' => DCS_ROOT_PATH . '/custom',
            'dcs-stats/backups/' => DCS_ROOT_PATH . '/backups',
        ];
        $permissionFiles = [
            'dcs-stats/site_config.json' => DCS_ROOT_PATH . '/site_config.json',
            'dcs-stats/menu_config.json' => DCS_ROOT_PATH . '/menu_config.json',
            'dcs-stats/custom_theme.css' => DCS_ROOT_PATH . '/custom_theme.css',
            'dcs-stats/header_custom.css' => DCS_ROOT_PATH . '/header_custom.css',
            'dcs-stats/.version_meta.json' => DCS_ROOT_PATH . '/.version_meta.json',
        ];
        $permissionFolderStatuses = $installerSupport->permissionStatuses($permissionFolders, 'dir');
        $permissionFileStatuses = $installerSupport->permissionStatuses($permissionFiles, 'file');
        $showInstallerForm = ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !empty($errors);
        require DCS_APP_PATH . '/Views/Admin/installer/form.php';
        exit;
    }
}

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

    echo "DCSServerBot API Key (optional, press Enter to skip): ";
    $api_key = trim(fgets(STDIN));
    if (!$installerSupport->isValidApiKey($api_key)) {
        die("Error: API key contains invalid characters.\n");
    }
    
    if (!$isDev) {
        echo "Testing connection...\n";

        $apiResult = $installerSupport->resolveApiUrl($api_url, $api_key, false);
        if (!$apiResult['connected']) {
            die("Error: Could not connect to API. Please check the address and ensure DCSServerBot is running.\n");
        }

        $api_url = $apiResult['url'];
        echo "✓ Connected successfully using " . parse_url($api_url, PHP_URL_SCHEME) . "\n";
    } else {
        $api_url = $installerSupport->resolveApiUrl($api_url, $api_key, true)['url'];
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
    'api_key' => !empty($api_key) ? $api_key : null,
    'timeout' => 30,
    'cache_ttl' => 300,
    'refresh_interval' => 300,
    'verify_ssl' => true,
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
        'stats' => '/stats',
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
    'default_language' => $default_language ?? 'en',
    'date_format' => 'd/m/Y',
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
\DcsStats\Core\SupportBootstrap::versionTracker();
\DcsStats\Core\SupportBootstrap::updateChannel();
// Define ADMIN_PANEL constant if not already defined
if (!defined('ADMIN_PANEL')) {
    define('ADMIN_PANEL', true);
}
\DcsStats\Core\AdminConfig::load();
$channelConfig = getUpdateChannelConfig();
$installBranch = $channelConfig['branch'] ?? 'main';
$githubVersionInfo = getGitHubBranchVersionInfo($channelConfig['repo'] ?? '', $installBranch);
updateVersionMetadata(
    ADMIN_PANEL_VERSION,
    $installBranch,
    'installer',
    $githubVersionInfo['commit_sha'] ?? null,
    $githubVersionInfo['commit_date'] ?? null
);

if ($is_cli) {
    echo "✓ Version tracking initialized\n";
}

$installerSelfDeleteStatus = 'not_attempted';
$installerSelfDeletePath = realpath(DCS_ROOT_PATH . '/site-config/install.php');
$installerDirPath = realpath(DCS_ROOT_PATH . '/site-config');
if ($installerSelfDeletePath !== false &&
    $installerDirPath !== false &&
    $installerSelfDeletePath === $installerDirPath . DIRECTORY_SEPARATOR . 'install.php' &&
    is_file($installerSelfDeletePath)) {
    $installerSelfDeleteStatus = @unlink($installerSelfDeletePath) ? 'removed' : 'failed';
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
    if ($installerSelfDeleteStatus === 'removed') {
        echo "\nSecurity cleanup: install.php was removed automatically.\n";
    } else {
        echo "\nFor security, delete or rename this install.php file.\n";
    }
} else {
    // Web installation success page
    require DCS_APP_PATH . '/Views/Admin/installer/complete.php';
}
