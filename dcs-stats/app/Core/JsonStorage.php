<?php

namespace DcsStats\Core;

final class JsonStorage
{
    private string $path;
    private array $defaultValue;

    public function __construct(string $path, array $defaultValue = [])
    {
        $this->path = $path;
        $this->defaultValue = $defaultValue;
    }

    public function read(): array
    {
        if (!is_file($this->path)) {
            return $this->defaultValue;
        }

        $contents = file_get_contents($this->path);
        if ($contents === false || trim($contents) === '') {
            return $this->defaultValue;
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : $this->defaultValue;
    }

    public function write(array $data): bool
    {
        $directory = dirname($this->path);
        if (!is_dir($directory)) {
            @mkdir($directory, 0700, true);
        }

        if (!is_dir($directory) || !is_writable($directory)) {
            return false;
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return false;
        }

        $result = file_put_contents($this->path, $json . PHP_EOL, LOCK_EX);
        if ($result === false) {
            return false;
        }

        @chmod($this->path, 0600);

        return true;
    }
}

