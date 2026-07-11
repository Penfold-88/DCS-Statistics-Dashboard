<?php

namespace DcsStats\Services\Api;

final class PublicSquadronStatsService
{
    public function getSquadrons(): array
    {
        \DcsStats\Core\SupportBootstrap::apiClient();
        \DcsStats\Core\SupportBootstrap::apiConfig();

        $response = ['data' => [], 'error' => null];

        try {
            $apiConfig = loadApiConfigWithFix()['config'];
            if (!$apiConfig || !$apiConfig['use_api']) {
                throw new \Exception('API not configured or disabled. Please check API settings.');
            }

            $client = createEnhancedAPIClient();
            $squadrons = $client->request('/squadrons', null, 'GET');

            if ($squadrons && is_array($squadrons)) {
                $response['data'] = array_map(function (array $squadron): array {
                    return [
                        'name' => $squadron['name'] ?? '',
                        'description' => $squadron['description'] ?? '',
                        'image_url' => $squadron['image_url'] ?? '',
                        'locked' => $squadron['locked'] ?? false,
                        'role' => $squadron['role'] ?? '',
                        'member_count' => 0,
                        'total_credits' => 0,
                    ];
                }, $squadrons);
            } else {
                $response['error'] = 'No squadrons data available';
            }
        } catch (\Exception $e) {
            $response['error'] = 'Failed to fetch squadrons: ' . $e->getMessage();
        }

        return $response;
    }

    public function getSquadronMembers(array $input): array
    {
        \DcsStats\Core\SupportBootstrap::apiClient();

        $response = ['data' => [], 'error' => null];

        try {
            $squadronName = $input['name'] ?? $_POST['name'] ?? '';
            if ($squadronName === '') {
                throw new \Exception('Squadron name is required');
            }

            $client = createEnhancedAPIClient();
            $members = $client->request('/squadron_members', ['name' => $squadronName]);

            if ($members) {
                $response['data'] = $members;
            } else {
                $response['error'] = 'No members data available for squadron: ' . $squadronName;
            }
        } catch (\Exception $e) {
            $response['error'] = 'Failed to fetch squadron members: ' . $e->getMessage();
        }

        return $response;
    }

    public function getSquadronCredits(array $input): array
    {
        \DcsStats\Core\SupportBootstrap::apiClient();

        $response = ['data' => [], 'error' => null];

        try {
            $squadronName = $input['name'] ?? $_POST['name'] ?? '';
            if ($squadronName === '') {
                throw new \Exception('Squadron name is required');
            }

            $client = createEnhancedAPIClient();
            $credits = $client->request('/squadron_credits', ['name' => $squadronName]);

            if ($credits) {
                $response['data'] = $credits;
            } else {
                $response['error'] = 'No credits data available for squadron: ' . $squadronName;
            }
        } catch (\Exception $e) {
            $response['error'] = 'Failed to fetch squadron credits: ' . $e->getMessage();
        }

        return $response;
    }
}
