<?php

namespace DcsStats\Services\Admin;

final class SensitiveDataRedactor
{
    private const SENSITIVE_KEYS = [
        'api_key',
        'password',
        'password_hash',
        'token',
        'token_hash',
        'csrf_token',
        'session_id',
        'secret',
    ];

    public function redact($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $redacted = [];
        foreach ($value as $key => $item) {
            $keyString = strtolower((string)$key);
            $isSensitive = false;
            foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
                if ($keyString === $sensitiveKey || strpos($keyString, $sensitiveKey) !== false) {
                    $isSensitive = true;
                    break;
                }
            }

            $redacted[$key] = $isSensitive ? '[REDACTED]' : $this->redact($item);
        }

        return $redacted;
    }
}
