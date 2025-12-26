<?php
rf_set_module('admin/routes/11_step_removeprotocol__step_updatemethodusername__step_getnamecustom.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "removeprotocol")) {
    $rf_admin_handled = true;

    if (!in_array($text, $protocoldata)) {
        sendmessage($from_id, $textbotlang['Admin']['Protocol']['invalidProtocol'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Protocol']['RemovedProtocol'], $optionMarzban, 'HTML');
    $stmt = $pdo->prepare("DELETE FROM protocol WHERE NameProtocol = :protocol");
    $stmt->bindParam(':protocol', $text, PDO::PARAM_STR);
    $stmt->execute();
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "💡 روش ساخت نام کاربری" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $text_username = "⭕️ روش ساخت نام کاربری برای اکانت ها را از دکمه زیر انتخاب نمایید.
        
⚠️ در صورتی که کاربری نام کاربری نداشته باشه کلمه انتخابی توسط شما ثبت خواهد شد جای نام کاربری اعمال خواهد شد.
        
⚠️ در صورتی که نام کاربری وجود داشته باشه یک عدد رندوم به نام کاربری اضافه خواهد شد";
    sendmessage($from_id, $text_username, $MethodUsername, 'HTML');
    step('updatemethodusername', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "updatemethodusername")) {
    $rf_admin_handled = true;

    update("marzban_panel", "MethodUsername", $text, "name_panel", $user['Processing_value']);
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    if ($text == "متن دلخواه + عدد رندوم" || $text == "متن دلخواه + عدد ترتیبی" || $text == "متن دلخواه نماینده + عدد ترتیبی") {
        step('getnamecustom', $from_id);
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['customnamesend'], $backadmin, 'HTML');
        return;
    }
    if ($text == "نام کاربری + عدد به ترتیب") {
        step('getnamecustom', $from_id);
        sendmessage($from_id, "📌 در صورتی که کاربر نام کاربری نداشت چه اسمی ثبت شود؟", $backadmin, 'HTML');
        return;
    }
    outtypepanel($typepanel['type'], $textbotlang['Admin']['AlgortimeUsername']['SaveData']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnamecustom")) {
    $rf_admin_handled = true;

    if (!preg_match('/^\w{3,32}$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['invalidname'], $backadmin, 'html');
        return;
    }
    update("marzban_panel", "namecustom", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['savedname']);
    return;
}

if (!$rf_admin_handled && (($datain == "cartsetting" && $adminrulecheck['rule'] == "administrator") || $text == "▶️ بازگشت به منوی تظنیمات کارت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $CartManage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "💳 تنظیم شماره کارت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $textcart = "💳 شماره کارت خود را ارسال کنید

⚠️ توجه داشته باشید شما می توانید چندین شماره کارت تعریف کنید در صورت تعریف چندین شماره کارت به کاربر یک شماره کارت از بین شماره کارت ها رندوم نشان خواهد داد";
    sendmessage($from_id, $textcart, $backadmin, 'HTML');
    step('changecard', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "changecard")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, "❌شماره کارت باید حتما عدد باشد.", $backuser, 'HTML');
        return;
    }
    if (in_array($text, $listcard)) {
        sendmessage($from_id, "❌ شماره کارت در دیتابیس وجود دارد.", $backuser, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['SettingPayment']['getnamecard'], $backuser, 'HTML');
    update("user", "Processing_value", $text, "id", $from_id);
    step('getnamecard', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnamecard")) {
    $rf_admin_handled = true;

    try {
        if (function_exists('ensureCardNumberTableSupportsUnicode')) {
            ensureCardNumberTableSupportsUnicode();
        }

        $stmt = $connect->prepare("INSERT INTO card_number (cardnumber,namecard) VALUES (?,?)");
        $stmt->bind_param("ss", $user['Processing_value'], $text);
        $stmt->execute();
        $stmt->close();
        sendmessage($from_id, $textbotlang['Admin']['SettingPayment']['Savacard'], $CartManage, 'HTML');
        step('home', $from_id);
    } catch (\mysqli_sql_exception $e) {
        error_log('Failed to save card number: ' . $e->getMessage());
        if (stripos($e->getMessage(), 'Incorrect string value') !== false) {
            error_log('card_number insert failed due to charset mismatch. Please verify the table collation.');
        }
        sendmessage($from_id, "❌ ثبت شماره کارت ناموفق بود. لطفاً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.", $backadmin, 'HTML');
        step('home', $from_id);
    }
    return;
}

if (!$rf_admin_handled && ($datain == "plisiosetting" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $NowPaymentsManage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🧩 api plisio" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "apinowpayment")['ValuePay'];
    $textcart = "⚙️ api سایت plisio.net.io را ارسال نمایید
        
        api plisio :$PaySetting";
    sendmessage($from_id, $textcart, $backadmin, 'HTML');
    step('apinowpayment', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "apinowpayment")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $NowPaymentsManage, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "apinowpayment");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "iranpay1setting" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $Swapinokey, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "API NOWPAYMENT")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "marchent_tronseller")['ValuePay'];
    $texttronseller = "💳 API NOWPAMENT خود را دریافت و در این قسمت وارد کنید
        
 api فعلی شما : $PaySetting";
    sendmessage($from_id, $texttronseller, $backadmin, 'HTML');
    step('marchent_tronseller', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "marchent_tronseller")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $keyboardadmin, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "marchent_tronseller");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "zarinpeysetting" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 یک گزینه را انتخاب کنید", $keyboardzarinpey, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "aqayepardakhtsetting" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $aqayepardakht, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "zarinpalsetting" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 یک گزینه را انتخاب کنید", $keyboardzarinpal, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "تنظیم مرچنت آقای پرداخت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "merchant_id_aqayepardakht")['ValuePay'];
    $textaqayepardakht = "💳 مرچنت کد خود را ازآقای پرداخت دریافت و در این قسمت وارد کنید
        
مرچنت کد فعلی شما : $PaySetting";
    sendmessage($from_id, $textaqayepardakht, $backadmin, 'HTML');
    step('merchant_id_aqayepardakht', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "merchant_id_aqayepardakht")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $aqayepardakht, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "merchant_id_aqayepardakht");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "مرچنت زرین پال" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "merchant_zarinpal")['ValuePay'];
    $textaqayepardakht = "💳 مرچنت کد خود را از زرین پال دریافت و در این قسمت وارد کنید
        
مرچنت کد فعلی شما : $PaySetting";
    sendmessage($from_id, $textaqayepardakht, $backadmin, 'HTML');
    step('merchant_zarinpal', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "merchant_zarinpal")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $keyboardzarinpal, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "merchant_zarinpal");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗂 نام درگاه زرین پی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, " 📌 نام درگاه را ارسال نمايید", $backadmin, 'HTML');
    step("gettextzarinpey", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextzarinpey")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $keyboardzarinpey, 'HTML');
    update("textbot", "text", $text, "id_text", "zarinpey");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🔑 توکن زرین پی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $token = getPaySettingValue('token_zarinpey', '0');
    $message = "🔑 توکن دسترسی زرین پی خود را ارسال کنید.\n\nتوکن فعلی شما: {$token}";
    sendmessage($from_id, $message, $backadmin, 'HTML');
    step('token_zarinpey', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "token_zarinpey")) {
    $rf_admin_handled = true;

    update("PaySetting", "ValuePay", $text, "NamePay", "token_zarinpey");
    sendmessage($from_id, "✅ توکن با موفقیت ذخیره شد.", $keyboardzarinpey, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "💰 کش بک زرین پی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید کاربر پس از پرداخت چه درصدی به عنوان هدیه به حسابش واریز شود. ( برای غیرفعال کردن این قابلیت عدد صفر ارسال کنید)", $backadmin, 'HTML');
    step("getcashzarinpey", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcashzarinpey")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    update("PaySetting", "ValuePay", $text, "NamePay", "chashbackzarinpey");
    sendmessage($from_id, "✅ مبلغ با موفقیت ذخیره گردید.", $keyboardzarinpey, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🧑🏼‍💻 اموزش اتصال")) {
    $rf_admin_handled = true;

    $inlineKeyboard = json_encode([
        'inline_keyboard' => [
            [
                [
                    'text' => '📞 دریافت API  مشاوره',
                    'url' => 'https://t.me/MiladRajabi2002',
                ],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE);

    $message = "🚀 درگاه کارت‌به‌کارت خودکار\n\nدرگاه هوشمند ZarinPay اکنون در میرزا بات نسخه پرو فعال است!\nتراکنش‌ها با خواندن پیامک بانکی به‌صورت خودکار و لحظه‌ای تأیید می‌شوند ⚡\nبدون نیاز به تأیید دستی، سریع، دقیق و ایمن 💳";

    sendmessage($from_id, $message, $inlineKeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ حداقل مبلغ زرین پی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmainzarinpey", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmainzarinpey")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    update("PaySetting", "ValuePay", $text, "NamePay", "minbalancezarinpey");
    sendmessage($from_id, "✅ حداقل مبلغ واریزی تنظیم گردید.", $keyboardzarinpey, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ حداکثر مبلغ زرین پی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmaaxzarinpey", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmaaxzarinpey")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    update("PaySetting", "ValuePay", $text, "NamePay", "maxbalancezarinpey");
    sendmessage($from_id, "✅ حداکثر مبلغ واریزی تنظیم گردید.", $keyboardzarinpey, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش زرین پی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .\n۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید\n۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("helpzarinpey", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "helpzarinpey")) {
    $rf_admin_handled = true;

    if ($text) {
        if ((int) $text === 2) {
            update("PaySetting", "ValuePay", "0", "NamePay", "helpzarinpey");
        } else {
            $data = json_encode([
                'type' => 'text',
                'text' => $text,
            ], JSON_UNESCAPED_UNICODE);
            update("PaySetting", "ValuePay", $data, "NamePay", "helpzarinpey");
        }
    } elseif ($photo) {
        $data = json_encode([
            'type' => 'photo',
            'text' => $caption,
            'photoid' => $photoid,
        ], JSON_UNESCAPED_UNICODE);
        update("PaySetting", "ValuePay", $data, "NamePay", "helpzarinpey");
    } elseif ($video) {
        $data = json_encode([
            'type' => 'video',
            'text' => $caption,
            'videoid' => $videoid,
        ], JSON_UNESCAPED_UNICODE);
        update("PaySetting", "ValuePay", $data, "NamePay", "helpzarinpey");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $keyboardzarinpey, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == $textbotlang['Admin']['btnkeyboardadmin']['managementpanel'] && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getloc'], $json_list_marzban_panel, 'HTML');
    step('GetLocationEdit', $from_id);
    return;
}

