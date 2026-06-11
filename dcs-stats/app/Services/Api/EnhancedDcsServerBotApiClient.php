<?php

namespace DcsStats\Services\Api;

final class EnhancedDcsServerBotApiClient extends \DCSServerBotAPIClient
{
    private string $apiHost;
    private ?string $detectedProtocol = null;
    private bool $protocolTested = false;
    private bool $verifySsl = true;

    public function __construct($config = [])
    {
        if (isset($config['api_host'])) {
            $this->apiHost = preg_replace('#^https?://#', '', $config['api_host']);
        } elseif (isset($config['api_base_url'])) {
            $this->apiHost = preg_replace('#^https?://#', '', $config['api_base_url']);
        } else {
            $this->apiHost = 'localhost:8080';
        }

        $this->verifySsl = isset($config['verify_ssl'])
            ? filter_var($config['verify_ssl'], FILTER_VALIDATE_BOOLEAN)
            : true;

        $config['api_base_url'] = 'http://' . $this->apiHost;
        parent::__construct($config);

        $this->detectProtocol();
    }

    private function detectProtocol(): string
    {
        if ($this->protocolTested) {
            return (string)$this->detectedProtocol;
        }

        foreach (['http', 'https'] as $protocol) {
            $testUrl = $protocol . '://' . $this->apiHost . '/servers';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $testUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_HEADER, false);

            if ($protocol === 'https') {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySsl);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySsl ? 2 : 0);
            }

            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode > 0 && $httpCode < 500 && empty($error)) {
                $this->detectedProtocol = $protocol;
                $this->apiBaseUrl = $protocol . '://' . $this->apiHost;
                $this->protocolTested = true;
                return $protocol;
            }
        }

        $this->detectedProtocol = 'http';
        $this->apiBaseUrl = 'http://' . $this->apiHost;
        $this->protocolTested = true;

        return 'http';
    }

    public function request($endpoint, $data = null, $method = 'POST')
    {
        return $this->makeRequest($method, $endpoint, $data);
    }

    public function makeRequest($method, $endpoint, $data = null)
    {
        if (!$this->protocolTested) {
            $this->detectProtocol();
        }

        $this->apiBaseUrl = $this->detectedProtocol . '://' . $this->apiHost;

        try {
            return parent::makeRequest($method, $endpoint, $data);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            if (
                $this->detectedProtocol === 'https' &&
                (
                    strpos($errorMsg, 'SSL') !== false ||
                    strpos($errorMsg, 'wrong version number') !== false ||
                    strpos($errorMsg, 'OpenSSL') !== false
                )
            ) {
                $this->detectedProtocol = 'http';
                $this->apiBaseUrl = 'http://' . $this->apiHost;
                $this->protocolTested = true;

                return parent::makeRequest($method, $endpoint, $data);
            }

            throw $e;
        }
    }

    public function getDetectedProtocol(): string
    {
        if (!$this->protocolTested) {
            $this->detectProtocol();
        }

        return (string)$this->detectedProtocol;
    }

    public function getApiBaseUrl(): string
    {
        if (!$this->protocolTested) {
            $this->detectProtocol();
        }

        return $this->apiBaseUrl;
    }
}
