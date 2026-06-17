<?php

namespace DcsStats\Services\Admin;

final class SystemUpdateDeploymentService
{
    private const PRESERVED_PATHS = [
        'site-config/data',
        'data',
        'backups',
        'UPGRADE',
        'api_config.json',
        'site_config.json',
        '.version_meta.json',
        'custom_theme.css',
        'header_custom.css',
        'menu_config.json',
        'site-config/theme_backups',
        '.env',
        '.dev',
        'docker-compose.yml',
        'docker-compose.override.yml',
        'Dockerfile',
        'Dockerfile.simple',
        '.dockerignore',
        'docker/docker-compose.yml',
        'docker/docker-compose.override.yml',
        'docker/Dockerfile',
        'docker/Dockerfile.simple',
        'docker/Dockerfile.dockerignore',
        'uploads',
        'custom',
        'logs',
        '.git',
        '.gitignore',
        '.gitattributes',
    ];

    private AdminFilesystemService $filesystem;

    public function __construct(?AdminFilesystemService $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
    }

    public function applyNewCode(string $rootPath, string $newCodeDir, callable $log): void
    {
        $newFiles = [];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($newCodeDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relPath = $this->filesystem->normalizePath(substr($filePath, strlen($newCodeDir) + 1));
            $targetPath = $rootPath . '/' . $relPath;
            $newFiles[] = $relPath;

            if ($this->shouldPreserve($relPath)) {
                $log('Preserving: ' . $relPath);
                continue;
            }

            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                    $log('Created directory: ' . $relPath);
                }
                continue;
            }

            if (!is_dir(dirname($targetPath))) {
                mkdir(dirname($targetPath), 0755, true);
            }

            if (file_exists($targetPath) && in_array(basename($targetPath), ['api_config.json', 'site_config.json', '.env', 'users.json'])) {
                $log('Skipping config file: ' . $relPath);
                continue;
            }

            copy($filePath, $targetPath);
            $log('Updated file: ' . $relPath);
        }

        $this->removeOldFiles($rootPath, $newFiles, $log);
    }

    private function removeOldFiles(string $rootPath, array $newFiles, callable $log): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relPath = $this->filesystem->normalizePath(substr($filePath, strlen($rootPath) + 1));

            if ($this->shouldPreserve($relPath)) {
                continue;
            }

            if (!in_array($relPath, $newFiles)) {
                if ($file->isDir()) {
                    rmdir($filePath);
                    $log('Removed directory: ' . $relPath);
                } else {
                    unlink($filePath);
                    $log('Removed file: ' . $relPath);
                }
            }
        }
    }

    private function shouldPreserve(string $relPath): bool
    {
        return $this->filesystem->shouldPreservePath($relPath, self::PRESERVED_PATHS);
    }
}
