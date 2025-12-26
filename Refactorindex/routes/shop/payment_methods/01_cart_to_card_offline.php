<?php
rf_set_module('routes/shop/payment_methods/01_cart_to_card_offline.php');
if (!$rf_get_step_payment_handled && ($datain == "cart_to_offline")) {
    $rf_get_step_payment_handled = true;
        $PaySetting = select("PaySetting", "ValuePay", "NamePay", "statuscardautoconfirm", "select")['ValuePay'];
        $checkpay = mysqli_query($connect, "SELECT * FROM Payment_report WHERE id = '$from_id' AND payment_Status = 'Unpaid'");
        if (mysqli_num_rows($checkpay) != 0) {
            sendmessage($from_id, $textbotlang['Admin']['SettingPayment']['issetpay'], null, 'HTML');
            rf_stop();
        }
        $mainbalance = select("PaySetting", "ValuePay", "NamePay", "minbalancecart", "select")['ValuePay'];
        $maxbalance = select("PaySetting", "ValuePay", "NamePay", "maxbalancecart", "select")['ValuePay'];
        if ($user['Processing_value'] < $mainbalance || $user['Processing_value'] > $maxbalance) {
            $mainbalance = number_format($mainbalance);
            $maxbalance = number_format($maxbalance);
            sendmessage($from_id, "❌ حداقل مبلغ واریزی این روش پرداخت باید $mainbalance و حداکثر $maxbalance تومان باشد", null, 'HTML');
            rf_stop();
        }
        $cardQuery = mysqli_query($connect, "SELECT * FROM card_number  ORDER BY RAND() LIMIT 1");
        if ($cardQuery === false) {
            error_log('Failed to fetch card_number data: ' . mysqli_error($connect));
            sendmessage($from_id, "❌ خطای داخلی در بازیابی کارت بانکی رخ داد. لطفاً بعداً تلاش کنید.", null, 'HTML');
            rf_stop();
        }

        $card_info = mysqli_fetch_assoc($cardQuery);
        if (!$card_info || empty($card_info['cardnumber']) || empty($card_info['namecard'])) {
            sendmessage($from_id, "❌ کارت بانکی فعالی برای این روش پرداخت یافت نشد. لطفاً بعداً تلاش کنید یا با پشتیبانی تماس بگیرید.", null, 'HTML');
            mysqli_free_result($cardQuery);
            rf_stop();
        }

        $card_number = $card_info['cardnumber'];
        $PaySettingname = $card_info['namecard'];
        mysqli_free_result($cardQuery);
        $price_copy = $user['Processing_value'];
        if ($PaySetting == "onautoconfirm") {
            $random_number = rand(0, 2000);
            $user['Processing_value'] = intval($user['Processing_value']) + $random_number;
            if (in_array($user['Processing_value'], $pricepayment)) {
                $random_number = rand(0, 2000);
                $user['Processing_value'] = intval($user['Processing_value']) + $random_number;
            }
            $valueshow = "{$user['Processing_value']}0";
            $replacements = [
                '{price}' => $valueshow,
                '{card_number}' => $card_number,
                '{name_card}' => $PaySettingname,
            ];
            $price_copy = $valueshow;
            $textcart = strtr($datatextbot['text_cart_auto'], $replacements);
            update("user", "Processing_value", $user['Processing_value'], "id", $from_id);
        } else {
            $valueprice = number_format($user['Processing_value']);
            $replacements = [
                '{price}' => $valueprice,
                '{card_number}' => $card_number,
                '{name_card}' => $PaySettingname,
            ];
            $price_copy = intval($user['Processing_value'] . "0");
            $textcart = strtr($datatextbot['text_cart'], $replacements);
        }
        $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        $dateacc = date('Y/m/d H:i:s');
        $randomString = bin2hex(random_bytes(5));
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method,id_invoice) VALUES (?,?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "cart to cart";
        $stmt->bind_param("sssssss", $from_id, $randomString, $dateacc, $user['Processing_value'], $payment_Status, $Payment_Method, $invoice);
        $stmt->execute();
        deletemessage($from_id, $message_id);
        if ($setting['statuscopycart'] == "1") {
            $sendresidcart = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => "کپی شماره کارت", 'copy_text' => ["text" => $card_number]],
                        ['text' => "کپی مبلغ", 'copy_text' => ["text" => $price_copy]]
                    ],
                    [
                        ['text' => "✅ پرداخت کردم | ارسال رسید.", 'callback_data' => "sendresidcart-" . $randomString]
                    ]
                ]
            ]);
        } else {
            $sendresidcart = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => "✅ پرداخت کردم | ارسال رسید.", 'callback_data' => "sendresidcart-" . $randomString]
                    ]
                ]
            ]);
        }
        $gethelp = select("PaySetting", "ValuePay", "NamePay", "helpcart", "select")['ValuePay'];
        if ($gethelp != 2) {
            $data = json_decode($gethelp, true);
            if ($data['type'] == "text") {
                sendmessage($from_id, $data['text'], null, 'HTML');
            } elseif ($data['type'] == "photo") {
                sendphoto($from_id, $data['photoid'], $data['text']);
            } elseif ($data['type'] == "video") {
                sendvideo($from_id, $data['videoid'], $data['text']);
            }
        }
        $message_id = telegram('sendmessage', [
            'chat_id' => $from_id,
            'text' => $textcart,
            'reply_markup' => $sendresidcart,
            'parse_mode' => "html",
        ]);
        updatePaymentMessageId($message_id, $randomString);
}
if (!$rf_get_step_payment_handled && ($datain == "aqayepardakht")) {
    $rf_get_step_payment_handled = true;
        if ($user['Processing_value'] < 5000) {
            sendmessage($from_id, $textbotlang['users']['Balance']['zarinpal'], null, 'HTML');
            rf_stop();
        }
        $mainbalance = select("PaySetting", "ValuePay", "NamePay", "minbalanceaqayepardakht", "select")['ValuePay'];
        $maxbalance = select("PaySetting", "ValuePay", "NamePay", "maxbalanceaqayepardakht", "select")['ValuePay'];
        if ($user['Processing_value'] < $mainbalance || $user['Processing_value'] > $maxbalance) {
            $mainbalance = number_format($mainbalance);
            $maxbalance = number_format($maxbalance);
            sendmessage($from_id, "❌ حداقل مبلغ واریزی این روش پرداخت باید $mainbalance و حداکثر $maxbalance تومان باشد", null, 'HTML');
            rf_stop();
        }
        deletemessage($from_id, $message_id);
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        $dateacc = date('Y/m/d H:i:s');
        $randomString = bin2hex(random_bytes(5));
        $pay = createPayaqayepardakht($user['Processing_value'], $randomString);
        if ($pay['status'] != "success") {
            $text_error = json_encode($pay);
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            $ErrorsLinkPayment = "⭕️ خطا در ساخت لینک اقای پردات
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
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method,id_invoice) VALUES (?,?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "aqayepardakht";
        $stmt->bind_param("sssssss", $from_id, $randomString, $dateacc, $user['Processing_value'], $payment_Status, $Payment_Method, $invoice);
        $stmt->execute();
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://panel.aqayepardakht.ir/startpay/" . $pay['transid']],
                ]
            ]
        ]);
        $price_format = number_format($user['Processing_value'], 0);
        $textnowpayments = "✅ فاکتور پرداخت ایجاد شد.\n\n🔢 شماره فاکتور : $randomString
