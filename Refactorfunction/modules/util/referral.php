<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/util/referral.php'); }

if (!function_exists('generateReferralCode')) {
    function generateReferralCode($length = 12)
    {
        $length = max(1, (int) $length);
        $bytes = (int) ceil($length / 2);

        if (function_exists('random_bytes')) {
            try {
                $code = bin2hex(random_bytes($bytes));
                return substr($code, 0, $length);
            } catch (Exception $exception) {
                error_log('Falling back to pseudo-random referral code generator: ' . $exception->getMessage());
            } catch (Error $exception) {
                error_log('Falling back to pseudo-random referral code generator: ' . $exception->getMessage());
            }
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxIndex = strlen($characters) - 1;
        $code = '';

        for ($i = 0; $i < $length; ++$i) {
            if (function_exists('random_int')) {
                try {
                    $index = random_int(0, $maxIndex);
                } catch (Exception $exception) {
                    error_log('random_int failed, using mt_rand fallback: ' . $exception->getMessage());
                    $index = mt_rand(0, $maxIndex);
                } catch (Error $exception) {
                    error_log('random_int failed, using mt_rand fallback: ' . $exception->getMessage());
                    $index = mt_rand(0, $maxIndex);
                }
            } else {
                $index = mt_rand(0, $maxIndex);
            }

            $code .= $characters[$index];
        }

        return $code;
    }
}


if (!function_exists('ensureUserInvitationCode')) {
    function ensureUserInvitationCode($userId, $currentCode = null, $length = 12)
    {
        if (!is_scalar($userId) || (string) $userId === '') {
            return null;
        }

        $currentCode = is_string($currentCode) ? trim($currentCode) : '';
        if ($currentCode !== '') {
            return $currentCode;
        }

        $newCode = generateReferralCode($length);
        update('user', 'codeInvitation', $newCode, 'id', (string) $userId);

        return $newCode;
    }
}


if (!function_exists('isValidInvitationCode')) {
    function isValidInvitationCode($setting, $fromId, $verfy_status)
    {

        if ($setting['verifybucodeuser'] == "onverify" && $verfy_status != 1) {
            sendmessage($fromId, "حساب کاربری شما با موفقیت احرازهویت گردید", null, 'html');
            update("user", "verify", "1", "id", $fromId);
            update("user", "cardpayment", "1", "id", $fromId);
        }
    }
}
