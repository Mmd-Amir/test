<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/util/formatting.php'); }

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2): string
    {
        $base = log($bytes, 1024);
        $power = $bytes > 0 ? floor($base) : 0;
        $suffixes = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت'];
        return round(pow(1024, $base - $power), $precision) . ' ' . $suffixes[$power];
    }
}
