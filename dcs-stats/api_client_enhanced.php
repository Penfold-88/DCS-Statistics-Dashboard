<?php
/**
 * Enhanced DCSServerBot REST API Client
 * 
 * Features:
 * - Auto-detection of HTTP/HTTPS
 * - Support for domain:port format
 * - Fallback mechanisms for blocked connections
 */

require_once __DIR__ . '/api_client.php';

class EnhancedDCSServerBotAPIClient extends DCSServerBotAPIClient {
    private $apiHost;
    private $detectedProtocol;
    private $protocolTested = false;
    private $verifySsl = true;
    
    public function __construct($config = []) {
        // Extract host without protocol
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

        // Don't set base URL yet - we'll detect it
        $config['api_base_url'] = 'http://' . $this->apiHost; // Default to HTTP
        parent::__construct($config);
        
        // Now detect the correct protocol
        $this->detectProtocol();
    }
    
    /**
     * Test which protocol works
     */
    private function detectProtocol() {
        if ($this->protocolTested) {
            return $this->detectedProtocol;
        }
        
        // Test both protocols
        $protocols = ['http', 'https']; // Start with HTTP since it's more common for local APIs
        
        foreach ($protocols as $protocol) {
            $testUrl = $protocol . '://' . $this->apiHost . '/servers';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $testUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
            curl_setopt($ch, CURLOPT_HEADER, false);
            
            if ($protocol === 'https') {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySsl);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySsl ? 2 : 0);
            }
            
            // Execute request
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            
            // Check if successful
            if ($httpCode > 0 && $httpCode < 500 && empty($error)) {
                $this->detectedProtocol = $protocol;
                $this->apiBaseUrl = $protocol . '://' . $this->apiHost;
                $this->protocolTested = true;
                return $protocol;
            }
        }
        
        // Default to HTTP if both fail
        $this->detectedProtocol = 'http';
        $this->apiBaseUrl = 'http://' . $this->apiHost;
        $this->protocolTested = true;
        return 'http';
    }
    
    /**
     * Public method to request with endpoint
     */
    public function request($endpoint, $data = null, $method = 'POST') {
        return $this->makeRequest($method, $endpoint, $data);
    }
    
    /**
     * Override makeRequest to ensure correct protocol
     */
    public function makeRequest($method, $endpoint, $data = null) {
        // Make sure we have the right protocol
        if (!$this->protocolTested) {
            $this->detectProtocol();
        }
        
        // Update base URL to use detected protocol
        $this->apiBaseUrl = $this->detectedProtocol . '://' . $this->apiHost;
        
        try {
            return parent::makeRequest($method, $endpoint, $data);
        } catch (Exception $e) {
            // If we get an SSL error and we're using HTTPS, retry with HTTP
            $errorMsg = $e->getMessage();
            if ($this->detectedProtocol === 'https' && 
                (strpos($errorMsg, 'SSL') !== false || 
                 strpos($errorMsg, 'wrong version number') !== false ||
                 strpos($errorMsg, 'OpenSSL') !== false)) {
                
                // Force HTTP
                $this->detectedProtocol = 'http';
                $this->apiBaseUrl = 'http://' . $this->apiHost;
                $this->protocolTested = true;
                
                // Retry with HTTP
                return parent::makeRequest($method, $endpoint, $data);
            }
            
            throw $e;
        }
    }
    
    /**
     * Get the detected protocol
     */
    public function getDetectedProtocol() {
        if (!$this->protocolTested) {
            $this->detectProtocol();
        }
        return $this->detectedProtocol;
    }
    
    /**
     * Get the current API base URL
     */
    public function getApiBaseUrl() {
        if (!$this->protocolTested) {
            $this->detectProtocol();
        }
        return $this->apiBaseUrl;
    }
}

// Include the config helper
require_once __DIR__ . '/api_config_helper.php';

// Create enhanced API client factory
function createEnhancedAPIClient() {
    // Use helper to load and auto-fix config
    $result = loadApiConfigWithFix();
    $config = $result['config'];
    
    return new EnhancedDCSServerBotAPIClient($config);
}

// Don't create global instance here to avoid conflicts
?>
