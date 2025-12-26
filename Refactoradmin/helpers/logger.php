<?php
// Refactor logger (per-module + global log)
//
// NOTE:
// Both Refactorindex and Refactoradmin may be loaded in the same request.
// To prevent "Cannot redeclare ..." fatal errors, every function is guarded
// with function_exists().

if (!defined('RF_LOG_DIR')) {
    define('RF_LOG_DIR', __DIR__ . '/../logs');
}
if (!is_dir(RF_LOG_DIR)) {
    @mkdir(RF_LOG_DIR, 0775, true);
}

$GLOBALS['RF_CURRENT_MODULE'] = $GLOBALS['RF_CURRENT_MODULE'] ?? 'unknown';

if (!function_exists('rf_set_module')) {
    function rf_set_module(string $module): void
    {
        $GLOBALS['RF_CURRENT_MODULE'] = $module;
    }
}

if (!function_exists('rf_get_module')) {
    function rf_get_module(): string
    {
        return $GLOBALS['RF_CURRENT_MODULE'] ?? 'unknown';
    }
}

if (!function_exists('rf_log')) {
    function rf_log(string $level, string $message, array $context = []): void
    {
        $ts = date('Y-m-d H:i:s');
        $module = function_exists('rf_get_module') ? rf_get_module() : ($GLOBALS['RF_CURRENT_MODULE'] ?? 'unknown');

        $contextJson = '';
        if (!empty($context)) {
            $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $line = '[' . $ts . '] [' . strtoupper($level) . '] [' . $module . '] ' . $message;
        if ($contextJson !== '' && $contextJson !== 'null') {
            $line .= ' ' . $contextJson;
        }
        $line .= PHP_EOL;

        // Global log
        @file_put_contents(RF_LOG_DIR . '/refactor.log', $line, FILE_APPEND | LOCK_EX);

        // Per-module log
        $safe = preg_replace('/[^a-zA-Z0-9_.-]+/', '_', (string)$module);
        if (!$safe) {
            $safe = 'unknown';
        }
        @file_put_contents(RF_LOG_DIR . '/' . $safe . '.log', $line, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('rf_log_exception')) {
    function rf_log_exception(Throwable $e, string $hint = ''): void
    {
        $ctx = [
            'hint' => $hint,
            'type' => get_class($e),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];

        if (function_exists('rf_log')) {
            rf_log('ERROR', 'Exception', $ctx);
        } else {
            error_log('[Refactor] Exception ' . json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }
}
