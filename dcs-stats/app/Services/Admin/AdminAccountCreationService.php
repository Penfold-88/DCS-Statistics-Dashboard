<?php

namespace DcsStats\Services\Admin;

final class AdminAccountCreationService
{
    private AdminAccountRepository $accounts;
    private AdminAccountInputValidator $validator;
    private AdminUserIdGenerator $idGenerator;

    public function __construct(
        AdminAccountRepository $accounts,
        AdminAccountInputValidator $validator,
        AdminUserIdGenerator $idGenerator
    ) {
        $this->accounts = $accounts;
        $this->validator = $validator;
        $this->idGenerator = $idGenerator;
    }

    public function add(array $post, int $currentAdminId): array
    {
        $username = trim($post['username'] ?? '');
        $email = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';
        $role = intval($post['role'] ?? ROLE_LSO);

        $validation = $this->validator->validateCreate($username, $email, $password);
        if ($validation !== null) {
            return $validation;
        }

        $users = $this->accounts->all();
        foreach ($users as $user) {
            if ($user['username'] === $username || $user['email'] === $email) {
                return [\dcs_t('admin.admins.error_exists'), 'error'];
            }
        }

        $users[] = [
            'id' => $this->idGenerator->generate($users),
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role' => $role,
            'created_at' => date(DATE_FORMAT),
            'last_login' => null,
            'is_active' => true,
            'failed_attempts' => 0,
            'locked_until' => null,
        ];

        $this->accounts->save($users);
        $this->accounts->log('ADMIN_CREATE', $currentAdminId, $username);

        return [SUCCESS_MESSAGES['admin_created'], 'success'];
    }
}
