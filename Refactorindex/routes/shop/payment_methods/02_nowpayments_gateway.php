<?php
rf_set_module('routes/shop/payment_methods/02_nowpayments_gateway.php');
if (!$rf_get_step_payment_handled && ($datain == "nowpayment")) {
    $rf_get_step_payment_handled = true;
        $rates = requireTronRates(['TRX', 'USD']);
        if ($rates === null) {
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            rf_stop();
        }
        $trx = $rates['TRX'];
        $usd = $rates['USD'];
        $trxprice = $user['Processing_value'] / $trx;
        $usdprice = $user['Processing_value'] / $usd;
        $mainbalanceplisio = select("PaySetting", "ValuePay", "NamePay", "minbalancenowpayment", "select")['ValuePay'];
        $maxbalanceplisio = select("PaySetting", "ValuePay", "NamePay", "maxbalancenowpayment", "select")['ValuePay'];
        if ($user['Processing_value'] < $mainbalanceplisio || $user['Processing_value'] > $maxbalanceplisio) {
            $mainbalanceplisio = number_format($mainbalanceplisio);
            $maxbalanceplisio = number_format($maxbalanceplisio);
            sendmessage($from_id, "❌ حداقل مبلغ واریزی این روش پرداخت باید $mainbalanceplisio و حداکثر $maxbalanceplisio تومان باشد", null, 'HTML');
            rf_stop();
        }
        deletemessage($from_id, $message_id);
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d H:i:s');
        $randomString = bin2hex(random_bytes(5));
        $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        $pay = nowPayments('invoice', $usdprice, $randomString, "order");
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method,id_invoice,dec_not_confirmed) VALUES (?,?,?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "nowpayment";
        $stmt->bind_param("ssssssss", $from_id, $randomString, $dateacc, $user['Processing_value'], $payment_Status, $Payment_Method, $invoice, $pay['id']);
        $stmt->execute();
        if (!isset($pay['id'])) {
            $text_error = json_encode($pay);
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            $ErrorsLinkPayment = "
                        ⭕️ یک کاربر قصد پرداخت با درگاه ارزی داشت که ساخت لینک پرداخت  با خطا مواجه شده و به کاربر لینک داده نشد
✍️ دلیل خطا : $text_error
            
آیدی کابر : $from_id
نام کاربری کاربر : @$username";
            if (strlen($setting['Channel_Report'] ?? '') > 0) {
                telegram('sendmessage', [
                    'chat_id' => $setting['Channel_Report'],
                    'message_thread_id' => $errorreport,
                    'text' => $ErrorsLinkPayment,
                    'parse_mode' => "HTML"
                ]);
            }
            rf_stop();
        }
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => $pay['invoice_url']],
                ]
            ]
        ]);
        $price_format = number_format($user['Processing_value'], 0);
        $USD = number_format($usd);
        $textnowpayments = "
<b>💲 جهت افزایش اعتبار کیف پول خود از طریق ارز دیجیتال روی دکمه پرداخت در انتهای پیام کلیک کنید</b>

⚠️ توجه:  زمان پرداخت 30 دقیقه می باشد پس از 30 دقیقه تراکنش لغو خواهد شد

🌐 برخی از سایت های داخلی جهت خرید ارز دیجیتال 👇
🔸 nikpardakht.com
🔹 webpurse.org
🔸 bitpin.ir
🔹 sarmayex.com
🔸 ok-ex.io
🔹 nobitex.ir
🔸 bitbarg.com
🔹 cafearz.com
🔸 pay98.app
🔢 شماره فاکتور : $randomString
💰 مبلغ فاکتور : $price_format تومان
📊 قیمت دلار: $USD تومان تا این لحظه


<blockquote>⚠️ پس از پرداخت، در صورتی که مبلغ تراکنش به‌درستی واریز شده باشد، موجودی شما حداکثر تا ۱۵ دقیقه آینده به‌صورت خودکار شارژ خواهد شد.</blockquote>


