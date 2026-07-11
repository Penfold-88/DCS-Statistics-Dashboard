<?php

namespace DcsStats\Core;

final class AdminRememberMeTokenService
{
    public function create(): array
    {
        $token = bin2hex(random_bytes(32));
        return ['token' => $token, 'hash' => hash('sha256', $token)];
    }

    public function parse(?string $cookieValue): ?array
    {
        if ($cookieValue === null) {
            return null;
        }

        $parts = explode(':', $cookieValue, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$userId, $token] = $parts;
        return [
            'user_id' => (int)$userId,
            'token' => $token,
            'hash' => hash('sha256', $token),
        ];
    }
}
