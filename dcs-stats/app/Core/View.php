<?php

namespace DcsStats\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $viewPath = DCS_APP_PATH . '/Views/' . ltrim($view, '/');

        if (!is_file($viewPath)) {
            throw new \RuntimeException('View not found: ' . $view);
        }

        extract($data, EXTR_SKIP);
        require $viewPath;
    }
}