جهت پرداخت از دکمه زیر استفاده👇🏻";
        $gethelp = select("PaySetting", "ValuePay", "NamePay", "helpnowpayment", "select")['ValuePay'];
        if ($gethelp != 2) {
            $data = json_decode($gethelp, true);
            if ($data['type'] == "text") {
                sendmessage($from_id, $data['text'], null, 'HTML');
            } elseif ($data['type'] == "photo") {
                sendphoto($from_id, $data['photoid'], null);
            } elseif ($data['type'] == "video") {
                sendvideo($from_id, $data['videoid'], null);
            }
        }
        $message_id = sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
        updatePaymentMessageId($message_id, $randomString);
}
if (!$rf_get_step_payment_handled && ($datain == "iranpay1")) {
    $rf_get_step_payment_handled = true;
        $rates = requireTronRates(['TRX', 'USD']);
        if ($rates === null) {
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            rf_stop();
        }
        $trx = $rates['TRX'];
        $usd = $rates['USD'];
        $trxprice = round($user['Processing_value'] / $trx, 2);
        $usdprice = $user['Processing_value'] / $usd;
        $mainbalanceplisio = select("PaySetting", "ValuePay", "NamePay", "minbalanceiranpay1", "select")['ValuePay'];
        $maxbalanceplisio = select("PaySetting", "ValuePay", "NamePay", "maxbalanceiranpay1", "select")['ValuePay'];
        if ($user['Processing_value'] < $mainbalanceplisio || $user['Processing_value'] > $maxbalanceplisio) {
            $mainbalanceplisio = number_format($mainbalanceplisio);
            $maxbalanceplisio = number_format($maxbalanceplisio);
            sendmessage($from_id, "❌ حداقل مبلغ واریزی این روش پرداخت باید $mainbalanceplisio و حداکثر $maxbalanceplisio تومان باشد", null, 'HTML');
            rf_stop();
        }
        deletemessage($from_id, $message_id);
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d H:i:s');
        $randomString = bin2hex(random_bytes(5));
        $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method,id_invoice) VALUES (?,?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "Currency Rial 1";
        $stmt->bind_param("sssssss", $from_id, $randomString, $dateacc, $user['Processing_value'], $payment_Status, $Payment_Method, $invoice);
        $stmt->execute();
        $pay = createInvoiceiranpay1($user['Processing_value'], $randomString);
        if ($pay['status'] != "100") {
            $text_error = $pay['message'];
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            $ErrorsLinkPayment = "
⭕️ یک کاربر قصد پرداخت داشت که ساخت لینک پرداخت  با خطا مواجه شده و به کاربر لینک داده نشد
✍️ دلیل خطا : $text_error

آیدی کابر : $from_id
روش پرداخت : $Payment_Method
نام کاربری کاربر : @$username";
            if (strlen($setting['Channel_Report'] ?? '') > 0) {
                telegram('sendmessage', [
                    'chat_id' => $setting['Channel_Report'],
                    'message_thread_id' => $errorreport,
                    'text' => $ErrorsLinkPayment,
                    'parse_mode' => "HTML"
                ]);
            }
            rf_stop();
        }
        update("Payment_report", "dec_not_confirmed", $pay['Authority'], "id_order", $randomString);
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "پرداخت", 'url' => $pay['payment_url_web']]
                ]
            ]
        ]);
        $pricetoman = number_format($user['Processing_value'], 0);
        $textnowpayments = "✅ تراکنش شما ایجاد شد
        
🛒 کد پیگیری:  <code>$randomString</code> 
💲 مبلغ تراکنش به تومان  : <code>$pricetoman</code>


💢 لطفا به این نکات قبل از پرداخت توجه کنید 👇
        
❌ این تراکنش به مدت ۲۴ ساعت اعتبار دارد پس از آن امکان پرداخت این تراکنش امکان ندارد.        


