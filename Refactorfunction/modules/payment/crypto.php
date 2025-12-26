<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/payment/crypto.php'); }

if (!function_exists('publickey')) {
    function publickey()
    {
        $randomBytes = static function (int $length) {
            if (function_exists('random_bytes')) {
                try {
                    return random_bytes($length);
                } catch (Throwable $exception) {
                    error_log('random_bytes failed: ' . $exception->getMessage());
                }
            }

            if (class_exists('\\ParagonIE_Sodium_Compat') && method_exists('\\ParagonIE_Sodium_Compat', 'randombytes_buf')) {
                try {
                    return \ParagonIE_Sodium_Compat::randombytes_buf($length);
                } catch (Throwable $exception) {
                    error_log('sodium_compat randombytes_buf failed: ' . $exception->getMessage());
                }
            }

            return null;
        };

        if (function_exists('sodium_crypto_box_keypair')) {
            try {
                $privateKey = sodium_crypto_box_keypair();
                $privateKeyEncoded = base64_encode(sodium_crypto_box_secretkey($privateKey));
                $publicKey = sodium_crypto_box_publickey($privateKey);
                $publicKeyEncoded = base64_encode($publicKey);
                $presharedBytes = $randomBytes(32);

                if ($presharedBytes === null) {
                    throw new RuntimeException('Unable to generate secure preshared key.');
                }

                return [
                    'private_key' => $privateKeyEncoded,
                    'public_key' => $publicKeyEncoded,
                    'preshared_key' => base64_encode($presharedBytes)
                ];
            } catch (Throwable $exception) {
                error_log('libsodium key generation failed: ' . $exception->getMessage());
            }
        }

        if (!class_exists('\\ParagonIE_Sodium_Compat')) {
            $sodiumCompatAutoloaders = [
                APP_ROOT_PATH . '/vendor/autoload.php',
                APP_ROOT_PATH . '/vendor/paragonie/sodium_compat/autoload.php'
            ];

            foreach ($sodiumCompatAutoloaders as $autoloadPath) {
                if (is_readable($autoloadPath)) {
                    require_once $autoloadPath;
                }
            }
            unset($sodiumCompatAutoloaders, $autoloadPath);
        }

        if (class_exists('\\ParagonIE_Sodium_Compat') && method_exists('\\ParagonIE_Sodium_Compat', 'crypto_box_keypair')) {
            try {
                $privateKey = \ParagonIE_Sodium_Compat::crypto_box_keypair();
                $privateKeyEncoded = base64_encode(\ParagonIE_Sodium_Compat::crypto_box_secretkey($privateKey));
                $publicKey = \ParagonIE_Sodium_Compat::crypto_box_publickey($privateKey);
                $publicKeyEncoded = base64_encode($publicKey);
                $presharedBytes = $randomBytes(32);

                if ($presharedBytes === null) {
                    throw new RuntimeException('Unable to generate secure preshared key.');
                }

                return [
                    'private_key' => $privateKeyEncoded,
                    'public_key' => $publicKeyEncoded,
                    'preshared_key' => base64_encode($presharedBytes)
                ];
            } catch (Throwable $exception) {
                error_log('sodium_compat key generation failed: ' . $exception->getMessage());
            }
        }

        return [
            'status' => false,
            'msg' => 'Libsodium not available'
        ];
    }
}
