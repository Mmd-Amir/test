<?php
rf_set_module('admin/routes/24_step_getpricecashback__step_getagent__editpayment.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($text == "💎 مالی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $cartotcart = getPaySettingValue('Cartstatus', 'offcard');
    $plisio = getPaySettingValue('nowpaymentstatus', 'offnowpayment');
    $arzireyali1 = getPaySettingValue('statusSwapWallet', 'offSwapinoBot');
    if ($arzireyali1 != "onSwapinoBot" && $arzireyali1 != "offSwapinoBot") {
        update("PaySetting", "ValuePay", "onSwapinoBot", "NamePay", "statusSwapWallet");
        $arzireyali1 = getPaySettingValue('statusSwapWallet', 'offSwapinoBot');
    }
    $arzireyali2 = getPaySettingValue('statustarnado', 'offternado');
    $arzireyali3 = getPaySettingValue('statusiranpay3', 'offiranpay3');
    $aqayepardakht = getPaySettingValue('statusaqayepardakht', 'offaqayepardakht');
    $zarinpal = getPaySettingValue('zarinpalstatus', 'offzarinpal');
    $zarinpey = getPaySettingValue('zarinpeystatus', 'offzarinpey');
    $affilnecurrency = getPaySettingValue('digistatus', 'offdigi');
    $paymentstatussnotverify = getPaySettingValue('paymentstatussnotverify', 'offpaymentstatus');
    $paymentsstartelegram = getPaySettingValue('statusstar', '0');
    $payment_status_nowpayment = getPaySettingValue('statusnowpayment', '0');
    $cartotcartstatus = [
        'oncard' => $textbotlang['Admin']['Status']['statuson'],
        'offcard' => $textbotlang['Admin']['Status']['statusoff']
    ][$cartotcart];
    $plisiostatus = [
        'onnowpayment' => $textbotlang['Admin']['Status']['statuson'],
        'offnowpayment' => $textbotlang['Admin']['Status']['statusoff']
    ][$plisio];
    $arzireyali1status = [
        'onSwapinoBot' => $textbotlang['Admin']['Status']['statuson'],
        'offSwapinoBot' => $textbotlang['Admin']['Status']['statusoff']
    ][$arzireyali1];
    $arzireyali2status = [
        'onternado' => $textbotlang['Admin']['Status']['statuson'],
        'offternado' => $textbotlang['Admin']['Status']['statusoff']
    ][$arzireyali2];
    $aqayepardakhtstatus = [
        'onaqayepardakht' => $textbotlang['Admin']['Status']['statuson'],
        'offaqayepardakht' => $textbotlang['Admin']['Status']['statusoff']
    ][$aqayepardakht];
    $zarinpalstatus = [
        'onzarinpal' => $textbotlang['Admin']['Status']['statuson'],
        'offzarinpal' => $textbotlang['Admin']['Status']['statusoff']
    ][$zarinpal];
    $zarinpeystatus = [
        'onzarinpey' => $textbotlang['Admin']['Status']['statuson'],
        'offzarinpey' => $textbotlang['Admin']['Status']['statusoff']
    ][$zarinpey];
    $affilnecurrencystatus = [
        'ondigi' => $textbotlang['Admin']['Status']['statuson'],
        'offdigi' => $textbotlang['Admin']['Status']['statusoff']
    ][$affilnecurrency];
    $arzireyali3text = [
        'oniranpay3' => $textbotlang['Admin']['Status']['statuson'],
        'offiranpay3' => $textbotlang['Admin']['Status']['statusoff']
    ][$arzireyali3];
    $paymentstar = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$paymentsstartelegram];
    $now_payment_status = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$payment_status_nowpayment];
    $Bot_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "عملیات", 'callback_data' => "actions"],
                ['text' => $textbotlang['Admin']['Status']['statussubject'], 'callback_data' => "subjectde"],
                ['text' => $textbotlang['Admin']['Status']['subject'], 'callback_data' => "subject"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "cartsetting"],
                ['text' => $cartotcartstatus, 'callback_data' => "editpayment-Cartstatus-$cartotcart"],
                ['text' => "🔌 کارت به کارت", 'callback_data' => "carttocart"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "plisiosetting"],
                ['text' => $plisiostatus, 'callback_data' => "editpayment-plisio-$plisio"],
                ['text' => "📌 plisio", 'callback_data' => "plisio"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "nowpaymentsetting"],
                ['text' => $now_payment_status, 'callback_data' => "editpayment-nowpayment-$payment_status_nowpayment"],
                ['text' => "📌 nowpayment", 'callback_data' => "nowpayment"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "iranpay1setting"],
                ['text' => $arzireyali1status, 'callback_data' => "editpayment-arzireyali1-$arzireyali1"],
                ['text' => "📌 ارزی ریالی اول", 'callback_data' => "arzireyali1"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "iranpay2setting"],
                ['text' => $arzireyali2status, 'callback_data' => "editpayment-arzireyali2-$arzireyali2"],
                ['text' => "📌 ارزی ریالی دوم", 'callback_data' => "arzireyali2"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "iranpay3setting"],
                ['text' => $arzireyali3text, 'callback_data' => "editpayment-oniranpay3-$arzireyali3"],
                ['text' => "📌ارزی ریالی سوم", 'callback_data' => "oniranpay3"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "zarinpeysetting"],
                ['text' => $zarinpeystatus, 'callback_data' => "editpayment-zarinpey-$zarinpey"],
                ['text' => "🟠 زرین پی", 'callback_data' => "zarinpey"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "aqayepardakhtsetting"],
                ['text' => $aqayepardakhtstatus, 'callback_data' => "editpayment-aqayepardakht-$aqayepardakht"],
                ['text' => "🔵 آقای پرداخت", 'callback_data' => "aqayepardakht"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "zarinpalsetting"],
                ['text' => $zarinpalstatus, 'callback_data' => "editpayment-zarinpal-$zarinpal"],
                ['text' => "🟡 زرین پال", 'callback_data' => "zarinpal"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "affilnecurrencysetting"],
                ['text' => $affilnecurrencystatus, 'callback_data' => "editpayment-affilnecurrency-$affilnecurrency"],
                ['text' => "💵ارزی آفلاین", 'callback_data' => "affilnecurrency"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "startelegram"],
                ['text' => $paymentstar, 'callback_data' => "editpayment-startelegram-$paymentsstartelegram"],
                ['text' => "💫Star Telegram", 'callback_data' => "none"],
            ],
            [
                ['text' => "⬆️ حداکثر شارژ موجودی", 'callback_data' => "maxbalanceaccount"],
                ['text' => "⬇️ حداقل شارژ موجودی", 'callback_data' => "mainbalanceaccount"],
            ],
            [
                ['text' => "آدرس ولت", 'callback_data' => "walletaddress"],
            ],
            [
                ['text' => "❌ بستن", 'callback_data' => 'close_stat']
            ],
        ]
    ]);
    sendmessage($from_id, "📌 از لیست زیر میتوانید درگاه ها را مدیریت کنید.

⚠️ تیم میرزا هیچ تضمینی برای درگاه ها نخواهد داشت و استفاده  و تمامی مسئولیت ها به عهده شما می باشد", $Bot_Status, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🎁 کش بک تمدید" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 مقدار درصدی که می خواهید حساب کاربر بعد از تمدید به عنوان هدیه شارژ شود را ارسال کنید.
⚠️ در صورتی که میخواهید غیرفعال باشد عدد 0 را ارسال کنید", $backadmin, 'HTML');
    step('getpricecashback', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpricecashback")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    savedata("clear", "price_cashback", $text);
    sendmessage($from_id, "📌 نوع کاربری را انتخاب نمایید
f
n
n2", $backadmin, 'HTML');
    step('getagent', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getagent")) {
    $rf_admin_handled = true;

    if (!in_array($text, ['f', 'n', 'n2'])) {
        sendmessage($from_id, "❌ گروه کاربری نامعتبر است", $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    if ($text == "f") {
        update("shopSetting", "value", $userdata['price_cashback'], "Namevalue", "chashbackextend");
    } else {
        $shop_cashbackagent = json_decode(select("shopSetting", "*", "Namevalue", "chashbackextend_agent")['value'], true);
        $shop_cashbackagent[$text] = $userdata['price_cashback'];
        update("shopSetting", "value", json_encode($shop_cashbackagent), "Namevalue", "chashbackextend_agent");
    }
    sendmessage($from_id, "✅ مبلغ با موفقیت تنظیم شد", $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/^editpayment-(.*)-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $type = $dataget[1];
    $value = $dataget[2];
    if ($type == "Cartstatus") {
        if ($value == "oncard") {
            $valuenew = "offcard";
        } else {
            $valuenew = "oncard";
        }
        update("PaySetting", "ValuePay", $valuenew, "NamePay", "Cartstatus");
    } elseif ($type == "plisio") {
        if ($value == "onnowpayment") {
            $valuenew = "offnowpayment";
        } else {
            $valuenew = "onnowpayment";
        }
        update("PaySetting", "ValuePay", $valuenew, "NamePay", "nowpaymentstatus");
    } elseif ($type == "arzireyali1") {
        if ($value == "onSwapinoBot") {
            $valuenew = "offSwapinoBot";
        } else {
            $valuenew = "onSwapinoBot";
        }
        update("PaySetting", "ValuePay", $valuenew, "NamePay", "statusSwapWallet");
    } elseif ($type == "arzireyali2") {
        if ($value == "onternado") {
            $valuenew = "offternado";
        } else {
            $valuenew = "onternado";
        }
        update("PaySetting", "ValuePay", $valuenew, "NamePay", "statustarnado");
    } elseif ($type == "aqayepardakht") {
        if ($value == "onaqayepardakht") {
            $valuenew = "offaqayepardakht";
        } else {
            $valuenew = "onaqayepardakht";
        }
        update("PaySetting", "ValuePay", $valuenew, "NamePay", "statusaqayepardakht");
    } elseif ($type == "zarinpey") {
        if ($value == "onzarinpey") {
            $valuenew = "offzarinpey";
        } else {
            $valuenew = "onzarinpey";
        }
        update("PaySetting", "ValuePay", $valuenew, "NamePay", "zarinpeystatus");
    } elseif ($type == "zarinpal") {
        if ($value == "onzarinpal") {
            $valuenew = "offzarinpal";
        } else {
            $valuenew = "onzarinpal";
        }
        update("PaySetting", "ValuePay", $valuenew, "NamePay", "zarinpalstatus");
    } elseif ($type == "affilnecurrency") {
        if ($value == "ondigi") {
            $valuenew = "offdigi";
        } else {
            $valuenew = "ondigi";
        }
        update("PaySetting", "ValuePay", $valuenew, "NamePay", "digistatus");
    } elseif ($type == "oniranpay3") {
        if ($value == "oniranpay3") {
            $valuenew = "offiranpay3";
        } else {
            $valuenew = "oniranpay3";
        }
        update("PaySetting", "ValuePay", $valuenew, "NamePay", "statusiranpay3");
    } elseif ($type == "startelegram") {
        if ($value == "1") {
            $valuenew = "0";
        } else {
            $valuenew = "1";
        }
        update("PaySetting", "ValuePay", $valuenew, "NamePay", "statusstar");
    } elseif ($type == "nowpayment") {
        if ($value == "1") {
            $valuenew = "0";
        } else {
            $valuenew = "1";
        }
        update("PaySetting", "ValuePay", $valuenew, "NamePay", "statusnowpayment");
    }
    $zarinpal = getPaySettingValue('zarinpalstatus', 'offzarinpal');
    $cartotcart = getPaySettingValue('Cartstatus', 'offcard');
    $plisio = getPaySettingValue('nowpaymentstatus', 'offnowpayment');
    $arzireyali1 = getPaySettingValue('statusSwapWallet', 'offSwapinoBot');
    $arzireyali2 = getPaySettingValue('statustarnado', 'offternado');
    $aqayepardakht = getPaySettingValue('statusaqayepardakht', 'offaqayepardakht');
    $zarinpey = getPaySettingValue('zarinpeystatus', 'offzarinpey');
    $affilnecurrency = getPaySettingValue('digistatus', 'offdigi');
    $arzireyali3 = getPaySettingValue('statusiranpay3', 'offiranpay3');
    $paymentstatussnotverify = getPaySettingValue('paymentstatussnotverify', 'offpaymentstatus');
    $paymentsstartelegram = getPaySettingValue('statusstar', '0');
    $payment_status_nowpayment = getPaySettingValue('statusnowpayment', '0');
    $cartotcartstatus = [
        'oncard' => $textbotlang['Admin']['Status']['statuson'],
        'offcard' => $textbotlang['Admin']['Status']['statusoff']
    ][$cartotcart];
    $plisiostatus = [
        'onnowpayment' => $textbotlang['Admin']['Status']['statuson'],
        'offnowpayment' => $textbotlang['Admin']['Status']['statusoff']
    ][$plisio];
    $arzireyali1status = [
        'onSwapinoBot' => $textbotlang['Admin']['Status']['statuson'],
        'offSwapinoBot' => $textbotlang['Admin']['Status']['statusoff']
    ][$arzireyali1];
    $arzireyali2status = [
        'onternado' => $textbotlang['Admin']['Status']['statuson'],
        'offternado' => $textbotlang['Admin']['Status']['statusoff']
    ][$arzireyali2];
    $aqayepardakhtstatus = [
        'onaqayepardakht' => $textbotlang['Admin']['Status']['statuson'],
        'offaqayepardakht' => $textbotlang['Admin']['Status']['statusoff']
    ][$aqayepardakht];
    $zarinpeystatus = [
        'onzarinpey' => $textbotlang['Admin']['Status']['statuson'],
        'offzarinpey' => $textbotlang['Admin']['Status']['statusoff']
    ][$zarinpey];
    $zarinpalstatus = [
        'onzarinpal' => $textbotlang['Admin']['Status']['statuson'],
        'offzarinpal' => $textbotlang['Admin']['Status']['statusoff']
    ][$zarinpal];
    $affilnecurrencystatus = [
        'ondigi' => $textbotlang['Admin']['Status']['statuson'],
        'offdigi' => $textbotlang['Admin']['Status']['statusoff']
    ][$affilnecurrency];
    $arzireyali3text = [
        'oniranpay3' => $textbotlang['Admin']['Status']['statuson'],
        'offiranpay3' => $textbotlang['Admin']['Status']['statusoff']
    ][$arzireyali3];
    $paymentstar = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$paymentsstartelegram];
    $now_payment_status = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$payment_status_nowpayment];
    $Bot_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "عملیات", 'callback_data' => "actions"],
                ['text' => $textbotlang['Admin']['Status']['statussubject'], 'callback_data' => "subjectde"],
                ['text' => $textbotlang['Admin']['Status']['subject'], 'callback_data' => "subject"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "cartsetting"],
                ['text' => $cartotcartstatus, 'callback_data' => "editpayment-Cartstatus-$cartotcart"],
                ['text' => "🔌 کارت به کارت", 'callback_data' => "carttocart"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "plisiosetting"],
                ['text' => $plisiostatus, 'callback_data' => "editpayment-plisio-$plisio"],
                ['text' => "📌 plisio", 'callback_data' => "plisio"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "nowpaymentsetting"],
                ['text' => $now_payment_status, 'callback_data' => "editpayment-nowpayment-$payment_status_nowpayment"],
                ['text' => "📌 nowpayment", 'callback_data' => "nowpayment"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "iranpay1setting"],
                ['text' => $arzireyali1status, 'callback_data' => "editpayment-arzireyali1-$arzireyali1"],
                ['text' => "📌 ارزی ریالی اول", 'callback_data' => "arzireyali1"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "iranpay2setting"],
                ['text' => $arzireyali2status, 'callback_data' => "editpayment-arzireyali2-$arzireyali2"],
                ['text' => "📌 ارزی ریالی دوم", 'callback_data' => "arzireyali2"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "iranpay3setting"],
                ['text' => $arzireyali3text, 'callback_data' => "editpayment-oniranpay3-$arzireyali3"],
                ['text' => "📌ارزی ریالی سوم", 'callback_data' => "oniranpay3"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "zarinpeysetting"],
                ['text' => $zarinpeystatus, 'callback_data' => "editpayment-zarinpey-$zarinpey"],
                ['text' => "🟠 زرین پی", 'callback_data' => "zarinpey"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "aqayepardakhtsetting"],
                ['text' => $aqayepardakhtstatus, 'callback_data' => "editpayment-aqayepardakht-$aqayepardakht"],
                ['text' => "🔵 آقای پرداخت", 'callback_data' => "aqayepardakht"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "zarinpalsetting"],
                ['text' => $zarinpalstatus, 'callback_data' => "editpayment-zarinpal-$zarinpal"],
                ['text' => "🟡 زرین پال", 'callback_data' => "zarinpal"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "affilnecurrencysetting"],
                ['text' => $affilnecurrencystatus, 'callback_data' => "editpayment-affilnecurrency-$affilnecurrency"],
                ['text' => "💵ارزی آفلاین", 'callback_data' => "affilnecurrency"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "startelegram"],
                ['text' => $paymentstar, 'callback_data' => "editpayment-startelegram-$paymentsstartelegram"],
                ['text' => "💫Star Telegram", 'callback_data' => "none"],
            ],
            [
                ['text' => "⬆️ حداکثر شارژ موجودی", 'callback_data' => "maxbalanceaccount"],
                ['text' => "⬇️ حداقل شارژ موجودی", 'callback_data' => "mainbalanceaccount"],
            ],
            [
                ['text' => "آدرس ولت", 'callback_data' => "walletaddress"],
            ],
            [
                ['text' => "❌ بستن", 'callback_data' => 'close_stat']
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, "📌 از لیست زیر میتوانید درگاه ها را مدیریت کنید.

⚠️ تیم میرزا هیچ تضمینی برای درگاه ها نخواهد داشت و استفاده  و تمامی مسئولیت ها به عهده شما می باشد", $Bot_Status);
    return;
}

if (!$rf_admin_handled && ($text == "💰 کش بک کارت به کارت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید کاربر پس از پرداخت چه درصدی به عنوان هدیه به حسابش واریز شود. ( برای غیرفعال کردن این قابلیت عدد صفر ارسال کنید)", $backadmin, 'HTML');
    step("getcashcart", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcashcart")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "chashbackcart");
    return;
}

if (!$rf_admin_handled && ($text == "💰 کش بک آقای پرداخت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید کاربر پس از پرداخت چه درصدی به عنوان هدیه به حسابش واریز شود. ( برای غیرفعال کردن این قابلیت عدد صفر ارسال کنید)", $backadmin, 'HTML');
    step("getcashahaypar", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcashahaypar")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "chashbackaqaypardokht");
    return;
}

if (!$rf_admin_handled && ($text == "💰 کش بک ارزی ریالی دوم")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید کاربر پس از پرداخت چه درصدی به عنوان هدیه به حسابش واریز شود. ( برای غیرفعال کردن این قابلیت عدد صفر ارسال کنید)", $backadmin, 'HTML');
    step("getcashiranpay2", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcashiranpay2")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ با موفقیت ذخیره گردید.", $trnado, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "chashbackiranpay2");
    return;
}

if (!$rf_admin_handled && ($text == "💰 کش بک ارزی ریالی سوم")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید کاربر پس از پرداخت چه درصدی به عنوان هدیه به حسابش واریز شود. ( برای غیرفعال کردن این قابلیت عدد صفر ارسال کنید)", $backadmin, 'HTML');
    step("getcashiranpay4", $from_id);
    return;
}

