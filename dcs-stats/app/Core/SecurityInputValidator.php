<?php

namespace DcsStats\Core;

final class SecurityInputValidator
{
    public function jsonLine(string $line, array $requiredFields = []): ?array
    {
        if (trim($line) === '') {
            return null;
        }

        $data = json_decode(trim($line), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return null;
        }

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return null;
            }
        }

        return $data;
    }

    public function path(string $path, string $baseDir)
    {
        $realBase = realpath($baseDir);
        $realPath = realpath($path);

        if ($realBase === false || $realPath === false) {
            return false;
        }

        if (strpos($realPath, $realBase) !== 0) {
            return false;
        }

        return $realPath;
    }

    public function input($input, array $rules = [])
    {
        $input = trim((string)$input);

        if (isset($rules['max_length']) && strlen($input) > $rules['max_length']) {
            return false;
        }

        if (isset($rules['min_length']) && strlen($input) < $rules['min_length']) {
            return false;
        }

        if (isset($rules['pattern']) && !preg_match($rules['pattern'], $input)) {
            return false;
        }

        if (isset($rules['type'])) {
            switch ($rules['type']) {
                case 'player_name':
                    if (!preg_match('/^[a-zA-Z0-9_\-\s\.\[\]|]+$/u', $input)) {
                        return false;
                    }
                    break;

                case 'numeric':
                    if (!is_numeric($input)) {
                        return false;
                    }
                    break;
            }
        }

        return $input;
    }
}
