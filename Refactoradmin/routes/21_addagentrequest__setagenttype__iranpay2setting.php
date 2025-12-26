<?php
rf_set_module('admin/routes/21_addagentrequest__setagenttype__iranpay2setting.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && (preg_match('/addagentrequest_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    $request_agent = select("Requestagent", "*", "id", $id_user, "select");
    if (!$request_agent) {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "درخواست مورد نظر یافت نشد.",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    if ($request_agent['status'] == "reject" || $request_agent['status'] == "accept") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "این درخواست توسط ادمین دیگری بررسی شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $defaultAgentType = 'n';
    $agentTypeLabels = [
        'n' => 'نماینده عادی',
        'n2' => 'نماینده پیشرفته',
    ];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE Requestagent SET status = :status, type = :type WHERE id = :id AND status = :expected_status");
        $stmt->execute([
            ':status' => 'accept',
            ':type' => $defaultAgentType,
            ':id' => $id_user,
            ':expected_status' => 'waiting',
        ]);

        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => "این درخواست توسط ادمین دیگری بررسی شده است",
                'show_alert' => true,
                'cache_time' => 5,
            ));
            return;
        }

        $stmtUser = $pdo->prepare("UPDATE user SET agent = :agent, expire = NULL WHERE id = :id");
        $stmtUser->execute([
            ':agent' => $defaultAgentType,
            ':id' => $id_user,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    sendmessage($id_user, "✅ کاربر گرامی با درخواست نمایندگی شما موافقت و شما نماینده شدید.", null, 'HTML');
    sendmessage($from_id, $textbotlang['Admin']['agent']['useragented'], $keyboardadmin, 'HTML');
    $agentTypeButtons = [];
    foreach ($agentTypeLabels as $typeCode => $label) {
        $buttonText = ($typeCode === $defaultAgentType ? "✅ " : "") . $label;
        $agentTypeButtons[] = [
            'text' => $buttonText,
            'callback_data' => "setagenttype_{$typeCode}_{$id_user}"
        ];
    }
    $keyboardreject = json_encode([
        'inline_keyboard' => [
            [['text' => "✅درخواست تایید شده.", 'callback_data' => "accept"]],
            $agentTypeButtons,
            [['text' => "⏱️ زمان انقضا نمایندگی", 'callback_data' => 'expireset_' . $id_user]],
            [['text' => "مدیریت کاربر", 'callback_data' => 'manageuser_' . $id_user]]
        ]
    ], JSON_UNESCAPED_UNICODE);
    $textrequestagent = "📣 یک کاربر درخواست نمایندگی ثبت کرده لطفا اطلاعات را بررسی و وضعیت را مشخص کنید.\n\nآیدی عددی : $id_user\nنام کاربری : {$request_agent['username']}\nتوضیحات :  {$request_agent['Description']} ";
    $textrequestagent .= "\nوضعیت: تایید شد ({$agentTypeLabels[$defaultAgentType]})";
    $textrequestagent .= "\nبرای تغییر نوع نماینده از دکمه‌های زیر استفاده کنید.";
    Editmessagetext($from_id, $message_id, $textrequestagent, $keyboardreject);
    telegram('answerCallbackQuery', array(
        'callback_query_id' => $callback_query_id,
        'text' => "درخواست تایید شد و نماینده عادی فعال شد.",
        'show_alert' => false,
        'cache_time' => 5,
    ));
    return;
}

if (!$rf_admin_handled && (preg_match('/^setagenttype_(n|n2)_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $selectedType = $datagetr[1];
    $id_user = $datagetr[2];
    $agentTypeLabels = [
        'n' => 'نماینده عادی',
        'n2' => 'نماینده پیشرفته',
    ];
    if (!array_key_exists($selectedType, $agentTypeLabels)) {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['Admin']['agent']['invalidtypeagent'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    update("user", "agent", $selectedType, "id", $id_user);
    update("Requestagent", "type", $selectedType, "id", $id_user);
    $request_agent = select("Requestagent", "*", "id", $id_user, "select");
    if ($request_agent) {
        $agentTypeButtons = [];
        foreach ($agentTypeLabels as $typeCode => $label) {
            $buttonText = ($typeCode === $selectedType ? "✅ " : "") . $label;
            $agentTypeButtons[] = [
                'text' => $buttonText,
                'callback_data' => "setagenttype_{$typeCode}_{$id_user}"
            ];
        }
        $keyboardreject = json_encode([
            'inline_keyboard' => [
                [['text' => "✅درخواست تایید شده.", 'callback_data' => "accept"]],
                $agentTypeButtons,
                [['text' => "⏱️ زمان انقضا نمایندگی", 'callback_data' => 'expireset_' . $id_user]],
                [['text' => "مدیریت کاربر", 'callback_data' => 'manageuser_' . $id_user]]
            ]
        ], JSON_UNESCAPED_UNICODE);
        $textrequestagent = "📣 یک کاربر درخواست نمایندگی ثبت کرده لطفا اطلاعات را بررسی و وضعیت را مشخص کنید.\n\nآیدی عددی : $id_user\nنام کاربری : {$request_agent['username']}\nتوضیحات :  {$request_agent['Description']} ";
        $textrequestagent .= "\nوضعیت: تایید شد ({$agentTypeLabels[$selectedType]})";
        $textrequestagent .= "\nبرای تغییر نوع نماینده از دکمه‌های زیر استفاده کنید.";
        Editmessagetext($from_id, $message_id, $textrequestagent, $keyboardreject);
    }
    telegram('answerCallbackQuery', array(
        'callback_query_id' => $callback_query_id,
        'text' => "نوع نماینده به {$agentTypeLabels[$selectedType]} تغییر کرد.",
        'show_alert' => false,
        'cache_time' => 5,
    ));
    return;
}

if (!$rf_admin_handled && ($datain == "iranpay2setting" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $trnado, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "iranpay3setting" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $iranpaykeyboard, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "وضعیت  درگاه ترونادو" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $statusternadoosql = select("PaySetting", "ValuePay", "NamePay", "statustarnado", "select");
    $statusternadoo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $statusternadoosql['ValuePay'], 'callback_data' => $statusternadoosql['ValuePay']],
            ],
        ]
    ]);
    $textternado = "در این بخش می توانید درگاه ترنادو را خاموش یا روشن کنید";
    sendmessage($from_id, $textternado, $statusternadoo, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "onternado")) {
    $rf_admin_handled = true;

    update("PaySetting", "ValuePay", "offternado", "NamePay", "statustarnado");
    $statusternadoosql = select("PaySetting", "ValuePay", "NamePay", "statustarnado", "select");
    $statusternadoo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $statusternadoosql['ValuePay'], 'callback_data' => $statusternadoosql['ValuePay']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, "خاموش گردید", $statusternadoo);
    return;
}

