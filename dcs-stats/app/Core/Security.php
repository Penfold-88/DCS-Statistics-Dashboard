<?php

namespace DcsStats\Core;

final class Security
{
    public static function checkRateLimit(int $limit = 60, int $window = 60): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $currentTime = time();
        $requests = $_SESSION['api_requests'] ?? [];
        $requests = array_filter($requests, function ($timestamp) use ($currentTime, $window) {
            return ($currentTime - $timestamp) < $window;
        });

        if (count($requests) >= $limit) {
            http_response_code(429);
            header('Retry-After: ' . $window);
            echo json_encode([
                'error' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $window,
            ]);
            return false;
        }

        $requests[] = $currentTime;
        $_SESSION['api_requests'] = $requests;

        return true;
    }

    public static function validateJsonLine(string $line, array $requiredFields = []): ?array
    {
        if (trim($line) === '') {
            return null;
        }

        $data = json_decode(trim($line), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return null;
        }

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return null;
            }
        }

        return $data;
    }

    public static function validatePath(string $path, string $baseDir)
    {
        $realBase = realpath($baseDir);
        $realPath = realpath($path);

        if ($realBase === false || $realPath === false) {
            return false;
        }

        if (strpos($realPath, $realBase) !== 0) {
            return false;
        }

        return $realPath;
    }

    public static function validateInput($input, array $rules = [])
    {
        $input = trim((string)$input);

        if (isset($rules['max_length']) && strlen($input) > $rules['max_length']) {
            return false;
        }

        if (isset($rules['min_length']) && strlen($input) < $rules['min_length']) {
            return false;
        }

        if (isset($rules['pattern']) && !preg_match($rules['pattern'], $input)) {
            return false;
        }

        if (isset($rules['type'])) {
            switch ($rules['type']) {
                case 'player_name':
                    if (!preg_match('/^[a-zA-Z0-9_\-\s\.\[\]|]+$/u', $input)) {
                        return false;
                    }
                    break;

                case 'numeric':
                    if (!is_numeric($input)) {
                        return false;
                    }
                    break;
            }
        }

        return $input;
    }

    public static function logEvent(string $event, string $details, ?string $ip = null): void
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

        $logPath = self::logPath();
        self::rotateLog($logPath);
        error_log($logEntry, 3, $logPath);
        @chmod($logPath, 0600);
    }

    public static function logPath(): string
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

    public static function rotateLog(string $logPath): void
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
