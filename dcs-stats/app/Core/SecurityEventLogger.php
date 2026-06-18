<?php

namespace DcsStats\Core;

final class SecurityEventLogger
{
    public function log(string $event, string $details, ?string $ip = null): void
    {
        $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $timestamp = date('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $logEntry = sprintf(
            "[%s] %s - %s - IP: %s - UA: %s\n",
            $timestamp,
            $event,
            $details,
            $ip,
            substr($userAgent, 0, 100)
        );

        $logPath = $this->path();
        $this->rotate($logPath);
        error_log($logEntry, 3, $logPath);
        @chmod($logPath, 0600);
    }

    public function path(): string
    {
        $customPath = getenv('DCS_STATS_SECURITY_LOG');
        if ($customPath) {
            $dir = dirname($customPath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0700, true);
            }
            if (is_dir($dir) && is_writable($dir)) {
                return $customPath;
            }
        }

        $dataDir = DCS_ROOT_PATH . '/site-config/data';
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0700, true);
        }
        if (is_dir($dataDir) && is_writable($dataDir)) {
            return $dataDir . '/security.log';
        }

        $tempDir = sys_get_temp_dir() . '/dcs_stats';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0700, true);
        }

        return $tempDir . '/security.log';
    }

    public function rotate(string $logPath): void
    {
        $maxBytes = 1024 * 1024;
        if (!file_exists($logPath) || filesize($logPath) < $maxBytes) {
            return;
        }

        $rotatedPath = $logPath . '.1';
        if (file_exists($rotatedPath)) {
            @unlink($rotatedPath);
        }
        @rename($logPath, $rotatedPath);
        @chmod($rotatedPath, 0600);
    }
}
