<?php

namespace DcsStats\Core;

final class Security
{
    public static function checkRateLimit(int $limit = 60, int $window = 60): bool
    {
        return (new SecurityRateLimiter())->check($limit, $window);
    }

    public static function validateJsonLine(string $line, array $requiredFields = []): ?array
    {
        return (new SecurityInputValidator())->jsonLine($line, $requiredFields);
    }

    public static function validatePath(string $path, string $baseDir)
    {
        return (new SecurityInputValidator())->path($path, $baseDir);
    }

    public static function validateInput($input, array $rules = [])
    {
        return (new SecurityInputValidator())->input($input, $rules);
    }

    public static function logEvent(string $event, string $details, ?string $ip = null): void
    {
        (new SecurityEventLogger())->log($event, $details, $ip);
    }

    public static function logPath(): string
    {
        return (new SecurityEventLogger())->path();
    }

    public static function rotateLog(string $logPath): void
    {
        (new SecurityEventLogger())->rotate($logPath);
    }
}
