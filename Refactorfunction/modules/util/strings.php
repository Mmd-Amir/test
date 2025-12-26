<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/util/strings.php'); }

if (!function_exists('generateUsername')) {
    function generateUsername($from_id, $Metode, $username, $randomString, $text, $namecustome, $usernamecustom)
    {
        $setting = select("setting", "*", null, null, "select");
        $user = select("user", "*", "id", $from_id, "select");
        if ($user == false) {
            $user = array();
            $user = array(
                'number_username' => '',
            );
        }
        if ($Metode == "آیدی عددی + حروف و عدد رندوم") {
            return $from_id . "_" . $randomString;
        } elseif ($Metode == "نام کاربری + عدد به ترتیب") {
            if ($username == "NOT_USERNAME") {
                if (preg_match('/^\w{3,32}$/', $namecustome)) {
                    $username = $namecustome;
                }
            }
            return $username . "_" . $user['number_username'];
        } elseif ($Metode == "نام کاربری دلخواه")
            return $text;
        elseif ($Metode == "نام کاربری دلخواه + عدد رندوم") {
            $random_number = rand(1000000, 9999999);
            return $text . "_" . $random_number;
        } elseif ($Metode == "متن دلخواه + عدد رندوم") {
            return $namecustome . "_" . $randomString;
        } elseif ($Metode == "متن دلخواه + عدد ترتیبی") {
            return $namecustome . "_" . $setting['numbercount'];
        } elseif ($Metode == "آیدی عددی+عدد ترتیبی") {
            return $from_id . "_" . $user['number_username'];
        } elseif ($Metode == "متن دلخواه نماینده + عدد ترتیبی") {
            if ($usernamecustom == "none") {
                return $namecustome . "_" . $setting['numbercount'];
            }
            return $usernamecustom . "_" . $user['number_username'];
        }
    }
}


if (!function_exists('sanitizeUserName')) {
    function sanitizeUserName($userName)
    {
        $forbiddenCharacters = [
            "'",
            "\"",
            "<",
            ">",
            "--",
            "#",
            ";",
            "\\",
            "%",
            "(",
            ")"
        ];

        foreach ($forbiddenCharacters as $char) {
            $userName = str_replace($char, "", $userName);
        }

        return $userName;
    }
}


if (!function_exists('sanitize_recursive')) {
    function sanitize_recursive(array $data): array
    {
        $sanitized_data = [];
        foreach ($data as $key => $value) {
            $sanitized_key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            if (is_array($value)) {
                $sanitized_data[$sanitized_key] = sanitize_recursive($value);
            } elseif (is_string($value)) {
                $sanitized_data[$sanitized_key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            } elseif (is_int($value)) {
                $sanitized_data[$sanitized_key] = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            } elseif (is_float($value)) {
                $sanitized_data[$sanitized_key] = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            } elseif (is_bool($value) || is_null($value)) {
                $sanitized_data[$sanitized_key] = $value;
            } else {
                $sanitized_data[$sanitized_key] = $value;
            }
        }
        return $sanitized_data;
    }
}


if (!function_exists('isBase64')) {
    function isBase64($string)
    {
        if (base64_encode(base64_decode($string, true)) === $string) {
            return true;
        }
        return false;
    }
}


if (!function_exists('inlineFixer')) {
    function inlineFixer($str, int $count_button = 1)
    {
        $str = trim($str);
        if (preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}]/u', $str)) {
            if ($count_button >= 1) {
                switch ($count_button) {
                    case 1:
                        $maxLength = 56;
                        break;
                    case 2:
                        $maxLength = 24;
                        break;
                    case 3:
                        $maxLength = 14;
                        break;
                    default:
                        $maxLength = 2;
                }
                $visualLength = 2;
                $trimmedString = '';
                foreach (mb_str_split($str) as $char) {
                    if (preg_match('/[\x{1F300}-\x{1F6FF}\x{1F900}-\x{1F9FF}\x{1F1E6}-\x{1F1FF}]/u', $char)) {
                        $visualLength += 2;
                    } else
                        $visualLength++;

                    if ($visualLength > $maxLength)
                        break;

                    $trimmedString .= $char;
                }
                if ($visualLength > $maxLength) {
                    return trim($trimmedString) . '..';
                }
            }
        }
        return trim($str);
    }
}
