<?php

namespace DcsStats\Services\Api;

final class EnhancedDcsServerBotApiClient extends \DCSServerBotAPIClient
{
    private string $apiHost;
    private ?string $detectedProtocol = null;
    private bool $protocolTested = false;
    private bool $verifySsl = true;
    private DcsServerBotProtocolDetector $protocolDetector;

    public function __construct($config = [], ?DcsServerBotProtocolDetector $protocolDetector = null)
    {
        $this->protocolDetector = $protocolDetector ?? new DcsServerBotProtocolDetector();

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

        $this->detectedProtocol = $this->protocolDetector->detect($this->apiHost, $this->verifySsl);
        $this->apiBaseUrl = $this->detectedProtocol . '://' . $this->apiHost;
        $this->protocolTested = true;

        return $this->detectedProtocol;
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
