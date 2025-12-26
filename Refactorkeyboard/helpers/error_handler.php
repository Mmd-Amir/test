<?php
// Error/exception/shutdown handlers (guarded).

if (!function_exists('rf_error_handler')) {
    function rf_error_handler(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Respect @ operator
        if (!(error_reporting() & $errno)) {
            return false;
        }
        if (function_exists('rf_log')) {
            rf_log('ERROR', $errstr, ['type' => $errno, 'file' => $errfile, 'line' => $errline]);
        } else {
            error_log($errstr . " in $errfile:$errline");
        }
        // Returning false lets PHP internal handler run too; returning true suppresses it.
        return false;
    }
}

if (!function_exists('rf_exception_handler')) {
    function rf_exception_handler(Throwable $e): void
    {
        if (function_exists('rf_log_exception')) {
            rf_log_exception($e, 'ERROR');
        } else {
            error_log($e);
        }
    }
}

if (!function_exists('rf_shutdown_handler')) {
    function rf_shutdown_handler(): void
    {
        $err = error_get_last();
        if (!$err) return;

        // Fatal error types
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (in_array($err['type'] ?? 0, $fatalTypes, true)) {
            if (function_exists('rf_log')) {
                rf_log('ERROR', 'Fatal error (shutdown)', [
                    'type' => $err['type'] ?? null,
                    'message' => $err['message'] ?? null,
                    'file' => $err['file'] ?? null,
                    'line' => $err['line'] ?? null,
                ]);
            } else {
                error_log("Fatal: " . ($err['message'] ?? '') . " in " . ($err['file'] ?? '') . ":" . ($err['line'] ?? 0));
            }
        }
    }
}

if (!function_exists('rf_install_error_handlers')) {
    function rf_install_error_handlers(): void
    {
        if (!empty($GLOBALS['RF_ERROR_HANDLERS_INSTALLED'])) return;
        $GLOBALS['RF_ERROR_HANDLERS_INSTALLED'] = true;

        set_error_handler('rf_error_handler');
        set_exception_handler('rf_exception_handler');
        register_shutdown_function('rf_shutdown_handler');
    }
}
