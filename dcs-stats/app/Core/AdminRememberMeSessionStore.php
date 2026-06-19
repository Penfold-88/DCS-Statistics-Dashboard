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
        return json_decode((string)@file_get_contents($this->path()), true) ?: [];
    }

    public function save(array $sessions): void
    {
        @file_put_contents(
            $this->path(),
            json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
        @chmod($this->path(), 0600);
    }

    private function path(): string
    {
        return $this->path ?? AdminEnvironment::dataFilePath('sessions');
    }
}
