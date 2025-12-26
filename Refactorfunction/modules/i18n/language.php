<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/i18n/language.php'); }

if (!function_exists('languagechange')) {
    /**
     * Load translations from json file and return selected language array.
     *
     * Notes:
     * - In legacy code some callers pass paths like "../text.json".
     *   We normalize to project root (RF_APP_ROOT) to avoid "No such file" warnings.
     * - Avoid repeated file_get_contents/json_decode.
     */
    function languagechange($path_dir)
    {
        // Determine preferred language from DB setting (defaults to 'fa')
        $lang = 'fa';
        if (function_exists('select')) {
            try {
                $setting = select("setting", "*");
                if (is_array($setting)) {
                    if (!empty($setting['languageen']) && intval($setting['languageen']) == 1) {
                        $lang = 'en';
                    } elseif (!empty($setting['languageru']) && intval($setting['languageru']) == 1) {
                        $lang = 'ru';
                    }
                }
            } catch (Throwable $e) {
                if (function_exists('rf_log_exception')) {
                    rf_log_exception($e);
                }
            }
        }

        if (!is_string($path_dir) || $path_dir === '') {
            return array();
        }

        // Resolve path
        $root = defined('RF_APP_ROOT') ? RF_APP_ROOT : dirname(__DIR__, 3);
        $candidates = array();

        // 1) As-is
        $candidates[] = $path_dir;

        // 2) Strip leading ../ or ./
        $normalized = preg_replace('#^(\./|\.\./)+#', '', $path_dir);
        $candidates[] = $normalized;

        // 3) Root-joined
        $candidates[] = rtrim($root, '/').'/'.ltrim($path_dir, '/');
        $candidates[] = rtrim($root, '/').'/'.ltrim($normalized, '/');

        // 4) If only filename matters
        $candidates[] = rtrim($root, '/').'/'.basename($path_dir);

        $filePath = null;
        foreach ($candidates as $p) {
            if (is_string($p) && $p !== '' && file_exists($p)) {
                $filePath = $p;
                break;
            }
        }

        if ($filePath === null) {
            if (function_exists('rf_log')) {
                rf_log('WARNING', 'Language file not found', array('path' => $path_dir));
            }
            return array();
        }

        $raw = @file_get_contents($filePath);
        if ($raw === false) {
            if (function_exists('rf_log')) {
                rf_log('WARNING', 'Failed to read language file', array('path' => $filePath));
            }
            return array();
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            if (function_exists('rf_log')) {
                rf_log('WARNING', 'Invalid language json', array('path' => $filePath));
            }
            return array();
        }

        if (isset($json[$lang]) && is_array($json[$lang])) {
            return $json[$lang];
        }

        // Fallback to fa
        return (isset($json['fa']) && is_array($json['fa'])) ? $json['fa'] : array();
    }
}
