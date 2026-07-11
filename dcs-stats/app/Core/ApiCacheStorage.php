<?php

namespace DcsStats\Core;

final class ApiCacheStorage
{
    private ApiCacheDirectory $directory;
    private ApiCacheKeyBuilder $keyBuilder;
    private ApiCachePolicy $policy;
    private ApiCachePruner $pruner;

    public function __construct(
        ?ApiCacheDirectory $directory = null,
        ?ApiCacheKeyBuilder $keyBuilder = null,
        ?ApiCachePolicy $policy = null,
        ?ApiCachePruner $pruner = null
    ) {
        $this->directory = $directory ?? new ApiCacheDirectory();
        $this->keyBuilder = $keyBuilder ?? new ApiCacheKeyBuilder();
        $this->policy = $policy ?? new ApiCachePolicy();
        $this->pruner = $pruner ?? new ApiCachePruner($this->policy);
    }

    public function read(string $method, string $baseUrl, string $endpoint, $data, array $config): ?array
    {
        if (!$this->policy->isCacheable($method, $endpoint, $config)) {
            return null;
        }

        $this->pruner->prune($this->directory->path(), $config);
        $file = $this->file($method, $baseUrl, $endpoint, $data);
        if (!is_file($file) || (time() - filemtime($file)) > $this->policy->ttlForEndpoint($endpoint, $config)) {
            return null;
        }

        $cached = json_decode((string)@file_get_contents($file), true);
        return is_array($cached) && array_key_exists('body', $cached) ? $cached : null;
    }

    public function write(
        string $method,
        string $baseUrl,
        string $endpoint,
        $data,
        array $config,
        string $body,
        int $httpCode = 200
    ): bool {
        if (!$this->policy->isCacheable($method, $endpoint, $config) || $httpCode < 200 || $httpCode >= 300) {
            return false;
        }

        $payload = [
            'created_at' => time(),
            'http_code' => $httpCode,
            'body' => $body,
        ];
        $saved = @file_put_contents(
            $this->file($method, $baseUrl, $endpoint, $data),
            json_encode($payload),
            LOCK_EX
        );
        if ($saved === false) {
            return false;
        }

        @chmod($this->file($method, $baseUrl, $endpoint, $data), 0600);
        $this->pruner->prune($this->directory->path(), $config);

        return true;
    }

    private function file(string $method, string $baseUrl, string $endpoint, $data): string
    {
        return $this->directory->file($this->keyBuilder->build($method, $baseUrl, $endpoint, $data));
    }
}
