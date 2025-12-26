<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/cron/shell.php'); }

if (!function_exists('isShellExecAvailable')) {
    function isShellExecAvailable()
    {
        static $isAvailable;

        if ($isAvailable !== null) {
            return $isAvailable;
        }

        if (!function_exists('shell_exec')) {
            $isAvailable = false;
            return $isAvailable;
        }

        $disabledFunctions = ini_get('disable_functions');
        if (!empty($disabledFunctions) && stripos($disabledFunctions, 'shell_exec') !== false) {
            $isAvailable = false;
            return $isAvailable;
        }

        $isAvailable = true;
        return $isAvailable;
    }
}


if (!function_exists('getCrontabBinary')) {
    function getCrontabBinary()
    {
        static $resolvedPath;

        if ($resolvedPath !== null) {
            return $resolvedPath ?: null;
        }

        $candidateDirectories = [
            '/usr/local/bin',
            '/usr/bin',
            '/bin',
            '/usr/sbin',
            '/sbin',
        ];

        $environmentPath = getenv('PATH');
        if ($environmentPath !== false && $environmentPath !== '') {
            foreach (explode(PATH_SEPARATOR, $environmentPath) as $pathDirectory) {
                $pathDirectory = trim($pathDirectory);
                if ($pathDirectory !== '' && !in_array($pathDirectory, $candidateDirectories, true)) {
                    $candidateDirectories[] = $pathDirectory;
                }
            }
        }

        foreach ($candidateDirectories as $directory) {
            $executablePath = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'crontab';
            if (@is_file($executablePath) && @is_executable($executablePath)) {
                $resolvedPath = $executablePath;
                return $resolvedPath;
            }
        }

        if (isShellExecAvailable()) {
            $whichOutput = @shell_exec('command -v crontab 2>/dev/null');
            if (is_string($whichOutput)) {
                $whichOutput = trim($whichOutput);
                if ($whichOutput !== '' && @is_executable($whichOutput)) {
                    $resolvedPath = $whichOutput;
                    return $resolvedPath;
                }
            }
        }

        $resolvedPath = '';
        error_log('Unable to locate the crontab executable on this system.');

        return null;
    }
}


if (!function_exists('runShellCommand')) {
    function runShellCommand($command)
    {
        if (!isShellExecAvailable()) {
            error_log('shell_exec is not available; unable to run command: ' . $command);
            return null;
        }

        if (getenv('PATH') === false || trim((string) getenv('PATH')) === '') {
            putenv('PATH=/usr/local/bin:/usr/bin:/bin');
        }

        return shell_exec($command);
    }
}
