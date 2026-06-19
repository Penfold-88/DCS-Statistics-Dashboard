<?php

namespace DcsStats\Core;

final class AdminRememberMeService
{
    private AdminRememberMeSessionStore $sessions;
    private AdminRememberMeTokenService $tokens;
    private AdminRememberMeCookie $cookie;
    private \Closure $findUser;
    private \Closure $applyUser;

    public function __construct(
        ?AdminRememberMeSessionStore $sessions = null,
        ?AdminRememberMeTokenService $tokens = null,
        ?AdminRememberMeCookie $cookie = null,
        ?callable $findUser = null,
        ?callable $applyUser = null
    ) {
        $this->sessions = $sessions ?? new AdminRememberMeSessionStore();
        $this->tokens = $tokens ?? new AdminRememberMeTokenService();
        $this->cookie = $cookie ?? new AdminRememberMeCookie();
        $this->findUser = \Closure::fromCallable($findUser ?? static function (int $userId): ?array {
            foreach (AdminUsers::all() as $user) {
                if ((int)($user['id'] ?? 0) === $userId) {
                    return $user;
                }
            }
            return null;
        });
        $this->applyUser = \Closure::fromCallable($applyUser ?? static function (array $user): void {
            AdminSessionState::applyUser($user);
        });
    }

    public function createForUser(array $user): void
    {
        $token = $this->tokens->create();
        $sessions = $this->sessions->all();
        $sessions[] = [
            'id' => count($sessions) + 1,
            'admin_id' => $user['id'],
            'token_hash' => $token['hash'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'expires_at' => date(DATE_FORMAT, time() + ADMIN_COOKIE_LIFETIME),
            'created_at' => date(DATE_FORMAT),
        ];

        $this->sessions->save($sessions);
        $this->cookie->issue((int)$user['id'], $token['token']);
    }

    public function restoreFromCookie(?string $cookieValue): bool
    {
        if ($cookieValue === null) {
            return false;
        }

        $token = $this->tokens->parse($cookieValue);
        if ($token === null) {
            $this->cookie->clear();
            return false;
        }

        foreach ($this->sessions->all() as $session) {
            if (
                (int)($session['admin_id'] ?? 0) === $token['user_id']
                && ($session['token_hash'] ?? '') === $token['hash']
                && strtotime((string)($session['expires_at'] ?? '')) > time()
            ) {
                $user = ($this->findUser)($token['user_id']);
                if ($user && !empty($user['is_active'])) {
                    ($this->applyUser)($user);
                    return true;
                }
            }
        }

        $this->cookie->clear();
        return false;
    }

    public function clearCookie(): void
    {
        $this->cookie->clear();
    }

    public function removeCookieSession(string $cookieValue): void
    {
        $token = $this->tokens->parse($cookieValue);
        if ($token === null) {
            return;
        }

        $sessions = array_filter($this->sessions->all(), static function ($session) use ($token) {
            return !(
                (int)($session['admin_id'] ?? 0) === $token['user_id']
                && ($session['token_hash'] ?? '') === $token['hash']
            );
        });

        $this->sessions->save(array_values($sessions));
    }
}
