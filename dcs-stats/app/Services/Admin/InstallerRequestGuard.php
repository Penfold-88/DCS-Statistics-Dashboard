<?php

namespace DcsStats\Services\Admin;

use DcsStats\Core\Csrf;

final class InstallerRequestGuard
{
    private $lockHandle;

    public function validationErrors(array $post): array
    {
        $errors = [];
        if (!Csrf::verify((string)($post['csrf_token'] ?? ''))) {
            $errors[] = 'The installer security token expired. Refresh the page and try again.';
        }

        $requiredToken = trim((string)getenv('DCS_INSTALL_TOKEN'));
        $providedToken = (string)($post['install_token'] ?? '');
        if ($requiredToken !== '' && ($providedToken === '' || !hash_equals($requiredToken, $providedToken))) {
            $errors[] = 'The installation token is invalid.';
        }

        $now = time();
        $attempts = array_values(array_filter($_SESSION['installer_attempts'] ?? [], static function ($timestamp) use ($now): bool {
            return is_int($timestamp) && ($now - $timestamp) < 900;
        }));
        if (count($attempts) >= 10) {
            http_response_code(429);
            $errors[] = 'Too many installation attempts. Try again in 15 minutes.';
        } else {
            $attempts[] = $now;
        }
        $_SESSION['installer_attempts'] = $attempts;

        return $errors;
    }

    public function acquire(string $dataDir): bool
    {
        $lockPath = rtrim($dataDir, '/\\') . '/.install.lock';
        $this->lockHandle = @fopen($lockPath, 'c');
        if ($this->lockHandle === false) {
            return false;
        }

        @chmod($lockPath, 0600);
        if (!flock($this->lockHandle, LOCK_EX | LOCK_NB)) {
            fclose($this->lockHandle);
            $this->lockHandle = null;
            return false;
        }

        return true;
    }

    public function release(): void
    {
        if (is_resource($this->lockHandle)) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
        }
        $this->lockHandle = null;
    }

    public function tokenRequired(): bool
    {
        return trim((string)getenv('DCS_INSTALL_TOKEN')) !== '';
    }
}
