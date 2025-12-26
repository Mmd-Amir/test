<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/db/connection.php'); }

if (!function_exists('getDatabaseConnection')) {
    function getDatabaseConnection()
    {
        static $cachedPdo = null;

        if ($cachedPdo instanceof PDO) {
            return $cachedPdo;
        }

        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
            $cachedPdo = $GLOBALS['pdo'];
            return $cachedPdo;
        }

        $dsn = $GLOBALS['dsn'] ?? null;
        $username = $GLOBALS['usernamedb'] ?? null;
        $password = $GLOBALS['passworddb'] ?? null;
        $options = $GLOBALS['options'] ?? [];

        if (!is_string($dsn) || trim($dsn) === '') {
            error_log('getDatabaseConnection: DSN is not configured.');
            return null;
        }

        try {
            $newPdo = new PDO($dsn, (string) $username, (string) $password, is_array($options) ? $options : []);
            $GLOBALS['pdo'] = $newPdo;
            $cachedPdo = $newPdo;
            return $cachedPdo;
        } catch (PDOException $e) {
            error_log('getDatabaseConnection: Unable to create PDO instance. ' . $e->getMessage());
            return null;
        }
    }
}
