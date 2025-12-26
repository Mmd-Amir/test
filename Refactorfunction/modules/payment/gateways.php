<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/payment/gateways.php'); }

if (!function_exists('plisio')) {
    function plisio($order_id, $price)
    {
        $apinowpayments = select("PaySetting", "ValuePay", "NamePay", "apinowpayment", "select")['ValuePay'];
        $api_key = $apinowpayments;

        $url = 'https://api.plisio.net/api/v1/invoices/new';
        $url .= '?currency=TRX';
        $url .= '&amount=' . urlencode($price);
        $url .= '&order_number=' . urlencode($order_id);
        $url .= '&email=customer@plisio.net';
        $url .= '&order_name=plisio';
        $url .= '&language=fa';
        $url .= '&api_key=' . urlencode($api_key);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($ch), true);
        return $response['data'];
        curl_close($ch);
    }
}


if (!function_exists('CreatePaymentNv')) {
    function CreatePaymentNv($invoice_id, $amount)
    {
        global $domainhosts;
        $PaySetting = select("PaySetting", "ValuePay", "NamePay", "marchentpaynotverify", "select")['ValuePay'];
        $data = [
            'api_key' => $PaySetting,
            'amount' => $amount,
            'callback_url' => "https://" . $domainhosts . "/payment/paymentnv/back.php",
            'desc' => $invoice_id
        ];
        $data = json_encode($data);
        $ch = curl_init("https://donatekon.com/pay/api/dargah/create");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
}


if (!function_exists('createPayZarinpal')) {
    function createPayZarinpal($price, $order_id)
    {
        global $domainhosts;
        $marchent_zarinpal = select("PaySetting", "ValuePay", "NamePay", "merchant_zarinpal", "select")['ValuePay'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.zarinpal.com/pg/v4/payment/request.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json'
            ),
        ));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
            "merchant_id" => $marchent_zarinpal,
            "currency" => "IRT",
            "amount" => $price,
            "callback_url" => "https://$domainhosts/payment/zarinpal.php",
            "description" => $order_id,
            "metadata" => array(
                "order_id" => $order_id
            )
        ]));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}


if (!function_exists('createPayZarinpey')) {
    function createPayZarinpey($price, $order_id, $userId)
    {
        global $domainhosts;

        $token = getPaySettingValue('token_zarinpey');
        if (empty($token) || $token === '0') {
            return [
                'success' => false,
                'message' => 'توکن زرین پی تنظیم نشده است.',
            ];
        }

        $normalizedPrice = filter_var($price, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 1,
            ],
        ]);

        if ($normalizedPrice === false) {
            return [
                'success' => false,
                'message' => 'مبلغ تراکنش نامعتبر است.',
            ];
        }

        $amountRial = $normalizedPrice * 10;

        $baseHost = trim($domainhosts ?? '');
        $scheme = 'https';
        if ($baseHost === '') {
            $httpsFlag = $_SERVER['HTTPS'] ?? '';
            if ($httpsFlag === '' || strtolower($httpsFlag) === 'off') {
                $scheme = 'http';
            }
        }

        $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_SPECIAL_CHARS);
        if ($baseHost !== '') {
            $callbackBase = $scheme . '://' . ltrim($baseHost, '/');
        } elseif (!empty($host)) {
            $callbackBase = $scheme . '://' . $host;
        } else {
            return [
                'success' => false,
                'message' => 'امکان تعیین آدرس بازگشت وجود ندارد.',
            ];
        }

        $payload = [
            'amount' => $amountRial,
            'order_id' => $order_id,
            'callback_url' => rtrim($callbackBase, '/') . '/payment/ZarinPay/successful.php',
            'type' => 'card',
            'customer_user_id' => $userId,
            'description' => sprintf('پرداخت فاکتور %s', $order_id),
        ];

        $ch = curl_init('https://zarinpay.me/api/create-payment');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'message' => $error,
            ];
        }

        curl_close($ch);

        $result = json_decode($response, true);
        if (!is_array($result)) {
            return [
                'success' => false,
                'message' => 'پاسخ نامعتبر از زرین پی دریافت شد.',
            ];
        }

        if (empty($result['success'])) {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'خطا در ایجاد پرداخت',
                'http_code' => $httpCode,
            ];
        }

        $data = $result['data'] ?? [];
        $authority = $result['authority'] ?? ($data['authority'] ?? null);
        $paymentLink = $result['payment_link']
            ?? ($result['payment_url'] ?? ($data['payment_link'] ?? ($data['payment_url'] ?? null)));

        if (empty($authority) || empty($paymentLink)) {
            return [
                'success' => false,
                'message' => 'پاسخ نامعتبر از زرین پی دریافت شد.',
            ];
        }

        return [
            'success' => true,
            'authority' => $authority,
            'payment_link' => $paymentLink,
            'amount_rial' => $amountRial,
            'raw_response' => $result,
        ];
    }
}


if (!function_exists('createPayaqayepardakht')) {
    function createPayaqayepardakht($price, $order_id)
    {
        global $domainhosts;
        $merchant_aqayepardakht = select("PaySetting", "ValuePay", "NamePay", "merchant_id_aqayepardakht", "select")['ValuePay'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://panel.aqayepardakht.ir/api/v2/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json'
            ),
        ));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
            'pin' => $merchant_aqayepardakht,
            'amount' => $price,
            'callback' => $domainhosts . "/payment/aqayepardakht.php",
            'invoice_id' => $order_id,
        ]));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}
