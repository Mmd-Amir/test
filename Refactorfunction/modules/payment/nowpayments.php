<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/payment/nowpayments.php'); }

if (!function_exists('updatePaymentMessageId')) {
    function updatePaymentMessageId($response, $orderId)
    {
        if (!is_array($response)) {
            error_log("Failed to send payment message for order {$orderId}: unexpected response");
            return false;
        }

        if (empty($response['ok'])) {
            error_log("Failed to send payment message for order {$orderId}: " . json_encode($response));
            return false;
        }

        if (!isset($response['result']['message_id'])) {
            error_log("Missing message_id for order {$orderId}: " . json_encode($response));
            return false;
        }

        update("Payment_report", "message_id", intval($response['result']['message_id']), "id_order", $orderId);
        return true;
    }
}


if (!function_exists('nowPayments')) {
    function nowPayments($payment, $price_amount, $order_id, $order_description)
    {
        global $domainhosts;
        $apinowpayments = select("PaySetting", "*", "NamePay", "marchent_tronseller", "select")['ValuePay'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.nowpayments.io/v1/' . $payment,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 7000,
            CURLOPT_ENCODING => '',
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array(
                'x-api-key:' . $apinowpayments,
                'Content-Type: application/json'
            ),
        ));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
            'price_amount' => $price_amount,
            'price_currency' => 'usd',
            'order_id' => $order_id,
            'order_description' => $order_description,
            'ipn_callback_url' => "https://" . $domainhosts . "/payment/nowpayment.php"
        ]));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}


if (!function_exists('StatusPayment')) {
    function StatusPayment($paymentid)
    {
        $apinowpayments = select("PaySetting", "*", "NamePay", "marchent_tronseller", "select")['ValuePay'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.nowpayments.io/v1/payment/' . $paymentid,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'x-api-key:' . $apinowpayments
            ),
        ));
        $response = curl_exec($curl);
        $response = json_decode($response, true);
        curl_close($curl);
        return $response;
    }
}
