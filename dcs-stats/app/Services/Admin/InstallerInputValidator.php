<?php

namespace DcsStats\Services\Admin;

final class InstallerInputValidator
{
    public function validate(array $input): array
    {
        $errors = [];
        if (trim((string)($input['username'] ?? '')) === '') {
            $errors[] = 'Username is required';
        }
        if (trim((string)($input['email'] ?? '')) === '') {
            $errors[] = 'Email is required';
        }
        if (strlen((string)($input['password'] ?? '')) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if (trim((string)($input['api_url'] ?? '')) === '') {
            $errors[] = 'API URL is required';
        }
        $apiKey = trim((string)($input['api_key'] ?? ''));
        if ($apiKey === '') {
            $errors[] = 'API key is required';
        } elseif (!$this->isValidApiKey($apiKey)) {
            $errors[] = 'API key contains invalid characters';
        }

        return $errors;
    }

    public function isValidApiKey($apiKey): bool
    {
        return $apiKey !== ''
            && strlen($apiKey) <= 256
            && preg_match('/^[A-Za-z0-9._~:+\/=-]+$/', $apiKey) === 1;
    }
}
