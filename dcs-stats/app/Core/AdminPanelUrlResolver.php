<?php

namespace DcsStats\Core;

final class AdminPanelUrlResolver
{
    public function resolve(string $path = ''): string
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $adminPos = strpos($scriptName, '/site-config');

        if ($adminPos !== false) {
            $basePath = substr($scriptName, 0, $adminPos + strlen('/site-config'));
        } else {
            $basePath = rtrim(dirname($scriptName), '/\\');
        }

        return rtrim($basePath, '/') . '/' . ltrim($path, '/');
    }
}
