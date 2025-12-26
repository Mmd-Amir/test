<?php
// Error/exception/shutdown handlers with logging.
// Safe to be loaded multiple times.

if (!function_exists('rf_install_error_handlers')) {
    function rf_install_error_handlers(): void
    {
        if (!empty($GLOBALS['RF_ERROR_HANDLERS_INSTALLED'])) {
            return;
        }
        $GLOBALS['RF_ERROR_HANDLERS_INSTALLED'] = true;

        set_error_handler(function ($severity, $message, $file, $line) {
            // Respect @ operator
            if (!(error_reporting() & $severity)) {
                return false;
            }
            if (function_exists('rf_log')) {
                rf_log('ERROR', $message, ['severity' => $severity, 'file' => $file, 'line' => $line]);
            } else {
                error_log($message . " in $file:$line");
            }
            // Let PHP continue its normal error handling too (useful during dev)
            return false;
        });

        set_exception_handler(function ($e) {
            if (function_exists('rf_log_exception')) {
                rf_log_exception($e, 'ERROR');
            } else {
                error_log($e->getMessage());
            }
            // Don't echo anything: webhook responses should stay clean.
        });

        register_shutdown_function(function () {
            $err = error_get_last();
            if (!$err) return;

            // Fatal types
            $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
            if (!in_array($err['type'], $fatalTypes, true)) return;

            if (function_exists('rf_log')) {
                rf_log('ERROR', 'Fatal error (shutdown)', $err);
            } else {
                error_log("Fatal error: {$err['message']} in {$err['file']}:{$err['line']}");
            }
        });
    }
}
