<?php

namespace DcsStats\Core;

final class AdminRememberMeSessionStore
{
    private ?string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path;
    }

    public function all(): array
    {
        $path = $this->path();
        if (!is_file($path)) {
            return [];
        }

        $content = file_get_contents($path);
        if ($content === false) {
            error_log('DCS Statistics could not read the remember-me session store.');
            return [];
        }

        $sessions = json_decode($content, true);
        return is_array($sessions) ? $sessions : [];
    }

    public function save(array $sessions): void
    {
        $path = $this->path();
        $json = json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || file_put_contents($path, $json, LOCK_EX) === false) {
            error_log('DCS Statistics could not write the remember-me session store.');
            return;
        }

        chmod($path, 0600);
    }

    private function path(): string
    {
        return $this->path ?? AdminEnvironment::dataFilePath('sessions');
    }
}
