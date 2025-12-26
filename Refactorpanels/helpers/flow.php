<?php
// Flow control helpers (guarded).

if (function_exists('rf_set_module')) {
    rf_set_module('helpers/flow.php');
}

if (!function_exists('rf_stop')) {
    function rf_stop(string $reason = ''): void
    {
        if ($reason !== '' && function_exists('rf_log')) {
            rf_log('NOTICE', 'Stopped', ['reason' => $reason]);
        }
        exit;
    }
}
