<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/i18n/language.php'); }

if (!function_exists('languagechange')) {
    function languagechange($path_dir)
    {
        $setting = select("setting", "*");
        return json_decode(file_get_contents($path_dir), true)['fa'];
        if (intval($setting['languageen']) == 1) {
            return json_decode(file_get_contents($path_dir), true)['en'];
        } elseif (intval($setting['languageru']) == 1) {
            return json_decode(file_get_contents($path_dir), true)['ru'];
        } else {
            return json_decode(file_get_contents($path_dir), true)['fa'];
        }
    }
}
