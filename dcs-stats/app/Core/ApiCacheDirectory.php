<?php

namespace DcsStats\Core;

final class ApiCacheDirectory
{
    private string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? DCS_ROOT_PATH . '/site-config/data/api-cache';
    }

    public function path(): string
    {
        if (!is_dir($this->path)) {
            @mkdir($this->path, 0700, true);
        }
        if (is_dir($this->path)) {
            @chmod($this->path, 0700);
        }

        return $this->path;
    }

    public function file(string $key): string
    {
        return $this->path() . '/' . $key . '.json';
    }

    public function clear(): int
    {
        $removed = 0;
        foreach (glob($this->path() . '/*.json') ?: [] as $file) {
            if (is_file($file) && @unlink($file)) {
                $removed++;
            }
        }

        return $removed;
    }

    public function lastRefreshTimestamp(): ?int
    {
        $latest = null;
        foreach (glob($this->path() . '/*.json') ?: [] as $file) {
            if (!is_file($file)) {
                continue;
            }

            $timestamp = filemtime($file);
            if ($timestamp !== false && ($latest === null || $timestamp > $latest)) {
                $latest = $timestamp;
            }
        }

        return $latest;
    }
}
