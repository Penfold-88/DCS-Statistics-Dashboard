<?php

namespace DcsStats\Services;

final class InstallCheckinConfiguration
{
    private string $endpoint;
    private string $token;
    private bool $allowSslFallback;

    public function __construct(?string $endpoint = null, ?string $token = null, ?bool $allowSslFallback = null)
    {
        $this->endpoint = $endpoint ?? (defined('DCS_STATS_INSTALL_CHECKIN_ENDPOINT')
            ? trim((string)DCS_STATS_INSTALL_CHECKIN_ENDPOINT)
            : '');
        $this->token = $token ?? (defined('DCS_STATS_INSTALL_CHECKIN_TOKEN')
            ? trim((string)DCS_STATS_INSTALL_CHECKIN_TOKEN)
            : '');
        $this->allowSslFallback = $allowSslFallback ?? (defined('DCS_STATS_INSTALL_CHECKIN_ALLOW_SSL_FALLBACK')
            ? (bool)DCS_STATS_INSTALL_CHECKIN_ALLOW_SSL_FALLBACK
            : false);
    }

    public function endpoint(): string
    {
        return trim($this->endpoint);
    }

    public function token(): string
    {
        return trim($this->token);
    }

    public function allowSslFallback(): bool
    {
        return $this->allowSslFallback;
    }
}
