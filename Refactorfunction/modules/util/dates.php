<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/util/dates.php'); }

if (!function_exists('isValidDate')) {
    function isValidDate($date)
    {
        return (strtotime($date) != false);
    }
}
