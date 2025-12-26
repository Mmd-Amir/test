<?php
// Root entrypoint (keeps webhook URL unchanged)

$entry = __DIR__ . '/Refactorindex/index.php';

if (!file_exists($entry)) {
    http_response_code(500);
    error_log("[Refactorindex] Missing entry file: " . $entry);
    echo "Server misconfiguration.";
    exit;
}

require_once $entry;
