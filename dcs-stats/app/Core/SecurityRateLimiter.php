<?php

namespace DcsStats\Core;

final class SecurityRateLimiter
{
    public function check(int $limit, int $window): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $currentTime = time();
        $requests = $_SESSION['api_requests'] ?? [];
        $requests = array_filter($requests, function ($timestamp) use ($currentTime, $window) {
            return ($currentTime - $timestamp) < $window;
        });

        if (count($requests) >= $limit) {
            http_response_code(429);
            header('Retry-After: ' . $window);
            echo json_encode([
                'error' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $window,
            ]);
            return false;
        }

        $requests[] = $currentTime;
        $_SESSION['api_requests'] = $requests;

        return true;
    }
}