if (!$rf_admin_handled && ($datain == "offternado")) {
    $rf_admin_handled = true;

    update("PaySetting", "ValuePay", "onternado", "NamePay", "statustarnado");
    $statusternadoosql = select("PaySetting", "ValuePay", "NamePay", "statustarnado", "select");
    $statusternadoo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $statusternadoosql['ValuePay'], 'callback_data' => $statusternadoosql['ValuePay']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, "روشن گردید", $statusternadoo);
    return;
}

if (!$rf_admin_handled && ($text == "🔑 ثبت API Key ترنادو" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "apiternado", "select");
    $currentKey = $PaySetting['ValuePay'] ?? 'ثبت نشده';
    $texttronseller = "🔑 کلید API ترنادو خود را اینجا وارد کنید.\n\nکلید فعلی شما: {$currentKey}";
    sendmessage($from_id, $texttronseller, $backadmin, 'HTML');
    step('apiternado', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "apiternado")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $trnado, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "apiternado");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "affilnecurrencysetting")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "یک گزینه را انتخاب کنید", $tronnowpayments, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🗂 نام درگاه کارت به کارت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, " 📌 نام درگاه را ارسال نمايید", $backadmin, 'HTML');
    step("getnamecarttocart", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnamecarttocart")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $CartManage, 'HTML');
    update("textbot", "text", $text, "id_text", "carttocart");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗂 نام درگاه nowpayment")) {
    $rf_admin_handled = true;

    sendmessage($from_id, " 📌 نام درگاه را ارسال نمايید", $backadmin, 'HTML');
    step("getnamenowpayment", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnamenowpayment")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $nowpayment_setting_keyboard, 'HTML');
    update("textbot", "text", $text, "id_text", "textsnowpayment");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗂 نام درگاه ریالی بدون احراز")) {
    $rf_admin_handled = true;

    sendmessage($from_id, " 📌 نام درگاه را ارسال نمايید", $backadmin, 'HTML');
    step("getnamecarttopaynotverify", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnamecarttopaynotverify")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $CartManage, 'HTML');
    update("textbot", "text", $text, "id_text", "textpaymentnotverify");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗂 نام درگاه   plisio")) {
    $rf_admin_handled = true;

    sendmessage($from_id, " 📌 نام درگاه را ارسال نمايید", $backadmin, 'HTML');
    step("gettextnowpayment", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextnowpayment")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $NowPaymentsManage, 'HTML');
    update("textbot", "text", $text, "id_text", "textnowpayment");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗂 نام درگاه رمز ارز آفلاین")) {
    $rf_admin_handled = true;

    sendmessage($from_id, " 📌 نام درگاه را ارسال نمايید", $backadmin, 'HTML');
    step("gettextnowpaymentTRON", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextnowpaymentTRON")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $tronnowpayments, 'HTML');
    update("textbot", "text", $text, "id_text", "textnowpaymenttron");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗂 نام درگاه ارزی ریالی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, " 📌 نام درگاه را ارسال نمايید", $backadmin, 'HTML');
    step("gettextiranpay2", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextiranpay2")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $Swapinokey, 'HTML');
    update("textbot", "text", $text, "id_text", "iranpay2");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗂 نام درگاه استار")) {
    $rf_admin_handled = true;

    sendmessage($from_id, " 📌 نام درگاه را ارسال نمايید", $backadmin, 'HTML');
    step("gettextstartelegram", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextstartelegram")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $Swapinokey, 'HTML');
    update("textbot", "text", $text, "id_text", "text_star_telegram");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🏷️ نام نمایشی درگاه ترنادو")) {
    $rf_admin_handled = true;

    $prompt = "🏷️ نام نمایشی دلخواه برای درگاه ترنادو را ارسال کنید.";
    sendmessage($from_id, $prompt, $backadmin, 'HTML');
    step("gettextiranpay3", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextiranpay3")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $trnado, 'HTML');
    update("textbot", "text", $text, "id_text", "iranpay3");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗂 نام درگاه ارزی ریالی سوم")) {
    $rf_admin_handled = true;

    sendmessage($from_id, " 📌 نام درگاه را ارسال نمايید", $backadmin, 'HTML');
    step("gettextiranpay1", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextiranpay1")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $iranpaykeyboard, 'HTML');
    update("textbot", "text", $text, "id_text", "iranpay1");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗂 نام درگاه آقای پرداخت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, " 📌 نام درگاه را ارسال نمايید", $backadmin, 'HTML');
    step("gettextaqayepardakht", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextaqayepardakht")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $aqayepardakht, 'HTML');
    update("textbot", "text", $text, "id_text", "aqayepardakht");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗂 نام درگاه زرین پال")) {
    $rf_admin_handled = true;

    sendmessage($from_id, " 📌 نام درگاه را ارسال نمايید", $backadmin, 'HTML');
    step("gettextzarinpal", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextzarinpal")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅  متن با موفقیت تنظیم گردید.", $keyboardzarinpal, 'HTML');
    update("textbot", "text", $text, "id_text", "zarinpal");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⚙️  اینباند اکانت غیرفعال" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['managepanel']['Inbound']['GetProtocol'], $keyboardprotocol, 'HTML');
    step('getprotocoldisable', $from_id);
    return;
}

