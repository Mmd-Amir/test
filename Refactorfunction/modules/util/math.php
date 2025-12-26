<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/util/math.php'); }

if (!function_exists('safe_divide')) {
        function safe_divide($numerator, $denominator, $fallback = 0)
        {
            if (!is_numeric($numerator) || !is_numeric($denominator)) {
                return $fallback;
            }

            $denominator = (float) $denominator;
            if ($denominator == 0.0) {
                return $fallback;
            }

            $result = (float) $numerator / $denominator;

            if (!is_finite($result)) {
                return $fallback;
            }

            return $result;
        }
}
