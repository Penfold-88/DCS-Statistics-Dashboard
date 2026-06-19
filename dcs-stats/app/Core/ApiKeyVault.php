<?php

namespace DcsStats\Core;

final class ApiKeyVault
{
    private const CIPHER = 'aes-256-gcm';
    private const AAD = 'dcs-statistics-dashboard-api-key-v1';
    private const ENV_KEY = 'DCS_CONFIG_ENCRYPTION_KEY';

    private ?string $keyFile;

    public function __construct(?string $keyFile = null)
    {
        $this->keyFile = $keyFile;
    }

    public function hydrate(array $config, string $configFile): array
    {
        if (!empty($config['api_key_encrypted']) && is_string($config['api_key_encrypted'])) {
            $config['api_key'] = $this->decrypt($config['api_key_encrypted'], $configFile);
        }

        return $config;
    }

    public function protect(array $config, string $configFile): array
    {
        $environmentOverride = !empty($config['api_key_env_override']);
        $apiKey = $environmentOverride ? null : ($config['api_key'] ?? null);

        if (is_string($apiKey) && $apiKey !== '') {
            $config['api_key_encrypted'] = $this->encrypt($apiKey, $configFile);
        } elseif (!$environmentOverride && empty($config['api_key_encrypted'])) {
            unset($config['api_key_encrypted']);
        }

        unset(
            $config['api_key'],
            $config['api_key_source'],
            $config['api_key_env_override'],
            $config['stored_api_key_present']
        );

        return $config;
    }

    public function containsPlaintextKey(array $config): bool
    {
        return isset($config['api_key']) && is_string($config['api_key']) && $config['api_key'] !== '';
    }

    private function encrypt(string $plaintext, string $configFile): string
    {
        $key = $this->environmentKey() ?? $this->localKey($configFile, true);
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            self::AAD,
            16
        );
        if ($ciphertext === false) {
            throw new \RuntimeException('Could not encrypt the stored API key.');
        }

        return base64_encode($iv . $tag . $ciphertext);
    }

    private function decrypt(string $payload, string $configFile): ?string
    {
        $decoded = base64_decode($payload, true);
        if ($decoded === false || strlen($decoded) < 29) {
            return null;
        }

        $keys = array_filter([
            $this->environmentKey(),
            $this->localKey($configFile, false),
        ]);
        foreach (array_unique($keys, SORT_STRING) as $key) {
            $plaintext = openssl_decrypt(
                substr($decoded, 28),
                self::CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                substr($decoded, 0, 12),
                substr($decoded, 12, 16),
                self::AAD
            );
            if ($plaintext !== false) {
                return $plaintext;
            }
        }

        return null;
    }

    private function environmentKey(): ?string
    {
        $environmentKey = getenv(self::ENV_KEY);
        if ($environmentKey !== false && trim((string)$environmentKey) !== '') {
            return hash('sha256', (string)$environmentKey, true);
        }

        return null;
    }

    private function localKey(string $configFile, bool $create): ?string
    {
        $keyFile = $this->keyFile ?? $this->defaultKeyFile($configFile);
        if (is_file($keyFile)) {
            $stored = trim((string)file_get_contents($keyFile));
            $decoded = base64_decode($stored, true);
            return is_string($decoded) && strlen($decoded) === 32 ? $decoded : null;
        }

        if (!$create) {
            return null;
        }

        $directory = dirname($keyFile);
        if (!is_dir($directory) && !mkdir($directory, 0700, true)) {
            throw new \RuntimeException('Could not create the API-key encryption directory.');
        }
        chmod($directory, 0700);

        $handle = fopen($keyFile, 'c+');
        if ($handle === false || !flock($handle, LOCK_EX)) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            throw new \RuntimeException('Could not initialize API-key encryption.');
        }

        rewind($handle);
        $stored = trim((string)stream_get_contents($handle));
        $decoded = base64_decode($stored, true);
        if (is_string($decoded) && strlen($decoded) === 32) {
            flock($handle, LOCK_UN);
            fclose($handle);
            return $decoded;
        }

        $key = random_bytes(32);
        rewind($handle);
        if (!ftruncate($handle, 0) || fwrite($handle, base64_encode($key)) === false || !fflush($handle)) {
            flock($handle, LOCK_UN);
            fclose($handle);
            throw new \RuntimeException('Could not write the API-key encryption key.');
        }
        chmod($keyFile, 0600);
        flock($handle, LOCK_UN);
        fclose($handle);
        return $key;
    }

    private function defaultKeyFile(string $configFile): string
    {
        if (defined('DCS_ROOT_PATH')) {
            return DCS_ROOT_PATH . '/site-config/data/.api_key.key';
        }

        return dirname($configFile) . '/.api_key.key';
    }
}
