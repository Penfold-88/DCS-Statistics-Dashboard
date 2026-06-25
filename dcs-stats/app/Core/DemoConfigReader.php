<?php

namespace DcsStats\Core;

final class DemoConfigReader
{
    private array $paths;

    public function __construct(?array $paths = null)
    {
        $this->paths = $paths ?? [
            DCS_ROOT_PATH . '/.demo',
            dirname(DCS_ROOT_PATH) . '/.demo',
        ];
    }

    public function configPath(): string
    {
        foreach ($this->paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return '';
    }

    public function protectedUsername(): string
    {
        $path = $this->configPath();
        if ($path === '' || !is_readable($path)) {
            return '';
        }

        $content = trim((string)@file_get_contents($path));
        if ($content === '') {
            return '';
        }

        $json = json_decode($content, true);
        if (is_array($json)) {
            return trim((string)($json['protected_user'] ?? $json['owner'] ?? $json['username'] ?? ''));
        }

        foreach (preg_split('/\R/', $content) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$key, $value] = array_map('trim', explode('=', $line, 2));
                if (in_array(strtolower($key), ['protected_user', 'owner', 'username'], true)) {
                    return $value;
                }
                continue;
            }

            return $line;
        }

        return '';
    }
}