💰 مبلغ فاکتور : $price_format تومان

❌ این تراکنش به مدت ۳۰ دقیقه (نیم ساعت) اعتبار دارد و پس از آن امکان پرداخت این تراکنش امکان‌پذیر نیست.

📌لطفاً پس از پرداخت و موفق بودن تراکنش ، کمی صبر کنید تا پیام پرداخت موفق در سایت ما دریافت کنید. در غیراینصورت اکانت شما شارژ نخواهد شد.

جهت پرداخت از دکمه زیر استفاده کنید👇🏻";
        $gethelp = select("PaySetting", "ValuePay", "NamePay", "helpaqayepardakht", "select")['ValuePay'];
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
if (!$rf_get_step_payment_handled && ($datain == "zarinpey")) {
    $rf_get_step_payment_handled = true;
        $minbalance = select("PaySetting", "ValuePay", "NamePay", "minbalancezarinpey", "select")['ValuePay'];
        $maxbalance = select("PaySetting", "ValuePay", "NamePay", "maxbalancezarinpey", "select")['ValuePay'];

        if ($user['Processing_value'] < $minbalance || $user['Processing_value'] > $maxbalance) {
            $minbalance = number_format($minbalance);
            $maxbalance = number_format($maxbalance);
            sendmessage($from_id, sprintf($textbotlang['users']['Balance']['zarinpey'], $minbalance, $maxbalance), null, 'HTML');
            rf_stop();
        }

        deletemessage($from_id, $message_id);
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $randomString = bin2hex(random_bytes(5));
        $pay = createPayZarinpey($user['Processing_value'], $randomString, $from_id);

        if (empty($pay['success'])) {
            $error_text = $pay['message'] ?? $textbotlang['users']['Balance']['errorLinkPayment'];
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            if (strlen($setting['Channel_Report'] ?? '') > 0) {
                $ErrorsLinkPayment = "⭕️ خطا در ساخت لینک زرین پی\n✍️ دلیل خطا : {$error_text}\n\nآیدی کابر : $from_id\nنام کاربری کاربر : @$username";
                telegram('sendmessage', [
                    'chat_id' => $setting['Channel_Report'],
                    'message_thread_id' => $errorreport,
                    'text' => $ErrorsLinkPayment,
                    'parse_mode' => 'HTML'
                ]);
            }
            rf_stop();
        }

        $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        $dateacc = date('Y/m/d H:i:s');
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method,id_invoice,dec_not_confirmed) VALUES (?,?,?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "zarinpay";
        $pendingMetadata = [
            'gateway' => 'zarinpay',
            'authority' => $pay['authority'] ?? null,
            'amount_rial' => $pay['amount_rial'] ?? null,
        ];
        $pendingMetadata = array_filter(
            $pendingMetadata,
            static function ($value, $key) {
                if ($key === 'gateway') {
                    return true;
                }

                return !($value === null || $value === '');
            },
            ARRAY_FILTER_USE_BOTH
        );

        if (!empty($pendingMetadata)) {
            $pendingNote = json_encode($pendingMetadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $pendingNote = (string) ($pay['authority'] ?? '');
        }

        $stmt->bind_param("ssssssss", $from_id, $randomString, $dateacc, $user['Processing_value'], $payment_Status, $Payment_Method, $invoice, $pendingNote);
        $stmt->execute();

        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => $pay['payment_link'] ?? 'https://zarinpay.me'],
                ]
            ]
        ]);

        $price_format = number_format($user['Processing_value'], 0);
        $textnowpayments = "✅ فاکتور پرداخت ایجاد شد.\n\n🔢 شماره فاکتور : $randomString\n💰 مبلغ فاکتور : $price_format تومان\n\n❌ این تراکنش به مدت ۳۰ دقیقه (نیم ساعت) اعتبار دارد و پس از آن امکان پرداخت این تراکنش امکان‌پذیر نیست.\n\n📌لطفاً پس از پرداخت و موفق بودن تراکنش ، کمی صبر کنید تا پیام پرداخت موفق در سایت ما دریافت کنید. در غیراینصورت اکانت شما شارژ نخواهد شد.\n\nجهت پرداخت از دکمه زیر استفاده کنید👇🏻";

        $gethelp = getPaySettingValue('helpzarinpey', '2');
        if ($gethelp != 2) {
            $data = json_decode($gethelp, true);
            if (is_array($data)) {
                if ($data['type'] == "text") {
                    sendmessage($from_id, $data['text'], null, 'HTML');
                } elseif ($data['type'] == "photo") {
                    sendphoto($from_id, $data['photoid'], null);
                } elseif ($data['type'] == "video") {
                    sendvideo($from_id, $data['videoid'], null);
                }
            }
        }

        $message_id = sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
        updatePaymentMessageId($message_id, $randomString);
}
if (!$rf_get_step_payment_handled && ($datain == "zarinpal")) {
    $rf_get_step_payment_handled = true;
        if ($user['Processing_value'] < 5000) {
            sendmessage($from_id, $textbotlang['users']['Balance']['zarinpal'], null, 'HTML');
            rf_stop();
        }
        $mainbalance = select("PaySetting", "ValuePay", "NamePay", "minbalancezarinpal", "select")['ValuePay'];
        $maxbalance = select("PaySetting", "ValuePay", "NamePay", "maxbalancezarinpal", "select")['ValuePay'];
        if ($user['Processing_value'] < $mainbalance || $user['Processing_value'] > $maxbalance) {
            $mainbalance = number_format($mainbalance);
            $maxbalance = number_format($maxbalance);
            sendmessage($from_id, "❌ حداقل مبلغ واریزی این روش پرداخت باید $mainbalance و حداکثر $maxbalance تومان باشد", null, 'HTML');
            rf_stop();
        }
        deletemessage($from_id, $message_id);
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $randomString = bin2hex(random_bytes(5));
        $pay = createPayZarinpal($user['Processing_value'], $randomString);
        if ($pay['data']['code'] != 100) {
            $text_error = json_encode($pay['errors']);
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            $ErrorsLinkPayment = "⭕️ خطا در ساخت لینک زرین پال
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
        $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        $dateacc = date('Y/m/d H:i:s');
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method,id_invoice,dec_not_confirmed) VALUES (?,?,?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "zarinpal";
        $stmt->bind_param("ssssssss", $from_id, $randomString, $dateacc, $user['Processing_value'], $payment_Status, $Payment_Method, $invoice, $pay['data']['authority']);
        $stmt->execute();
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://www.zarinpal.com/pg/StartPay/" . $pay['data']['authority']],
                ]
            ]
        ]);
        $price_format = number_format($user['Processing_value'], 0);
        $textnowpayments = "
