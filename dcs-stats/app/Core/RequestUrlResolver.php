<?php

namespace DcsStats\Core;

final class RequestUrlResolver
{
    public function basePath(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if ($scriptName === '') {
            return '';
        }

        $scriptPath = dirname($scriptName);
        if ($scriptPath === '/' || $scriptPath === '\\' || $scriptPath === '.') {
            return '';
        }

        return rtrim($scriptPath, '/\\');
    }

    public function baseUrl(): string
    {
        $serverPort = $_SERVER['SERVER_PORT'] ?? null;
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $serverPort == 443;
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return ($secure ? 'https://' : 'http://') . $host . $this->basePath();
    }

    public function to(string $path = ''): string
    {
        if ($path === '') {
            return BASE_PATH;
        }

        $path = ltrim($path, '/');
        return BASE_PATH === '' ? '/' . $path : BASE_PATH . '/' . $path;
    }

    public function absolute(string $path = ''): string
    {
        return $path === '' ? BASE_URL : BASE_URL . '/' . ltrim($path, '/');
    }

    public function jsConfig(): string
    {
        return json_encode(['basePath' => BASE_PATH, 'baseUrl' => BASE_URL]);
    }
}
