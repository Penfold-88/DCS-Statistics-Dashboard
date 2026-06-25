<?php

namespace DcsStats\Services\Admin;

final class AdminAccountInputValidator
{
    public function validateCreate(string $username, string $email, string $password): ?array
    {
        if ($username === '' || $email === '' || $password === '') {
            return [\dcs_t('admin.admins.error_all_fields'), 'error'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [\dcs_t('admin.admins.error_invalid_email'), 'error'];
        }
        if (strlen($password) < 8) {
            return [\dcs_t('admin.admins.error_password_length'), 'error'];
        }

        return null;
    }

    public function validatePassword(string $password): ?array
    {
        return strlen($password) < 8
            ? [\dcs_t('admin.admins.error_password_length'), 'error']
            : null;
    }
}
