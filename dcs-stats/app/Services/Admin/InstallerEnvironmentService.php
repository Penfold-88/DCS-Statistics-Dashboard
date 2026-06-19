<?php

namespace DcsStats\Services\Admin;

final class InstallerEnvironmentService
{
    public function resetDefaultInstall(string $usersFile, string $dataDir): bool
    {
        if (!file_exists($usersFile)) {
            return false;
        }

        $users = json_decode((string)file_get_contents($usersFile), true);
        if (
            !is_array($users) ||
            count($users) !== 1 ||
            ($users[0]['username'] ?? '') !== 'admin' ||
            ($users[0]['email'] ?? '') !== 'admin@example.com' ||
            !password_verify('', $users[0]['password_hash'] ?? '')
        ) {
            return false;
        }

        @unlink($usersFile);
        @unlink($dataDir . '/logs.json');
        @unlink($dataDir . '/bans.json');
        @unlink($dataDir . '/sessions.json');

        return true;
    }

    public function assertRuntimeRequirements(): void
    {
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            die("Error: PHP 7.4 or higher is required. You have " . PHP_VERSION . "\n");
        }

        $requiredExtensions = ['json', 'session', 'openssl', 'mbstring'];
        $missingExtensions = [];

        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }

        if (!empty($missingExtensions)) {
            die("Error: Missing required PHP extensions: " . implode(', ', $missingExtensions) . "\n");
        }
    }

    public function ensureDataDirectory(string $dataDir): void
    {
        if (!is_dir($dataDir) && !mkdir($dataDir, 0700, true)) {
            die("Error: Could not create data directory. Please create it manually with permissions 700.\n");
        }
    }

    public function ensureSecurityFile(string $dataDir): void
    {
        $htaccess = $dataDir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Order deny,allow\nDeny from all");
        }
    }

    public function assertWritable(string $dataDir): void
    {
        $testFile = $dataDir . '/test.tmp';
        if (file_put_contents($testFile, 'test') === false) {
            die("\nError: Data directory is not writable. Please check permissions.\n");
        }
        unlink($testFile);
    }

    public function removeInstallerFile(): string
    {
        $installerSelfDeletePath = realpath(DCS_ROOT_PATH . '/site-config/install.php');
        $installerDirPath = realpath(DCS_ROOT_PATH . '/site-config');

        if (
            $installerSelfDeletePath !== false &&
            $installerDirPath !== false &&
            $installerSelfDeletePath === $installerDirPath . DIRECTORY_SEPARATOR . 'install.php' &&
            is_file($installerSelfDeletePath)
        ) {
            if (@unlink($installerSelfDeletePath)) {
                return 'removed';
            }

            error_log('DCS Statistics installer warning: site-config/install.php could not be removed after installation.');
            return 'failed';
        }

        error_log('DCS Statistics installer warning: site-config/install.php self-delete path could not be verified.');
        return 'not_attempted';
    }
}