✅ در صورت مشکل میتوانید با پشتیبانی در ارتباط باشید";
        $gethelp = select("PaySetting", "ValuePay", "NamePay", "helpiranpay1", "select")['ValuePay'];
        if ($gethelp != 2) {
            $data = json_decode($gethelp, true);
            if ($data['type'] == "text") {
                sendmessage($from_id, $data['text'], null, 'HTML');
            } elseif ($data['type'] == "photo") {
                sendphoto($from_id, $data['photoid'], null);
            } elseif ($data['type'] == "video") {
                sendvideo($from_id, $data['videoid'], null);
            }
        }
        $message_id = sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
        updatePaymentMessageId($message_id, $randomString);
}
if (!$rf_get_step_payment_handled && ($datain == "iranpay2")) {
    $rf_get_step_payment_handled = true;
        $rates = requireTronRates(['TRX', 'USD']);
        if ($rates === null) {
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            rf_stop();
        }
        $trx = $rates['TRX'];
        $usd = $rates['USD'];
        $trxprice = $user['Processing_value'] / $trx;
        $usdprice = $user['Processing_value'] / $usd;
        $mainbalanceplisio = select("PaySetting", "ValuePay", "NamePay", "minbalanceiranpay2", "select")['ValuePay'];
        $maxbalanceplisio = select("PaySetting", "ValuePay", "NamePay", "maxbalanceiranpay2", "select")['ValuePay'];
        if ($user['Processing_value'] < $mainbalanceplisio || $user['Processing_value'] > $maxbalanceplisio) {
            $mainbalanceplisio = number_format($mainbalanceplisio);
            $maxbalanceplisio = number_format($maxbalanceplisio);
            sendmessage($from_id, "❌ حداقل مبلغ واریزی این روش پرداخت باید $mainbalanceplisio و حداکثر $maxbalanceplisio تومان باشد", null, 'HTML');
            rf_stop();
        }
        deletemessage($from_id, $message_id);
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d H:i:s');
        $randomString = bin2hex(random_bytes(5));
        $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method,id_invoice) VALUES (?,?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "Currency Rial 2";
        $stmt->bind_param("sssssss", $from_id, $randomString, $dateacc, $user['Processing_value'], $payment_Status, $Payment_Method, $invoice);
        $stmt->execute();
        $payment = trnado($randomString, $trxprice);

        $paymentErrorData = null;
        if (!is_array($payment)) {
            $paymentErrorData = ['error' => 'پاسخ نامعتبر از سرویس ترنادو'];
        } elseif ((isset($payment['success']) && $payment['success'] === false) || (isset($payment['error']) && !isset($payment['IsSuccessful']))) {
            $paymentErrorData = $payment;
        }

        if ($paymentErrorData !== null) {
            $errorLines = [];
            if (isset($paymentErrorData['status_code'])) {
                $errorLines[] = "کد وضعیت HTTP: " . $paymentErrorData['status_code'];
            }
            if (isset($paymentErrorData['errno'])) {
                $errorLines[] = "کد خطای cURL: " . $paymentErrorData['errno'];
            }
            if (isset($paymentErrorData['error'])) {
                $errorLines[] = "پیام خطا: " . $paymentErrorData['error'];
            }
            if (isset($paymentErrorData['raw_response'])) {
                $errorLines[] = "پاسخ خام: " . $paymentErrorData['raw_response'];
            }

            if (empty($errorLines)) {
                $errorLines[] = json_encode($paymentErrorData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $text_error = implode("\n", $errorLines);
            $safeErrorText = htmlspecialchars($text_error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            $ErrorsLinkPayment = "
                        ⭕️ یک کاربر قصد پرداخت داشت که ساخت لینک پرداخت  با خطا مواجه شده و به کاربر لینک داده نشد
✍️ دلیل خطا : <pre>$safeErrorText</pre>

آیدی کابر : $from_id
روش پرداخت : $Payment_Method
نام کاربری کاربر : @$username";
            if (strlen($setting['Channel_Report'] ?? '') > 0) {
                telegram('sendmessage', [
                    'chat_id' => $setting['Channel_Report'],
                    'message_thread_id' => $errorreport,
                    'text' => $ErrorsLinkPayment,
                    'parse_mode' => "HTML"
                ]);
            }
            rf_stop();
        }

        if (!isset($payment['IsSuccessful']) || $payment['IsSuccessful'] != "true") {
            $text_error = json_encode($payment, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $safeErrorText = htmlspecialchars($text_error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            $ErrorsLinkPayment = "
                        ⭕️ یک کاربر قصد پرداخت داشت که ساخت لینک پرداخت  با خطا مواجه شده و به کاربر لینک داده نشد
✍️ دلیل خطا : <pre>$safeErrorText</pre>

آیدی کابر : $from_id
روش پرداخت : $Payment_Method
نام کاربری کاربر : @$username";
            if (strlen($setting['Channel_Report'] ?? '') > 0) {
                telegram('sendmessage', [
                    'chat_id' => $setting['Channel_Report'],
                    'message_thread_id' => $errorreport,
                    'text' => $ErrorsLinkPayment,
                    'parse_mode' => "HTML"
                ]);
            }
            rf_stop();
        }
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://t.me/tronado_robot/customerpayment?startapp={$payment['Data']['Token']}"]
                ]
            ]
        ]);
        $pricetoman = number_format($user['Processing_value'], 0);
        $textnowpayments = "✅ تراکنش شما ایجاد شد
        
🛒 کد پیگیری:  <code>$randomString</code> 
💲 مبلغ تراکنش به تومان  : <code>$pricetoman</code>

💢 لطفا به این نکات قبل از پرداخت توجه کنید 👇
        
🔹 تراکنش تا یک روز اعتبار و پس از آن در صورت پرداخت تایید نخواهد شد .
❌ پس از تراکنش 15 تا یک ساعت زمان میبرد تا تراکنش تایید شود

✅ در صورت مشکل میتوانید با پشتیبانی در ارتباط باشید";
        $gethelp = select("PaySetting", "ValuePay", "NamePay", "helpiranpay2", "select")['ValuePay'];
        if ($gethelp != 2) {
            $data = json_decode($gethelp, true);
            if ($data['type'] == "text") {
                sendmessage($from_id, $data['text'], null, 'HTML');
            } elseif ($data['type'] == "photo") {
                sendphoto($from_id, $data['photoid'], null);
            } elseif ($data['type'] == "video") {
                sendvideo($from_id, $data['videoid'], null);
            }
        }
        $message_id = sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
        updatePaymentMessageId($message_id, $randomString);
}
if (!$rf_get_step_payment_handled && ($datain == "iranpay3")) {
    $rf_get_step_payment_handled = true;
        $dateacc = date('Y/m/d');
        $query = "SELECT SUM(price) as price FROM Payment_report WHERE  Payment_Method = 'Currency Rial 1' AND  time LIKE '%$dateacc%'";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $sumpayment = $stmt->fetch(PDO::FETCH_ASSOC);
        if (intval($sumpayment['price']) > 1000000) {
            sendmessage($from_id, "تعداد افراد در صف درخواست درگاه پرداخت بشدت زیاد است 📊

‼️درحال حاظر از روش پرداخت دیگری استفاده کنید", null, 'HTML');
            rf_stop();
        }
        $rates = requireTronRates(['TRX', 'USD']);
        if ($rates === null) {
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            rf_stop();
        }
        $trx = $rates['TRX'];
        $usd = $rates['USD'];
        $trxprice = $user['Processing_value'] / $trx;
        $usdprice = $user['Processing_value'] / $usd;
        $mainbalanceplisio = select("PaySetting", "ValuePay", "NamePay", "minbalanceiranpay", "select")['ValuePay'];
        $maxbalanceplisio = select("PaySetting", "ValuePay", "NamePay", "maxbalanceiranpay", "select")['ValuePay'];
        if ($user['Processing_value'] < $mainbalanceplisio || $user['Processing_value'] > $maxbalanceplisio) {
            $mainbalanceplisio = number_format($mainbalanceplisio);
            $maxbalanceplisio = number_format($maxbalanceplisio);
            sendmessage($from_id, "❌ حداقل مبلغ واریزی این روش پرداخت باید $mainbalanceplisio و حداکثر $maxbalanceplisio تومان باشد", null, 'HTML');
            rf_stop();
        }
        deletemessage($from_id, $message_id);
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], null, 'HTML');
        $dateacc = date('Y/m/d H:i:s');
        $randomString = bin2hex(random_bytes(5));
        $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method,id_invoice) VALUES (?,?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "Currency Rial 3";
        $stmt->bind_param("sssssss", $from_id, $randomString, $dateacc, $user['Processing_value'], $payment_Status, $Payment_Method, $invoice);
        $stmt->execute();
        $paylink = createInvoice($trxprice);
        if (!$paylink['success']) {
            $text_error = $paylink['message'];
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            $ErrorsLinkPayment = "
⭕️ یک کاربر قصد پرداخت داشت که ساخت لینک پرداخت  با خطا مواجه شده و به کاربر لینک داده نشد
✍️ دلیل خطا : $text_error
            
آیدی کابر : $from_id
روش پرداخت : $Payment_Method
نام کاربری کاربر : @$username";
            if (strlen($setting['Channel_Report'] ?? '') > 0) {
                telegram('sendmessage', [
                    'chat_id' => $setting['Channel_Report'],
                    'message_thread_id' => $errorreport,
                    'text' => $ErrorsLinkPayment,
                    'parse_mode' => "HTML"
                ]);
            }
            rf_stop();
        }
        update("Payment_report", "dec_not_confirmed", $paylink['data']['id'], "id_order", $randomString);
        $pricetoman = number_format($user['Processing_value'], 0);
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "💎 پرداخت", 'url' => "t.me/AvidTrx_Bot?start=" . $paylink['data']['id']]
                ],
            ]
        ]);
        $textnowpayments = "✅ تراکنش شما ایجاد شد
        
