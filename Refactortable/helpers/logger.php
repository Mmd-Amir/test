<?php
// Shared lightweight logger for refactored modules.
// Safe to be loaded alongside other Refactor* packages.

if (!function_exists('rf_sanitize_filename')) {
    function rf_sanitize_filename(string $name): string
    {
        $name = str_replace(['\\', '/'], '_', $name);
        $name = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $name);
        return trim($name, '_');
    }
}

if (!function_exists('rf_set_module')) {
    function rf_set_module(string $module): void
    {
        $GLOBALS['RF_MODULE'] = $module;
    }
}

if (!function_exists('rf_log')) {
    function rf_log(string $level, string $message, array $context = []): void
    {
        $ts = date('Y-m-d H:i:s');
        $module = $GLOBALS['RF_MODULE'] ?? 'unknown';

        // Prefer globally defined RF_LOG_DIR (used by other Refactor* packages), otherwise default to this package.
        $logDir = defined('RF_LOG_DIR') ? RF_LOG_DIR : (__DIR__ . '/../logs');
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $line = sprintf("[%s] [%s] [%s] %s", $ts, strtoupper($level), $module, $message);
        if (!empty($context)) {
            $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json !== false) {
                $line .= " " . $json;
            }
        }
        $line .= PHP_EOL;

        // Main log
        @file_put_contents($logDir . '/refactor.log', $line, FILE_APPEND);

        // Per-module log
        $modFile = $logDir . '/' . rf_sanitize_filename($module) . '.log';
        @file_put_contents($modFile, $line, FILE_APPEND);
    }
}

if (!function_exists('rf_log_exception')) {
    function rf_log_exception(Throwable $e, string $level = 'ERROR'): void
    {
        rf_log($level, $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'type' => get_class($e),
        ]);
    }
}
