<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/payment/tronado.php'); }

if (!function_exists('tronratee')) {
    function tronratee(array $requiredKeys = [])
    {

        $normalizedKeys = [];
        foreach ($requiredKeys as $key) {
            $normalized = strtoupper(trim((string) $key));
            if ($normalized === '') {
                continue;
            }
            $normalizedKeys[$normalized] = true;
        }

        if (empty($normalizedKeys)) {
            $normalizedKeys = ['TRX' => true, 'TON' => true, 'USD' => true];
        }

        $needsTrx = isset($normalizedKeys['TRX']);
        $needsTon = isset($normalizedKeys['TON']);
        $needsUsd = isset($normalizedKeys['USD']);

        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
            ],
        ]);

        $result = [];
        $missingKeys = [];

        if (!$needsTrx && !$needsTon && !$needsUsd) {
            return ['ok' => true, 'result' => $result];
        }

        $usdToToman = null;

        $usdResponse = @file_get_contents('https://sarfe.erfjab.com/api/prices', false, $context);
        if ($usdResponse === false) {
            error_log('Failed to fetch USD price from Sarfe API');
        } else {
            $usdData = json_decode($usdResponse, true);
            if (!is_array($usdData)) {
                error_log('Invalid response received from Sarfe API');
            } else {
                $usdPrice = null;
                $usdRawValues = [];

                foreach (['usd1', 'usd2'] as $usdKey) {
                    if (!array_key_exists($usdKey, $usdData)) {
                        continue;
                    }

                    $rawValue = $usdData[$usdKey];
                    $usdRawValues[$usdKey] = $rawValue;

                    if (is_string($rawValue)) {

                        $normalizedValue = preg_replace('/[^\d\.\-]/u', '', $rawValue);
                    } elseif (is_numeric($rawValue)) {
                        $normalizedValue = (string) $rawValue;
                    } else {
                        continue;
                    }

                    if (!is_numeric($normalizedValue)) {
                        continue;
                    }

                    $numericValue = abs((float) $normalizedValue);
                    if ($numericValue > 0.0) {
                        $usdPrice = $numericValue;
                        break;
                    }
                }

                if ($usdPrice === null) {
                    $rawLog = '';
                    if (!empty($usdRawValues)) {
                        $rawLog = ' Raw values: ' . json_encode($usdRawValues);
                    }
                    error_log('Missing USD price from Sarfe API.' . $rawLog);
                } else {

                    $usdToToman = $usdPrice;
                }
            }
        }

        if ($usdToToman === null) {
            if ($needsTrx) {
                $missingKeys[] = 'TRX';
            }
            if ($needsTon) {
                $missingKeys[] = 'Ton';
            }
            if ($needsUsd) {
                $missingKeys[] = 'USD';
            }

            $ok = empty($missingKeys);
            return ['ok' => $ok, 'result' => $result];
        }

        $fetchCoinPrice = static function (string $id) use ($context) {
            $endpoint = 'https://api.coingecko.com/api/v3/simple/price?ids='
                . rawurlencode($id)
                . '&vs_currencies=usd';

            $response = @file_get_contents($endpoint, false, $context);
            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);
            if (!is_array($data) || !isset($data[$id]['usd']) || !is_numeric($data[$id]['usd'])) {
                return null;
            }

            $value = (float) $data[$id]['usd'];
            if ($value <= 0.0 || !is_finite($value)) {
                return null;
            }

            return $value; 
        };

        if ($needsTrx) {

            $trxUsd = $fetchCoinPrice('tron');
            if ($trxUsd === null) {
                error_log('Missing or invalid TRX price from CoinGecko');
                $missingKeys[] = 'TRX';
            } else {
                $result['TRX'] = round($trxUsd * $usdToToman, 2); 
            }
        }

        if ($needsTon) {
            $tonUsd = $fetchCoinPrice('toncoin');
            if ($tonUsd === null) {
                error_log('Missing or invalid Ton price from CoinGecko');
                $missingKeys[] = 'Ton';
            } else {
                $result['Ton'] = round($tonUsd * $usdToToman, 2); 
            }
        }

        if ($needsUsd) {

            $usdtUsd = $fetchCoinPrice('tether');
            if ($usdtUsd === null) {
                error_log('Missing or invalid USDT price from CoinGecko');
                $missingKeys[] = 'USD';
            } else {
                $result['USD'] = round($usdtUsd * $usdToToman, 2); 
            }
        }

        $ok = empty($missingKeys);

        return ['ok' => $ok, 'result' => $result];
    }
}