🛒 کد پیگیری:  <code>$randomString</code> 
💲 مبلغ تراکنش به تومان  : <code>$pricetoman</code> تومان


💢 لطفا به این نکات قبل از پرداخت توجه کنید 👇
        
❌ این تراکنش به مدت یک روز اعتبار دارد پس از آن امکان پرداخت این تراکنش امکان ندارد.        

✅ در صورت مشکل میتوانید با پشتیبانی در ارتباط باشید";
        $gethelp = select("PaySetting", "ValuePay", "NamePay", "helpiranpay3", "select")['ValuePay'];
        if ($gethelp != 2) {
            $data = json_decode($gethelp, true);
            if ($data['type'] == "text") {
                sendmessage($from_id, $data['text'], null, 'HTML');
            } elseif ($data['type'] == "photo") {
                sendphoto($from_id, $data['photoid'], null);
            } elseif ($data['type'] == "video") {
                sendvideo($from_id, $data['videoid'], null);
            }
        }
        sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
        step("getvoocherx", $from_id);
        savedata("clear", "id_payment", $randomString);
}
if (!$rf_get_step_payment_handled && ($datain == "digitaltron")) {
    $rf_get_step_payment_handled = true;
        $rates = requireTronRates(['TRX', 'USD']);
        if ($rates === null) {
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            rf_stop();
        }
        $trx = $rates['TRX'];
        $usd = $rates['USD'];
        $trxprice = round($user['Processing_value'] / $trx, 2);
        $usdprice = round($user['Processing_value'] / $usd, 2);
        if ($trxprice <= 1) {
            sendmessage($from_id, $textbotlang['users']['Balance']['changeto'], null, 'HTML');
            rf_stop();
        }
        $mainbalancedigitaltron = select("PaySetting", "ValuePay", "NamePay", "minbalancedigitaltron", "select")['ValuePay'];
        $maxbalancedigitaltron = select("PaySetting", "ValuePay", "NamePay", "maxbalancedigitaltron", "select")['ValuePay'];
        if ($user['Processing_value'] < $mainbalancedigitaltron || $user['Processing_value'] > $maxbalancedigitaltron) {
            $mainbalanceplisio = number_format($mainbalancedigitaltron);
            $maxbalanceplisio = number_format($maxbalancedigitaltron);
            sendmessage($from_id, "❌ حداقل مبلغ واریزی این روش پرداخت باید $mainbalanceplisio و حداکثر $maxbalanceplisio تومان باشد", null, 'HTML');
            rf_stop();
        }
        deletemessage($from_id, $message_id);
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d H:i:s');
        $randomString = bin2hex(random_bytes(5));
        $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method,id_invoice) VALUES (?,?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "arze digital offline";
        $stmt->bind_param("sssssss", $from_id, $randomString, $dateacc, $user['Processing_value'], $payment_Status, $Payment_Method, $invoice);
        $stmt->execute();
        $affilnecurrency = select("PaySetting", "*", "NamePay", "walletaddress", "select")['ValuePay'];
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "✅ ارسال لینک واریز یا تصویر واریزی", 'callback_data' => "sendresidarze-{$randomString}"]
                ]
            ]
        ]);
        $formatprice = number_format($user['Processing_value'], 0);
        $textnowpayments = "✅ تراکنش شما ایجاد شد

