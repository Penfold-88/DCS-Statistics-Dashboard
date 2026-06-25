<?php

namespace DcsStats\Services;

final class InstallCheckinSchedulePolicy
{
    public function skipStatus(array $state, string $payloadKey, string $today, bool $force, int $now): ?string
    {
        if (!$force && ($state['last_success_day'] ?? '') === $today && ($state['last_success_key'] ?? '') === $payloadKey) {
            return 'already_sent_today';
        }

        $lastAttemptTime = strtotime((string)($state['last_attempt_at'] ?? ''));
        if (!$force && $lastAttemptTime && $now - $lastAttemptTime < 1800) {
            return 'recent_attempt_wait';
        }

        return null;
    }
}
