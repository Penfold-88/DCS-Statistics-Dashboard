<?php

namespace DcsStats\Services;

final class InstallCheckinTransport
{
    public function sendWithCurl(string $endpoint, string $json, array $headers, bool $verifySsl = true): array
    {
        $curl = curl_init($endpoint);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => 500,
            CURLOPT_TIMEOUT_MS => 750,
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
        ]);
        curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $errno = (int)curl_errno($curl);
        curl_close($curl);

        return [
            'success' => $status >= 200 && $status < 300,
            'status' => $status,
            'errno' => $errno,
        ];
    }

    public function sendPayload(string $endpoint, string $json, array $headers, bool $allowSslFallback): bool
    {
        if (function_exists('curl_init')) {
            $result = $this->sendWithCurl($endpoint, $json, $headers, true);
            if ($result['success']) {
                return true;
            }

            if ($result['errno'] === 60 && $allowSslFallback) {
                $fallbackResult = $this->sendWithCurl($endpoint, $json, $headers, false);
                return $fallbackResult['success'];
            }

            return false;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers) . "\r\n",
                'content' => $json,
                'timeout' => 0.75,
                'ignore_errors' => true,
            ],
        ]);

        $result = @file_get_contents($endpoint, false, $context);

        return $result !== false;
    }
}