🛒 کد پیگیری: <code>$randomString</code>
🌐 شبکه: TRX
💳 آدرس ولت: <code>$affilnecurrency</code>
💲 مبلغ تراکنش: $trxprice TRX

📌 مبلغ $formatprice تومان را واریز پس از واریز دکمه زیر را  کلیک و رسید را ارسال نمایید

💢 لطفا به این نکات قبل از پرداخت توجه کنید 👇

🔸 در صورت اشتباه وارد کردن آدرس کیف پول، تراکنش تایید نمیشود و بازگشت وجه امکان پذیر نیست
🔹 مبلغ ارسالی نباید کمتر و یا بیشتر از مبلغ اعلام شده باشد
🔹 در صورت واریز بیش از مقدار گفته شده، امکان اضافه کردن تفاوت وجه وجود ندارد
🔹 هر تراکنش یک ساعت معتبر است و بعد از دریافت پیام منقضی شدن تراکنش به هیچ عنوان مبلغی به کیف پول ارسال نکنید

✅ در صورت مشکل میتوانید با پشتیبانی در ارتباط باشید";
        $gethelp = getPaySettingValue('helpofflinearze');
        if ($gethelp !== null && $gethelp != 2) {
            $data = json_decode($gethelp, true);
            if ($data['type'] == "text") {
                sendmessage($from_id, $data['text'], null, 'HTML');
            } elseif ($data['type'] == "photo") {
                sendphoto($from_id, $data['photoid'], null);
            } elseif ($data['type'] == "video") {
                sendvideo($from_id, $data['videoid'], null);
            }
        }
        $message_id =sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
        updatePaymentMessageId($message_id, $randomString);
}