if (!function_exists('requireTronRates')) {
    function requireTronRates(array $keys = [])
    {
        $normalizedKeys = [];
        foreach ($keys as $key) {
            $upper = strtoupper(trim((string) $key));
            if ($upper === '') {
                continue;
            }
            $normalizedKeys[$upper] = true;
        }

        $requestedKeys = array_keys($normalizedKeys);
        $rates = tronratee($requestedKeys);

        if (!is_array($rates) || !isset($rates['result']) || !is_array($rates['result'])) {
            return null;
        }

        $result = $rates['result'];

        if (isset($result['USD']) && is_numeric($result['USD'])) {
            $result['USD'] = round(abs((float) $result['USD']), 2);
        }

        $validationKeys = [];
        if (empty($requestedKeys)) {
            $validationKeys = ['TRX', 'Ton', 'USD'];
        } else {
            foreach ($requestedKeys as $requestedKey) {
                if ($requestedKey === 'TON') {
                    $validationKeys[] = 'Ton';
                } elseif ($requestedKey === 'TRX' || $requestedKey === 'USD') {
                    $validationKeys[] = $requestedKey;
                } else {
                    $validationKeys[] = $requestedKey;
                }
            }
        }

        foreach ($validationKeys as $key) {
            if (!isset($result[$key]) || (is_numeric($result[$key]) && (float) $result[$key] == 0.0)) {
                return null;
            }
        }

        return $result;
    }
}


if (!function_exists('trnado')) {
    function trnado($order_id, $price)
    {
        global $domainhosts;

        $errorId = 'TRN-' . bin2hex(random_bytes(4));

        $apitronseller = select("PaySetting", "*", "NamePay", "apiternado", "select")['ValuePay'];
        $walletSetting = select("PaySetting", "*", "NamePay", "walletaddress", "select");
        $walletaddress = trim((string) ($walletSetting['ValuePay'] ?? ''));
        $configuredUrl = trim((string) (select("PaySetting", "*", "NamePay", "urlpaymenttron", "select")['ValuePay'] ?? ''));

        if ($configuredUrl === '') {
            $configuredUrl = 'https://bot.tronado.cloud/api/v3/GetOrderToken';
        }

        if ($walletaddress === '') {
            $lastErrorPayload = [
                'success'  => false,
                'error'    => 'آدرس کیف پول تنظیم نشده است',
                'error_id' => $errorId,
            ];
            error_log('[Tronado] ' . json_encode($lastErrorPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $lastErrorPayload;
        }

        $trxAmountStr = number_format((float)$price, 6, '.', '');

        $callbackUrl = 'https://' . $domainhosts . '/payment/tronado.php';

        $fields = [
            'PaymentID'                  => (string)$order_id,
            'WalletAddress'              => $walletaddress,
            'TronAmount'                 => $trxAmountStr,
            'CallbackUrl'                => $callbackUrl,
            'wageFromBusinessPercentage' => '0',
            'apiVersion'                 => '1',
        ];

        $ch = curl_init($configuredUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $fields, 
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . $apitronseller,
            ],
            CURLOPT_TIMEOUT        => 20,
        ]);

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $errstr   = curl_error($ch);
        $status   = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($errno) {
            $lastErrorPayload = [
                'success'  => false,
                'error'    => "cURL error ($errno): $errstr",
                'http'     => $status,
                'error_id' => $errorId,
            ];
            error_log('[Tronado] ' . json_encode($lastErrorPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $lastErrorPayload;
        }

        if ($status < 200 || $status >= 300) {
            $lastErrorPayload = [
                'success'  => false,
                'error'    => "HTTP $status",
                'http'     => $status,
                'body'     => mb_substr((string)$response, 0, 500, 'UTF-8'),
                'error_id' => $errorId,
            ];
            error_log('[Tronado] ' . json_encode($lastErrorPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $lastErrorPayload;
        }

        $json = json_decode((string)$response, true);
        if (!is_array($json)) {
            $lastErrorPayload = [
                'success'  => false,
                'error'    => 'پاسخ نامعتبر از ترنادو',
                'body'     => mb_substr((string)$response, 0, 500, 'UTF-8'),
                'error_id' => $errorId,
            ];
            error_log('[Tronado] ' . json_encode($lastErrorPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $lastErrorPayload;
        }

        if (!empty($json['IsSuccessful'])) {
            $token = $json['Data']['Token'] ?? null;
            if (!$token) {
                $lastErrorPayload = [
                    'success'  => false,
                    'error'    => 'Token خالی در پاسخ موفق ترنادو',
                    'raw'      => $json,
                    'error_id' => $errorId,
                ];
                error_log('[Tronado] ' . json_encode($lastErrorPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                return $lastErrorPayload;
            }

            return [
                'success'        => true,
                'IsSuccessful'   => true,
                'Data'           => ['Token' => $token],
                'FullPaymentUrl' => $json['Data']['FullPaymentUrl'] ?? null,
                'raw'            => $json,
                'error_id'       => $errorId,
            ];
        }

        $lastErrorPayload = [
            'success'  => false,
            'error'    => $json['Message'] ?? 'خطای نامشخص ترنادو',
            'code'     => $json['Code'] ?? null,
            'raw'      => $json,
            'error_id' => $errorId,
        ];
        error_log('[Tronado] ' . json_encode($lastErrorPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $lastErrorPayload;
    }
}
