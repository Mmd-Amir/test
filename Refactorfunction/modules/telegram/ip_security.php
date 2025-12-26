<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/telegram/ip_security.php'); }

if (!function_exists('checktelegramip')) {
    function checktelegramip()
    {
        global $telegramStrictIpValidation;

        $strictValidation = $telegramStrictIpValidation;
        if (!is_bool($strictValidation)) {
            $strictValidation = true;
        }

        if ($strictValidation === false) {
            return true;
        }

        $clientIp = getClientIpConsideringProxies();
        if ($clientIp === null) {
            return false;
        }

        $telegramIpRanges = [
            ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'],
            ['lower' => '91.108.4.0', 'upper' => '91.108.7.255'],
            ['lower' => '2001:67c:4e8::', 'upper' => '2001:67c:4e8:ffff:ffff:ffff:ffff:ffff'],
        ];

        foreach ($telegramIpRanges as $range) {
            if (isClientIpInRange($clientIp, $range['lower'], $range['upper'])) {
                return true;
            }
        }

        return false;
    }
}


if (!function_exists('getClientIpConsideringProxies')) {
    function getClientIpConsideringProxies()
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_TRUE_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED',
        ];

        foreach ($headers as $header) {
            if (empty($_SERVER[$header]) || !is_string($_SERVER[$header])) {
                continue;
            }

            $rawValue = trim($_SERVER[$header]);
            if ($rawValue === '') {
                continue;
            }

            $candidateIps = extractClientIpsFromHeader($rawValue, $header);
            foreach ($candidateIps as $candidate) {
                $candidate = normaliseProxyIpCandidate($candidate);
                if ($candidate === null || $candidate === '') {
                    continue;
                }

                if (!filter_var($candidate, FILTER_VALIDATE_IP)) {
                    continue;
                }

                if (!isPublicIpAddress($candidate)) {
                    continue;
                }

                return $candidate;
            }
        }

        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;
        if (is_string($remoteAddr)) {
            $remoteAddr = trim($remoteAddr);
            if ($remoteAddr !== '' && filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
                return $remoteAddr;
            }
        }

        return null;
    }
}


if (!function_exists('extractClientIpsFromHeader')) {
    function extractClientIpsFromHeader($value, $header)
    {
        switch ($header) {
            case 'HTTP_X_FORWARDED_FOR':
                $parts = preg_split('/\s*,\s*/', $value);
                return $parts !== false ? $parts : [];
            case 'HTTP_FORWARDED':
                $matches = [];
                preg_match_all('/for=([^;,"]+|"[^"]+")/i', $value, $matches);
                $results = [];
                foreach ($matches[1] ?? [] as $match) {
                    $results[] = $match;
                }
                return $results;
            default:
                return [$value];
        }
    }
}


if (!function_exists('normaliseProxyIpCandidate')) {
    function normaliseProxyIpCandidate($candidate)
    {
        if (!is_string($candidate)) {
            return null;
        }

        $candidate = trim($candidate);
        if ($candidate === '') {
            return null;
        }

        $candidate = trim($candidate, "\"' ");

        if (stripos($candidate, 'for=') === 0) {
            $candidate = substr($candidate, 4);
            $candidate = ltrim($candidate, '=');
        }

        $candidate = trim($candidate, "\"' ");

        if (strpos($candidate, '[') === 0) {
            $closingBracket = strpos($candidate, ']');
            if ($closingBracket !== false) {
                $candidate = substr($candidate, 1, $closingBracket - 1);
            }
        }

        $candidate = trim($candidate, '[]');

        if (strpos($candidate, ':') !== false && substr_count($candidate, ':') === 1 && strpos($candidate, '.') !== false) {
            [$possibleIp, $possiblePort] = explode(':', $candidate, 2);
            $possiblePort = trim($possiblePort);
            if ($possiblePort === '' || ctype_digit(str_replace([' ', "\t"], '', $possiblePort))) {
                $candidate = $possibleIp;
            }
        }

        if (strpos($candidate, '%') !== false) {
            $candidateWithoutZone = preg_replace('/%.*$/', '', $candidate);
            if (is_string($candidateWithoutZone)) {
                $candidate = $candidateWithoutZone;
            }
        }

        $candidate = trim($candidate);

        return $candidate === '' ? null : $candidate;
    }
}


if (!function_exists('isPublicIpAddress')) {
    function isPublicIpAddress($ipAddress)
    {
        return filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }
}


if (!function_exists('isClientIpInRange')) {
    function isClientIpInRange($clientIp, $lowerBound, $upperBound)
    {
        $clientPacked = inet_pton($clientIp);
        $lowerPacked = inet_pton($lowerBound);
        $upperPacked = inet_pton($upperBound);

        if ($clientPacked === false || $lowerPacked === false || $upperPacked === false) {
            return false;
        }

        $length = strlen($clientPacked);
        if ($length !== strlen($lowerPacked) || $length !== strlen($upperPacked)) {
            return false;
        }

        return strcmp($clientPacked, $lowerPacked) >= 0 && strcmp($clientPacked, $upperPacked) <= 0;
    }
}
