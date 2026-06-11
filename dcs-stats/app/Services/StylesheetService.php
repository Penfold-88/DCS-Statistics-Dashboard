<?php

namespace DcsStats\Services;

final class StylesheetService
{
    public function render(): string
    {
        require_once DCS_ROOT_PATH . '/config_path.php';

        $cssPath = DCS_ROOT_PATH . '/styles.css';
        $css = is_file($cssPath) ? file_get_contents($cssPath) : '';

        if ($css === false || $css === '') {
            return '';
        }

        return preg_replace_callback('/url\([\'"]?([^\'")]+)[\'"]?\)/', function (array $matches): string {
            $path = $matches[1];

            if (preg_match('/^(https?:|data:)/', $path)) {
                return $matches[0];
            }

            return 'url(\'' . url($path) . '\')';
        }, $css) ?? $css;
    }
}