✅ فاکتور پرداخت ایجاد شد.
            
🔢 شماره فاکتور : $randomString
💰 مبلغ فاکتور : $price_format تومان

❌ این تراکنش به مدت یک روز اعتبار دارد پس از آن امکان پرداخت این تراکنش امکان ندارد.        

📌لطفاً پس از پرداخت و موفق بودن تراکنش ، کمی صبر کنید تا پیام پرداخت موفق در سایت ما دریافت کنید. در غیراینصورت اکانت شما شارژ نخواهد شد.

جهت پرداخت از دکمه زیر استفاده کنید👇🏻";
        $gethelp = select("PaySetting", "ValuePay", "NamePay", "helpzarinpal", "select")['ValuePay'];
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
if (!$rf_get_step_payment_handled && ($datain == "plisio")) {
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
        if ($usdprice <= 1) {
            sendmessage($from_id, $textbotlang['users']['Balance']['nowpayments'], null, 'HTML');
            rf_stop();
        }
        $mainbalanceplisio = select("PaySetting", "ValuePay", "NamePay", "minbalanceplisio", "select")['ValuePay'];
        $maxbalanceplisio = select("PaySetting", "ValuePay", "NamePay", "maxbalanceplisio", "select")['ValuePay'];
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
        $pay = plisio($randomString, $trxprice);
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method,id_invoice,dec_not_confirmed) VALUES (?,?,?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "plisio";
        $stmt->bind_param("ssssssss", $from_id, $randomString, $dateacc, $user['Processing_value'], $payment_Status, $Payment_Method, $invoice, $pay['txn_id']);
        $stmt->execute();
        if (isset($pay['message'])) {
            $text_error = $pay['message'];
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

جهت پرداخت از دکمه زیر استفاده👇🏻";
        $gethelp = select("PaySetting", "ValuePay", "NamePay", "helpplisio", "select")['ValuePay'];
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
