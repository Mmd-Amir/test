<?php
// Refactor logger (per-module + global log)
// Guarded to avoid "Cannot redeclare ..." when multiple refactor folders are loaded in one request.

if (!defined('RF_LOG_DIR')) {
    define('RF_LOG_DIR', __DIR__ . '/../logs');
}
if (!is_dir(RF_LOG_DIR)) {
    @mkdir(RF_LOG_DIR, 0775, true);
}

if (!function_exists('rf_get_current_module')) {
    function rf_get_current_module(): string
    {
        return (string) ($GLOBALS['RF_CURRENT_MODULE'] ?? 'unknown');
    }
}

if (!function_exists('rf_set_module')) {
    function rf_set_module(string $module): void
    {
        $GLOBALS['RF_CURRENT_MODULE'] = $module;
    }
}

if (!function_exists('rf_log')) {
    function rf_log(string $level, string $message, array $context = []): void
    {
        $module = rf_get_current_module();
        $ts = date('Y-m-d H:i:s');
        $line = '[' . $ts . '] [' . strtoupper($level) . '] [' . $module . '] ' . $message;

        if (!empty($context)) {
            $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $line .= ' ' . ($json !== false ? $json : '');
        }
        $line .= PHP_EOL;

        // Per-module file
        $safeModule = preg_replace('/[^a-zA-Z0-9_\-\.]+/', '_', $module);
        @file_put_contents(RF_LOG_DIR . '/' . $safeModule . '.log', $line, FILE_APPEND);

        // Global file
        @file_put_contents(RF_LOG_DIR . '/refactor.log', $line, FILE_APPEND);
    }
}

if (!function_exists('rf_log_exception')) {
    function rf_log_exception(Throwable $e, string $message = 'Exception'): void
    {
        rf_log('ERROR', $message, [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}

if (!function_exists('rf_stop')) {
    function rf_stop(string $reason = ''): void
    {
        if ($reason !== '') {
            rf_log('NOTICE', 'Stopped', ['reason' => $reason]);
        }
        exit;
    }
}
