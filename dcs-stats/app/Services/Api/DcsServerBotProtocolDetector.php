<?php

namespace DcsStats\Services\Api;

final class DcsServerBotProtocolDetector
{
    public function detect(string $apiHost, bool $verifySsl): string
    {
        foreach (['http', 'https'] as $protocol) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $protocol . '://' . $apiHost . '/servers');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_HEADER, false);

            if ($protocol === 'https') {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySsl ? 2 : 0);
            }

            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode > 0 && $httpCode < 500 && $error === '') {
                return $protocol;
            }
        }

        return 'http';
    }
}
