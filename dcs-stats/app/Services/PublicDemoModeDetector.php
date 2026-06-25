<?php

namespace DcsStats\Services;

final class PublicDemoModeDetector
{
    public function isEnabled(): bool
    {
        return (function_exists('isDemoMode') && \isDemoMode())
            || file_exists(DCS_ROOT_PATH . '/.demo')
            || file_exists(DCS_ROOT_PATH . '/site-config/.demo')
            || file_exists(dirname(DCS_ROOT_PATH) . '/.demo');
    }
}
