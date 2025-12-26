<?php
// Lightweight logger used by refactored modules.
// All functions are guarded to avoid "Cannot redeclare" when multiple refactor folders are loaded.

if (!function_exists('rf_set_module')) {
    function rf_set_module(string $module): void
    {
        $GLOBALS['RF_CURRENT_MODULE'] = $module;
    }
}

if (!function_exists('rf__sanitize_filename')) {
    function rf__sanitize_filename(string $s): string
    {
        $s = str_replace(['\\','/'], '_', $s);
        $s = preg_replace('/[^A-Za-z0-9._-]+/u', '_', $s);
        return trim($s, '_');
    }
}

if (!function_exists('rf__log_dir')) {
    function rf__log_dir(): string
    {
        // Prefer a shared RF_LOG_DIR if already defined elsewhere (Refactorindex, Refactoradmin, ...).
        // Otherwise, default to this package logs folder.
        if (defined('RF_LOG_DIR')) {
            return RF_LOG_DIR;
        }
        $dir = __DIR__ . '/../logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }
}

if (!function_exists('rf_log')) {
    function rf_log(string $level, string $message, array $context = []): void
    {
        $ts = date('Y-m-d H:i:s');
        $module = $GLOBALS['RF_CURRENT_MODULE'] ?? 'unknown';
        $line = sprintf("[%s] [%s] [%s] %s", $ts, strtoupper($level), $module, $message);

        if (!empty($context)) {
            $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json !== false) {
                $line .= " " . $json;
            }
        }
        $line .= "\n";

        $dir = rf__log_dir();
        @file_put_contents($dir . '/refactor.log', $line, FILE_APPEND);

        // Per-module log:
        $fname = rf__sanitize_filename($module);
        if ($fname !== '') {
            @file_put_contents($dir . '/' . $fname . '.log', $line, FILE_APPEND);
        }
    }
}

if (!function_exists('rf_log_exception')) {
    function rf_log_exception(Throwable $e, string $level = 'ERROR'): void
    {
        rf_log($level, $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => substr($e->getTraceAsString(), 0, 4000),
        ]);
    }
}
