<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/cron/http_urls.php'); }

if (!function_exists('resolveCronHttpDirectory')) {
    function resolveCronHttpDirectory(): string
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $configured = null;
        if (defined('CRON_HTTP_BASE_PATH')) {
            $configured = CRON_HTTP_BASE_PATH;
        } elseif (($env = getenv('CRON_HTTP_BASE_PATH')) !== false) {
            $configured = $env;
        }

        if (is_string($configured)) {
            $configured = trim($configured);
            if ($configured === '' || $configured === '/') {
                return $cache = '';
            }

            return $cache = trim($configured, "/\\");
        }

        $preferredOrder = ['cronbot', 'cron'];
        foreach ($preferredOrder as $candidate) {
            $candidate = trim($candidate, "/\\");
            if ($candidate === '') {
                continue;
            }

            if (is_dir(APP_ROOT_PATH . '/' . $candidate)) {
                return $cache = $candidate;
            }
        }

        return $cache = 'cronbot';
    }
}


if (!function_exists('getCronHttpRelativePrefix')) {
    function getCronHttpRelativePrefix(): string
    {
        $directory = resolveCronHttpDirectory();
        if ($directory === '') {
            return '';
        }

        return trim($directory, "/\\") . '/';
    }
}


if (!function_exists('buildCronScriptUrlByHost')) {
    function buildCronScriptUrlByHost(string $host, string $script): string
    {
        $host = trim($host);
        if ($host === '') {
            $host = 'localhost';
        }

        $base = preg_match('~^https?://~i', $host) ? rtrim($host, '/') : 'https://' . $host;
        $script = ltrim($script, '/');

        $prefix = getCronHttpRelativePrefix();
        if ($prefix !== '' && substr($prefix, -1) !== '/') {
            $prefix .= '/';
        }

        return $base . '/' . $prefix . $script;
    }
}
