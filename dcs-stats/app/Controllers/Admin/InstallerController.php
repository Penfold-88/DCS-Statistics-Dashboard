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
$installerConfigFactory = new \DcsStats\Services\Admin\InstallerConfigFactory();
$installerEnvironment = new \DcsStats\Services\Admin\InstallerEnvironmentService();
$installerWebForm = new \DcsStats\Services\Admin\InstallerWebFormService($installerSupport);

function showInstallerLockedPage() {
    http_response_code(403);
    require DCS_APP_PATH . '/Views/Admin/installer/locked.php';
    exit;
}
$installerLanguage = dcs_language_code($_POST['install_language'] ?? $_GET['lang'] ?? 'en');
dcs_set_language_override($installerLanguage);

$isDefaultInstall = $installerEnvironment->resetDefaultInstall($usersFile, $dataDir);

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

$installerEnvironment->assertRuntimeRequirements();

if ($is_cli) {
    echo "✓ PHP version and extensions OK\n";
}

$installerEnvironment->ensureDataDirectory($dataDir);

if ($is_cli) {
    echo "✓ Data directory created\n";
}

// Include dev mode detection
\DcsStats\Core\SupportBootstrap::devMode();
$isDev = isDevMode();

// For web installation, provide a form interface
if (!$is_cli) {
    $webFormState = $installerWebForm->submissionState($_POST, $isDev);
    $readyToInstall = $webFormState['ready'];
    $errors = $webFormState['errors'];
    $username = $webFormState['username'];
    $email = $webFormState['email'];
    $password = $webFormState['password'];
    $api_url = $webFormState['api_url'];
    $api_key = $webFormState['api_key'];
    $site_name = $webFormState['site_name'];
    $discord_url = $webFormState['discord_url'];
    $default_language = $webFormState['default_language'];
    $update_branch = $webFormState['update_branch'];
    
    // Show installation form
    if (!$readyToInstall) {
        $permissionState = $installerWebForm->permissionState();
        $permissionFolders = $permissionState['folders'];
        $permissionFiles = $permissionState['files'];
        $permissionFolderStatuses = $permissionState['folder_statuses'];
        $permissionFileStatuses = $permissionState['file_statuses'];
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
$admin = $installerConfigFactory->adminUser($username, $email, $password);

// Create data files
$files = $installerConfigFactory->adminDataFiles($admin);

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

$installerEnvironment->ensureSecurityFile($dataDir);

if ($is_cli) {
    echo "✓ Security files created\n";
}

$installerEnvironment->assertWritable($dataDir);

if ($is_cli) {
    echo "✓ Write permissions OK\n";
}

// Create API configuration
$apiConfig = $installerConfigFactory->apiConfig((string)($api_url ?? ''), (string)($api_key ?? ''));

if (file_put_contents($apiConfigFile, json_encode($apiConfig, JSON_PRETTY_PRINT)) === false) {
    die("Error: Could not create api_config.json\n");
}

if ($is_cli) {
    echo "✓ API configuration created\n";
}

// Create site configuration
$siteConfig = $installerConfigFactory->siteConfig(
    $site_name ?? 'DCS Statistics',
    $default_language ?? 'en',
    $discord_url ?? ''
);

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

$installerSelfDeleteStatus = $installerEnvironment->removeInstallerFile();

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
