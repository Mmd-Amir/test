<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/payment/invoice.php'); }

if (!function_exists('createInvoice')) {
    function createInvoice($amount)
    {
        global $from_id, $domainhosts;
        $PaySetting = select("PaySetting", "*", "NamePay", "apiiranpay", "select")['ValuePay'];
        $walletaddress = select("PaySetting", "*", "NamePay", "walletaddress", "select")['ValuePay'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://pay.melorinabeauty.ir/api/factor/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('amount' => $amount, 'address' => $walletaddress, 'base' => 'trx'),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Token ' . $PaySetting
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }
}


if (!function_exists('verifpay')) {
    function verifpay($id)
    {
        global $from_id, $domainhosts;
        $PaySetting = select("PaySetting", "*", "NamePay", "apiiranpay", "select")['ValuePay'];
        $walletaddress = select("PaySetting", "*", "NamePay", "walletaddress", "select")['ValuePay'];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://pay.melorinabeauty.ir/api/factor/status?id=' . $id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Token ' . $PaySetting
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}


if (!function_exists('createInvoiceiranpay1')) {
    function createInvoiceiranpay1($amount, $id_invoice)
    {
        global $domainhosts;
        $PaySetting = select("PaySetting", "*", "NamePay", "marchent_floypay", "select")['ValuePay'];
        $curl = curl_init();
        $amount = intval($amount);
        $data = [
            "ApiKey" => $PaySetting,
            "Hash_id" => $id_invoice,
            "Amount" => $amount . "0",
            "CallbackURL" => "https://$domainhosts/payment/iranpay1.php"
        ];
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://tetra98.ir/api/create_order",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}


if (!function_exists('verifyxvoocher')) {
    function verifyxvoocher($code)
    {
        $PaySetting = select("PaySetting", "*", "NamePay", "apiiranpay", "select")['ValuePay'];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://bot.donatekon.com/api/transaction/verify/" . $code,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'Content-Type: application/json',
                'Authorization: ' . $PaySetting
            ),
        ));

        $response = curl_exec($curl);
        return json_decode($response, true);

        curl_close($curl);
    }
}
