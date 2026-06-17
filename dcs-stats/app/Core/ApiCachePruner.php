<?php

namespace DcsStats\Core;

final class ApiCachePruner
{
    private ApiCachePolicy $policy;

    public function __construct(?ApiCachePolicy $policy = null)
    {
        $this->policy = $policy ?? new ApiCachePolicy();
    }

    public function prune(string $dir, array $config = []): int
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $markerFile = $dir . '/.last-prune';
        if (is_file($markerFile) && (time() - filemtime($markerFile)) < 7200) {
            return 0;
        }
        @file_put_contents($markerFile, (string)time(), LOCK_EX);
        @chmod($markerFile, 0600);

        $files = glob($dir . '/*.json') ?: [];
        if (empty($files)) {
            return 0;
        }

        $maxAge = max($this->policy->ttl($config), 900) + 300;
        $now = time();
        $removed = 0;
        $remaining = [];

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $fileAge = $now - (int)filemtime($file);
            if ($fileAge > $maxAge) {
                if (@unlink($file)) {
                    $removed++;
                }
                continue;
            }

            $remaining[] = $file;
        }

        $maxFiles = $this->policy->maxFiles($config);
        if (count($remaining) > $maxFiles) {
            usort($remaining, function ($a, $b) {
                return filemtime($a) <=> filemtime($b);
            });

            $deleteCount = count($remaining) - $maxFiles;
            for ($i = 0; $i < $deleteCount; $i++) {
                if (@unlink($remaining[$i])) {
                    $removed++;
                }
            }
        }

        return $removed;
    }
}
