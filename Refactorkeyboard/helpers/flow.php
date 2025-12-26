<?php
// Flow helpers (guarded).

if (!function_exists('rf_stop')) {
    function rf_stop(string $reason = ''): void
    {
        if ($reason !== '' && function_exists('rf_log')) {
            rf_log('NOTICE', 'Stopped', ['reason' => $reason]);
        }
        exit;
    }
}
