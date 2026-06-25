<?php

namespace DcsStats\Core;

final class ApiConfigValidator
{
    public function validateAndFix($config): array
    {
        $default = ApiConfig::defaults();
        $fixed = false;

        if (!is_array($config)) {
            return ['config' => $default, 'fixed' => true, 'changes' => ['Replaced invalid config with default']];
        }

        $changes = [];

        if (empty($config['api_host']) && !empty($config['api_base_url'])) {
            $config['api_host'] = preg_replace('#^https?://#', '', $config['api_base_url']);
            $changes[] = 'Extracted api_host from api_base_url';
            $fixed = true;
        }

        if (!empty($config['api_host']) && empty($config['api_base_url'])) {
            $config['api_base_url'] = 'https://' . $config['api_host'];
            $changes[] = 'Generated api_base_url from api_host';
            $fixed = true;
        }

        $requiredFields = ['timeout', 'cache_ttl', 'refresh_interval', 'use_api', 'verify_ssl'];
        foreach ($requiredFields as $field) {
            if (!isset($config[$field])) {
                $config[$field] = $default[$field];
                $changes[] = "Added missing field: $field";
                $fixed = true;
            }
        }

        if (isset($config['fallback_to_json'])) {
            unset($config['fallback_to_json']);
            $changes[] = 'Removed deprecated fallback_to_json setting';
            $fixed = true;
        }

        if (!isset($config['enabled_endpoints']) || !is_array($config['enabled_endpoints'])) {
            $config['enabled_endpoints'] = $default['enabled_endpoints'];
            $changes[] = 'Added all enabled endpoints';
            $fixed = true;
        } else {
            $missingEndpoints = array_diff($default['enabled_endpoints'], $config['enabled_endpoints']);
            if (!empty($missingEndpoints)) {
                $config['enabled_endpoints'] = array_unique(array_merge($config['enabled_endpoints'], $missingEndpoints));
                $changes[] = 'Added missing endpoints: ' . implode(', ', $missingEndpoints);
                $fixed = true;
            }
        }

        if (!isset($config['endpoints']) || !is_array($config['endpoints'])) {
            $config['endpoints'] = $default['endpoints'];
            $changes[] = 'Added endpoint mappings';
            $fixed = true;
        } else {
            foreach ($default['endpoints'] as $key => $value) {
                if (!isset($config['endpoints'][$key])) {
                    $config['endpoints'][$key] = $value;
                    $changes[] = "Added missing endpoint mapping: $key";
                    $fixed = true;
                }
            }
        }

        if (!is_int($config['timeout']) || $config['timeout'] < 1) {
            $config['timeout'] = 30;
            $changes[] = 'Fixed invalid timeout value';
            $fixed = true;
        }

        if (!is_int($config['cache_ttl']) || $config['cache_ttl'] < 0) {
            $config['cache_ttl'] = 300;
            $changes[] = 'Fixed invalid cache_ttl value';
            $fixed = true;
        }

        if (!is_int($config['refresh_interval']) || $config['refresh_interval'] < 60) {
            $config['refresh_interval'] = 300;
            $changes[] = 'Fixed invalid refresh_interval value';
            $fixed = true;
        }

        $config['verify_ssl'] = filter_var($config['verify_ssl'], FILTER_VALIDATE_BOOLEAN);

        if ($config['use_api'] !== true) {
            $config['use_api'] = true;
            $changes[] = 'Set use_api to true (API-only mode)';
            $fixed = true;
        }

        return [
            'config' => $config,
            'fixed' => $fixed,
            'changes' => $changes,
        ];
    }
}
