<?php
if (function_exists('rf_set_module')) { rf_set_module('modules/payment/direct_payment.php'); }

if (!function_exists('DirectPayment')) {
    function DirectPayment($order_id, $image = 'images.jpg')
    {
            global $pdo, $ManagePanel, $textbotlang, $keyboardextendfnished, $keyboard, $Confirm_pay, $from_id, $message_id, $datatextbot;
            $buyreport = select("topicid", "idreport", "report", "buyreport", "select")['idreport'];
            $admin_ids = select("admin", "id_admin", null, null, "FETCH_COLUMN");
            $otherservice = select("topicid", "idreport", "report", "otherservice", "select")['idreport'];
            $otherreport = select("topicid", "idreport", "report", "otherreport", "select")['idreport'];
            $errorreport = select("topicid", "idreport", "report", "errorreport", "select")['idreport'];
            $porsantreport = select("topicid", "idreport", "report", "porsantreport", "select")['idreport'];
            $setting = select("setting", "*");
            $Payment_report = select("Payment_report", "*", "id_order", $order_id, "select");
            $paymentNote = formatPaymentReportNote($Payment_report['dec_not_confirmed'] ?? null);
            $format_price_cart = number_format($Payment_report['price']);
            $Balance_id = select("user", "*", "id", $Payment_report['id_user'], "select");
            $steppay = explode("|", $Payment_report['id_invoice']);
            update("user", "Processing_value", "0", "id", $Balance_id['id']);
            update("user", "Processing_value_one", "0", "id", $Balance_id['id']);
            update("user", "Processing_value_tow", "0", "id", $Balance_id['id']);
            update("user", "Processing_value_four", "0", "id", $Balance_id['id']);

        $base = __DIR__ . '/direct_payment';
        if ($steppay[0] == "getconfigafterpay") {
            require $base . '/getconfigafterpay.php';
        } elseif ($steppay[0] == "getextenduser") {
            require $base . '/getextenduser.php';
        } elseif ($steppay[0] == "getextravolumeuser") {
            require $base . '/getextravolumeuser.php';
        } elseif ($steppay[0] == "getextratimeuser") {
            require $base . '/getextratimeuser.php';
        } else {
            require $base . '/fallback_add_balance.php';
        }
    }
}
