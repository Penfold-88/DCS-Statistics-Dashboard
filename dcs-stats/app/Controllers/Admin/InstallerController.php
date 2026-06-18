<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Core\AdminBootstrap;
use DcsStats\Core\SupportBootstrap;
use DcsStats\Services\Admin\InstallerCliInputService;
use DcsStats\Services\Admin\InstallerCompletionRenderer;
use DcsStats\Services\Admin\InstallerConfigFactory;
use DcsStats\Services\Admin\InstallerEnvironmentService;
use DcsStats\Services\Admin\InstallerFileWriter;
use DcsStats\Services\Admin\InstallerSupportService;
use DcsStats\Services\Admin\InstallerVersionInitializer;
use DcsStats\Services\Admin\InstallerWebFormService;

final class InstallerController
{
    private InstallerConfigFactory $configFactory;
    private InstallerEnvironmentService $environment;
    private InstallerWebFormService $webForm;
    private InstallerCliInputService $cliInput;
    private InstallerFileWriter $fileWriter;
    private InstallerVersionInitializer $versionInitializer;
    private InstallerCompletionRenderer $completionRenderer;

    public function __construct(
        ?InstallerSupportService $support = null,
        ?InstallerConfigFactory $configFactory = null,
        ?InstallerEnvironmentService $environment = null,
        ?InstallerWebFormService $webForm = null,
        ?InstallerCliInputService $cliInput = null,
        ?InstallerFileWriter $fileWriter = null,
        ?InstallerVersionInitializer $versionInitializer = null,
        ?InstallerCompletionRenderer $completionRenderer = null
    ) {
        $support = $support ?? new InstallerSupportService();
        $this->configFactory = $configFactory ?? new InstallerConfigFactory();
        $this->environment = $environment ?? new InstallerEnvironmentService();
        $this->webForm = $webForm ?? new InstallerWebFormService($support);
        $this->cliInput = $cliInput ?? new InstallerCliInputService($support);
        $this->fileWriter = $fileWriter ?? new InstallerFileWriter();
        $this->versionInitializer = $versionInitializer ?? new InstallerVersionInitializer();
        $this->completionRenderer = $completionRenderer ?? new InstallerCompletionRenderer();
    }

    public function handle(): void
    {
        $dataDir = DCS_ROOT_PATH . '/site-config/data';
        $usersFile = $dataDir . '/users.json';
        $apiConfigFile = $dataDir . '/api_config.json';
        $legacyApiConfigFile = DCS_ROOT_PATH . '/api_config.json';
        $siteConfigFile = DCS_ROOT_PATH . '/site_config.json';
        $isCli = php_sapi_name() === 'cli';

        SupportBootstrap::language();
        $installerLanguage = \dcs_language_code($_POST['install_language'] ?? $_GET['lang'] ?? 'en');
        \dcs_set_language_override($installerLanguage);

        $isDefaultInstall = $this->environment->resetDefaultInstall($usersFile, $dataDir);
        if (!$isDefaultInstall && file_exists($usersFile) && (file_exists($apiConfigFile) || file_exists($legacyApiConfigFile))) {
            if ($isCli) {
                die("System appears to be already installed. Delete site-config/data/users.json and api_config.json to reinstall.\n");
            }

            AdminBootstrap::auth();
            \requireAdmin();
            $this->renderLockedPage();
            return;
        }

        if ($isCli) {
            echo "DCS Statistics Admin Panel Installer\n";
            echo "====================================\n\n";
        }

        $this->environment->assertRuntimeRequirements();
        $this->cliStatus($isCli, "✓ PHP version and extensions OK\n");

        $this->environment->ensureDataDirectory($dataDir);
        $this->cliStatus($isCli, "✓ Data directory created\n");

        SupportBootstrap::devMode();
        $isDev = \isDevMode();
        $input = $isCli
            ? $this->cliInput->collect($isDev)
            : $this->webInput($isDev, $dataDir, $installerLanguage);
        if ($input === null) {
            return;
        }

        $admin = $this->configFactory->adminUser($input['username'], $input['email'], $input['password']);
        $failedDataFile = $this->fileWriter->writeAdminDataFiles(
            $dataDir,
            $this->configFactory->adminDataFiles($admin)
        );
        if ($failedDataFile !== null) {
            die("Error: Could not create $failedDataFile\n");
        }
        $this->cliStatus($isCli, "✓ Data files created\n");

        $this->environment->ensureSecurityFile($dataDir);
        $this->cliStatus($isCli, "✓ Security files created\n");

        $this->environment->assertWritable($dataDir);
        $this->cliStatus($isCli, "✓ Write permissions OK\n");

        $apiConfig = $this->configFactory->apiConfig((string)$input['api_url'], (string)$input['api_key']);
        if (!$this->fileWriter->writeJson($apiConfigFile, $apiConfig)) {
            die("Error: Could not create api_config.json\n");
        }
        $this->cliStatus($isCli, "✓ API configuration created\n");

        $siteConfig = $this->configFactory->siteConfig(
            $input['site_name'] ?? 'DCS Statistics',
            $input['default_language'] ?? 'en',
            $input['discord_url'] ?? ''
        );
        if (!$this->fileWriter->writeJson($siteConfigFile, $siteConfig)) {
            die("Error: Could not create site_config.json\n");
        }
        $this->cliStatus($isCli, "✓ Site configuration created\n");

        $this->versionInitializer->initialize();
        $this->cliStatus($isCli, "✓ Version tracking initialized\n");

        $selfDeleteStatus = $this->environment->removeInstallerFile();
        $this->completionRenderer->render($isCli, $input, $selfDeleteStatus);
    }

    private function webInput(bool $isDev, string $dataDir, string $installerLanguage): ?array
    {
        $webFormState = $this->webForm->submissionState($_POST, $isDev);
        if ($webFormState['ready']) {
            return $webFormState;
        }

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

        $permissionState = $this->webForm->permissionState();
        $permissionFolders = $permissionState['folders'];
        $permissionFiles = $permissionState['files'];
        $permissionFolderStatuses = $permissionState['folder_statuses'];
        $permissionFileStatuses = $permissionState['file_statuses'];
        $showInstallerForm = ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !empty($errors);
        $required_extensions = ['json', 'session', 'openssl', 'mbstring'];

        require DCS_APP_PATH . '/Views/Admin/installer/form.php';
        return null;
    }

    private function renderLockedPage(): void
    {
        http_response_code(403);
        require DCS_APP_PATH . '/Views/Admin/installer/locked.php';
    }

    private function cliStatus(bool $isCli, string $message): void
    {
        if ($isCli) {
            echo $message;
        }
    }
}
