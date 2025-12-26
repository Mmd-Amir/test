<?php
// Refactor error handlers: log every warning/notice + fatal + uncaught exceptions.
rf_set_module('helpers/error_handler.php');

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    // Respect @ operator
    if (error_reporting() === 0) {
        return false;
    }

    rf_log('WARNING', 'PHP error', [
        'errno' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
    ]);

    // Let PHP continue normal handling too (it will also go to php_error.log).
    return false;
});

set_exception_handler(function (Throwable $e): void {
    rf_log_exception($e, 'Uncaught exception');
    // Telegram webhook: don't output errors.
    http_response_code(200);
    exit;
});

register_shutdown_function(function (): void {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        rf_log('ERROR', 'Fatal error (shutdown)', $err);
    }
});
