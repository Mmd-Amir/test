<?php
// Refactor error handlers: log warnings/notices + fatal (shutdown) + uncaught exceptions.
// Installed only once per request.

if (function_exists('rf_set_module')) {
    rf_set_module('helpers/error_handler.php');
}

if (!defined('RF_ERROR_HANDLER_INSTALLED')) {
    define('RF_ERROR_HANDLER_INSTALLED', true);

    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');

    set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
        // Respect @ operator
        if (error_reporting() === 0) {
            return false;
        }

        if (function_exists('rf_log')) {
            rf_log('WARNING', 'PHP error', [
                'errno' => $errno,
                'message' => $errstr,
                'file' => $errfile,
                'line' => $errline,
            ]);
        }

        // Let PHP continue normal handling too.
        return false;
    });

    set_exception_handler(function (Throwable $e): void {
        if (function_exists('rf_log_exception')) {
            rf_log_exception($e, 'Uncaught exception');
        } else {
            error_log('Uncaught exception: ' . $e->getMessage());
        }
        http_response_code(500);
    });

    register_shutdown_function(function (): void {
        $err = error_get_last();
        if (!$err) {
            return;
        }
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (!in_array($err['type'] ?? 0, $fatalTypes, true)) {
            return;
        }

        if (function_exists('rf_log')) {
            rf_log('ERROR', 'Fatal error (shutdown)', $err);
        } else {
            error_log('Fatal error: ' . json_encode($err));
        }
    });
}
