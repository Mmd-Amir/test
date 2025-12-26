<?php
// Flow control helpers for refactored, modular index.php
//
// NOTE:
// Both Refactorindex and Refactoradmin may be loaded in the same request.
// Guard against function redeclare.

if (function_exists('rf_set_module')) {
    rf_set_module('helpers/flow.php');
}

/**
 * Stops execution immediately (replacement for `return;` in the original single-file script).
 * Keep it silent (no log) unless you pass a reason.
 */
if (!function_exists('rf_stop')) {
    function rf_stop(string $reason = ''): void
    {
        if ($reason !== '' && function_exists('rf_log')) {
            rf_log('NOTICE', 'Stopped', ['reason' => $reason]);
        }
        exit;
    }
}
