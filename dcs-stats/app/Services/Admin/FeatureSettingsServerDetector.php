<?php

namespace DcsStats\Services\Admin;

final class FeatureSettingsServerDetector
{
    public function detectedServerCardFeatures(): array
    {
        try {
            $client = \createEnhancedAPIClient();
            $response = $client->request('/servers', null, 'GET');
            $servers = is_array($response) ? $response : [];
            $features = [];

            foreach ($servers as $index => $server) {
                if (!is_array($server)) {
                    continue;
                }

                $name = trim((string)($server['name'] ?? \dcs_t('admin.settings.server_number', ['number' => $index + 1])));
                if ($name === '') {
                    $name = \dcs_t('admin.settings.server_number', ['number' => $index + 1]);
                }

                $features[\serverCardFeatureKey($name)] = $name;
            }

            return $features;
        } catch (\Exception $e) {
            return [];
        }
    }
}
