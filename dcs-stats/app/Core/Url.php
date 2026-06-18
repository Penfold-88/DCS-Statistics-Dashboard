<?php

namespace DcsStats\Core;

final class Url
{
    public static function basePath(): string
    {
        return (new RequestUrlResolver())->basePath();
    }

    public static function baseUrl(): string
    {
        return (new RequestUrlResolver())->baseUrl();
    }

    public static function to(string $path = ''): string
    {
        return (new RequestUrlResolver())->to($path);
    }

    public static function asset(string $path = ''): string
    {
        return (new AssetUrlBuilder())->build($path);
    }

    public static function isDemoMode(): bool
    {
        return DemoMode::isEnabled();
    }

    public static function absolute(string $path = ''): string
    {
        return (new RequestUrlResolver())->absolute($path);
    }

    public static function jsConfig(): string
    {
        return (new RequestUrlResolver())->jsConfig();
    }
}
