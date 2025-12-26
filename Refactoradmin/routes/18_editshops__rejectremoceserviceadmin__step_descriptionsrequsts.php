<?php
rf_set_module('admin/routes/18_editshops__rejectremoceserviceadmin__step_descriptionsrequsts.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && (preg_match('/^editshops-(.*)-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $type = $dataget[1];
    $value = $dataget[2];
    if ($type == "extravolunme") {
        if ($value == "onextra") {
            $valuenew = "offextra";
        } else {
            $valuenew = "onextra";
        }
        update("shopSetting", "value", $valuenew, "Namevalue", "statusextra");
    } elseif ($type == "paydirect") {
        if ($value == "ondirectbuy") {
            $valuenew = "offdirectbuy";
        } else {
            $valuenew = "ondirectbuy";
        }
        update("shopSetting", "value", $valuenew, "Namevalue", "statusdirectpabuy");
    } elseif ($type == "statustimeextra") {
        if ($value == "ontimeextraa") {
            $valuenew = "offtimeextraa";
        } else {
            $valuenew = "ontimeextraa";
        }
        update("shopSetting", "value", $valuenew, "Namevalue", "statustimeextra");
    } elseif ($type == "disorderss") {
        if ($value == "ondisorder") {
            $valuenew = "offdisorder";
        } else {
            $valuenew = "ondisorder";
        }
        update("shopSetting", "value", $valuenew, "Namevalue", "statusdisorder");
    } elseif ($type == "categroygenral") {
        if ($value == "oncategorys") {
            $valuenew = "offcategorys";
        } else {
            $valuenew = "oncategorys";
        }
        update("setting", "statuscategorygenral", $valuenew, null, null);
    } elseif ($type == "changgestatus") {
        if ($value == "onstatus") {
            $valuenew = "offstatus";
        } else {
            $valuenew = "onstatus";
        }
        update("shopSetting", "value", $valuenew, "Namevalue", "statuschangeservice");
    } elseif ($type == "showprice") {
        if ($value == "onshowprice") {
            $valuenew = "offshowprice";
        } else {
            $valuenew = "onshowprice";
        }
        update("shopSetting", "value", $valuenew, "Namevalue", "statusshowprice");
    } elseif ($type == "showconfig") {
        if ($value == "onconfig") {
            $valuenew = "offconfig";
        } else {
            $valuenew = "onconfig";
        }
        update("shopSetting", "value", $valuenew, "Namevalue", "configshow");
    } elseif ($type == "removeservicebackbtn") {
        if ($value == "on") {
            $valuenew = "off";
        } else {
            $valuenew = "on";
        }
        update("shopSetting", "value", $valuenew, "Namevalue", "backserviecstatus");
    } elseif ($type == "categorytime") {
        if ($value == "oncategory") {
            $valuenew = "offcategory";
        } else {
            $valuenew = "oncategory";
        }
        update("setting", "statuscategory", $valuenew);
    }
    $setting = select("setting", "*", null, null, "select") ?? [];

    $marzbanstatusextraRow = select("shopSetting", "*", "Namevalue", "statusextra", "select") ?? [];
    $marzbandirectpayRow = select("shopSetting", "*", "Namevalue", "statusdirectpabuy", "select") ?? [];
    $statustimeextraRow = select("shopSetting", "*", "Namevalue", "statustimeextra", "select") ?? [];
    $statusdisorderRow = select("shopSetting", "*", "Namevalue", "statusdisorder", "select") ?? [];
    $statuschangeserviceRow = select("shopSetting", "*", "Namevalue", "statuschangeservice", "select") ?? [];
    $statusshowpriceRow = select("shopSetting", "*", "Namevalue", "statusshowprice", "select") ?? [];
    $statusshowconfigRow = select("shopSetting", "*", "Namevalue", "configshow", "select") ?? [];
    $statusremoveserveiceRow = select("shopSetting", "*", "Namevalue", "backserviecstatus", "select") ?? [];

    $marzbanstatusextra = $marzbanstatusextraRow['value'] ?? 'offextra';
    $marzbandirectpay = $marzbandirectpayRow['value'] ?? 'offdirectbuy';
    $statustimeextra = $statustimeextraRow['value'] ?? 'offtimeextraa';
    $statusdisorder = $statusdisorderRow['value'] ?? 'offdisorder';
    $statuschangeservice = $statuschangeserviceRow['value'] ?? 'offstatus';
    $statusshowprice = $statusshowpriceRow['value'] ?? 'offshowprice';
    $statusshowconfig = $statusshowconfigRow['value'] ?? 'offconfig';
    $statusremoveserveice = $statusremoveserveiceRow['value'] ?? 'off';

    $categoryStatusGeneralKey = $setting['statuscategorygenral'] ?? 'offcategorys';
    if (!in_array($categoryStatusGeneralKey, ['oncategorys', 'offcategorys'], true)) {
        $categoryStatusGeneralKey = 'offcategorys';
    }

    $categoryStatusKey = $setting['statuscategory'] ?? 'offcategory';
    if (!in_array($categoryStatusKey, ['oncategory', 'offcategory'], true)) {
        $categoryStatusKey = 'offcategory';
    }

    $name_status_extra_Vloume = [
        'onextra' => $textbotlang['Admin']['Status']['statuson'],
        'offextra' => $textbotlang['Admin']['Status']['statusoff']
    ][$marzbanstatusextra] ?? $textbotlang['Admin']['Status']['statusoff'];
    $name_status_paydirect = [
        'ondirectbuy' => $textbotlang['Admin']['Status']['statuson'],
        'offdirectbuy' => $textbotlang['Admin']['Status']['statusoff']
    ][$marzbandirectpay] ?? $textbotlang['Admin']['Status']['statusoff'];
    $name_status_timeextra = [
        'ontimeextraa' => $textbotlang['Admin']['Status']['statuson'],
        'offtimeextraa' => $textbotlang['Admin']['Status']['statusoff']
    ][$statustimeextra] ?? $textbotlang['Admin']['Status']['statusoff'];
    $name_status_disorder = [
        'ondisorder' => $textbotlang['Admin']['Status']['statuson'],
        'offdisorder' => $textbotlang['Admin']['Status']['statusoff']
    ][$statusdisorder] ?? $textbotlang['Admin']['Status']['statusoff'];
    $categorygenral = [
        'oncategorys' => $textbotlang['Admin']['Status']['statuson'],
        'offcategorys' => $textbotlang['Admin']['Status']['statusoff']
    ][$categoryStatusGeneralKey];
    $statustextchange = [
        'onstatus' => $textbotlang['Admin']['Status']['statuson'],
        'offstatus' => $textbotlang['Admin']['Status']['statusoff']
    ][$statuschangeservice] ?? $textbotlang['Admin']['Status']['statusoff'];
    $statusshowpricestext = [
        'onshowprice' => $textbotlang['Admin']['Status']['statuson'],
        'offshowprice' => $textbotlang['Admin']['Status']['statusoff']
    ][$statusshowprice] ?? $textbotlang['Admin']['Status']['statusoff'];
    $statusshowconfigtext = [
        'onconfig' => $textbotlang['Admin']['Status']['statuson'],
        'offconfig' => $textbotlang['Admin']['Status']['statusoff']
    ][$statusshowconfig] ?? $textbotlang['Admin']['Status']['statusoff'];
    $statusbackremovetext = [
        'on' => $textbotlang['Admin']['Status']['statuson'],
        'off' => $textbotlang['Admin']['Status']['statusoff']
    ][$statusremoveserveice] ?? $textbotlang['Admin']['Status']['statusoff'];
    $name_status_categorytime = [
        'oncategory' => $textbotlang['Admin']['Status']['statuson'],
        'offcategory' => $textbotlang['Admin']['Status']['statusoff']
    ][$categoryStatusKey];
    $Bot_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['Admin']['Status']['statussubject'], 'callback_data' => "subjectde"],
                ['text' => $textbotlang['Admin']['Status']['subject'], 'callback_data' => "subject"],
            ],
            [
                ['text' => $name_status_extra_Vloume, 'callback_data' => "editshops-extravolunme-$marzbanstatusextra"],
                ['text' => $textbotlang['Admin']['Status']['statusvolumeextra'], 'callback_data' => "extravolunme"],
            ],
            [
                ['text' => $name_status_paydirect, 'callback_data' => "editshops-paydirect-$marzbandirectpay"],
                ['text' => $textbotlang['Admin']['Status']['paydirect'], 'callback_data' => "paydirect"],
            ],
            [
                ['text' => $name_status_timeextra, 'callback_data' => "editshops-statustimeextra-$statustimeextra"],
                ['text' => $textbotlang['Admin']['Status']['statustimeextra'], 'callback_data' => "statustimeextra"],
            ],
            [
                ['text' => $name_status_disorder, 'callback_data' => "editshops-disorderss-$statusdisorder"],
                ['text' => "⚠️ ارسال گزارش اختلال", 'callback_data' => "disorderss"],
            ],
            [
                ['text' => $categorygenral, 'callback_data' => "editshops-categroygenral-" . $setting['statuscategorygenral']],
                ['text' => "🐛 دسته بندی ", 'callback_data' => "categroygenral"],
            ],
            [
                ['text' => $name_status_categorytime, 'callback_data' => "editshops-categorytime-{$setting['statuscategory']}"],
                ['text' => $textbotlang['Admin']['Status']['statuscategorytime'], 'callback_data' => "statuscategorytime"],
            ],
            [
                ['text' => $statustextchange, 'callback_data' => "editshops-changgestatus-" . $statuschangeservice],
                ['text' => "❓وضعیت غیرفعال کردن اکانت", 'callback_data' => "changgestatus"],
            ],
            [
                ['text' => $statusshowpricestext, 'callback_data' => "editshops-showprice-" . $statusshowprice],
                ['text' => "💰 نمایش قیمت محصول", 'callback_data' => "showprice"],
            ],
            [
                ['text' => $statusshowconfigtext, 'callback_data' => "editshops-showconfig-" . $statusshowconfig],
                ['text' => "🔗 دکمه دریافت کانفیگ", 'callback_data' => "config"],
            ],
            [
                ['text' => $statusbackremovetext, 'callback_data' => "editshops-removeservicebackbtn-" . $statusremoveserveice],
                ['text' => "💎 دکمه بازگشت وجه", 'callback_data' => "removeservicebackbtn"],
            ],
            [
                ['text' => "❌ بستن", 'callback_data' => 'close_stat']
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['BotTitle'], $Bot_Status);
    return;
}

if (!$rf_admin_handled && ($text == "🪪 خروجی گرفتن اطلاعات" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboardexportdata, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🕚 تنظیمات کرون جاب" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $setting_panel, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "خروجی کاربران" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $counttable = select("user", "*", null, null, "count");
    if ($counttable == 0) {
        sendmessage($from_id, "❌ دیتایی برای ارسال خروجی وجود ندارد", null, 'HTML');
        return;
    }
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sql = "SELECT * FROM user";
    $result = $connect->query($sql);

    $col = 1;
    $headers = array_keys($result->fetch_assoc());
    foreach ($headers as $header) {
        $sheet->setCellValue([$col, 1], $header);
        $col++;
    }

    $row = 2;
    while ($row_data = $result->fetch_assoc()) {
        $col = 1;
        foreach ($row_data as $value) {
            $sheet->setCellValue([$col, $row], $value);
            $col++;
        }
        $row++;
    }
    $date = date("Y-m-d");
    $filename = "users_{$date}.xlsx";
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    sendDocument($from_id, $filename, "🪪 خروجی دیتای کاربران");
    unlink($filename);
    return;
}

if (!$rf_admin_handled && ($text == "خروجی سفارشات" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $counttable = select("invoice", "*", null, null, "count");
    if ($counttable == 0) {
        sendmessage($from_id, "❌ دیتایی برای ارسال خروجی وجود ندارد", null, 'HTML');
        return;
    }
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sql = "SELECT * FROM invoice";
    $result = $connect->query($sql);

    $col = 1;
    $headers = array_keys($result->fetch_assoc());
    foreach ($headers as $header) {
        $sheet->setCellValue([$col, 1], $header);
        $col++;
    }

    $row = 2;
    while ($row_data = $result->fetch_assoc()) {
        $col = 1;
        foreach ($row_data as $value) {
            $sheet->setCellValue([$col, $row], $value);
            $col++;
        }
        $row++;
    }
    $date = date("Y-m-d");
    $filename = "invoice_{$date}.xlsx";
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    sendDocument($from_id, $filename, "🪪 خروجی سفارشات کاربران");
    unlink($filename);
    return;
}

if (!$rf_admin_handled && ($text == "خروجی گرفتن پرداخت ها" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $counttable = select("Payment_report", "*", null, null, "count");
    if ($counttable == 0) {
        sendmessage($from_id, "❌ دیتایی برای ارسال خروجی وجود ندارد", null, 'HTML');
        return;
    }
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sql = "SELECT * FROM Payment_report";
    $result = $connect->query($sql);

    $col = 1;
    $headers = array_keys($result->fetch_assoc());
    foreach ($headers as $header) {
        $sheet->setCellValue([$col, 1], $header);
        $col++;
    }

    $row = 2;
    while ($row_data = $result->fetch_assoc()) {
        $col = 1;
        foreach ($row_data as $value) {
            $sheet->setCellValue([$col, $row], $value);
            $col++;
        }
        $row++;
    }
    $date = date("Y-m-d");
    $filename = "Payment_report_{$date}.xlsx";
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    sendDocument($from_id, $filename, "🪪 خروجی پرداختی های کاربران");
    unlink($filename);
    return;
}

if (!$rf_admin_handled && (preg_match('/rejectremoceserviceadmin-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_invoice = $dataget[1];
    $invoice = select("invoice", "*", "id_invoice", $id_invoice, "select");
    $requestcheck = select("cancel_service", "*", "username", $invoice['username'], "select");
    if ($requestcheck['status'] == "accept" || $requestcheck['status'] == "reject") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "این درخواست توسط ادمین دیگری بررسی شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    step("descriptionsrequsts", $from_id);
    update("user", "Processing_value", $requestcheck['username'], "id", $from_id);
    sendmessage($from_id, $textbotlang['users']['stateus']['requestadmin'], $backuser, 'HTML');
    return;
}

if (!$rf_admin_handled && ($user['step'] == "descriptionsrequsts")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['stateus']['accecptreqests'], $keyboardadmin, 'HTML');
    $nameloc = select("invoice", "*", "username", $user['Processing_value'], "select");
    update("cancel_service", "status", "reject", "username", $user['Processing_value']);
    update("cancel_service", "description", $text, "username", $user['Processing_value']);
    step("home", $from_id);
    sendmessage($nameloc['id_user'], "❌ کاربری گرامی درخواست حذف شما با نام کاربری  {$user['Processing_value']} موافقت نگردید.
        
        دلیل عدم تایید : $text", null, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/remoceserviceadmin-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_invoice = $dataget[1];
    $invoice = select("invoice", "*", "id_invoice", $id_invoice, "select");
    $requestcheck = select("cancel_service", "*", "username", $invoice['username'], "select");
    if ($requestcheck['status'] == "accept" || $requestcheck['status'] == "reject") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "این درخواست توسط ادمین دیگری بررسی شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $nameloc = select("invoice", "*", "username", $requestcheck['username'], "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $requestcheck['username']);
    $stmt = $pdo->prepare("SELECT  SUM(price) FROM service_other WHERE username = :username AND type != 'change_location' AND type != 'extend_user' LIMIT 1");
    $stmt->bindParam(':username', $nameloc['username']);
    $stmt->execute();
    $sumproduct = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($DataUserOut['msg']) && $DataUserOut['msg'] == "User not found") {
        sendmessage($from_id, $textbotlang['users']['stateus']['UserNotFound'], null, 'html');
        step('home', $from_id);
        return;
    }
    if ($DataUserOut['data_limit'] == null && $DataUserOut['expire'] == null) {
        sendmessage($from_id, "❌ به دلیل نامحدود بودن حجم و زمان امکان حذف سرویس وجود ندارد. ", null, 'html');
        step('home', $from_id);
        return;
    }
    if ($DataUserOut['status'] == "on_hold") {
        $pricelast = $invoice['price_product'];
    } elseif ($DataUserOut['data_limit'] == null) {
        $serviceTime = (float) ($nameloc['Service_time'] ?? 0);
        if ($serviceTime > 0) {
            $pricetime = safe_divide($nameloc['price_product'], $serviceTime, 0) + intval($sumproduct['SUM(price)']);
            $pricelast = (($DataUserOut['expire'] - time()) / 86400) * $pricetime;
        } else {
            $pricelast = 0;
        }
    } elseif ($DataUserOut['expire'] == null) {
        $dataLimit = isset($DataUserOut['data_limit']) ? (float) $DataUserOut['data_limit'] : 0;
        if ($dataLimit > 0) {
            $volumelefts = ($dataLimit - (float) ($DataUserOut['used_traffic'] ?? 0)) / pow(1024, 3);
            $volumeDivisor = $dataLimit / pow(1024, 3);
            $volumeleft = $volumeDivisor > 0 ? safe_divide($volumelefts, $volumeDivisor, 0) : 0;
            $pricelast = round($volumeleft * ($nameloc['price_product'] + intval($sumproduct['SUM(price)'])), 2);
        } else {
            $pricelast = 0;
        }
    } else {
        $serviceTime = (float) ($nameloc['Service_time'] ?? 0);
        $dataLimit = isset($DataUserOut['data_limit']) ? (float) $DataUserOut['data_limit'] : 0;
        $volumeDivisor = $dataLimit / pow(1024, 3);
        if ($serviceTime > 0 && $volumeDivisor > 0) {
            $timeleft = safe_divide(round(($DataUserOut['expire'] - time()) / 86400, 0), $serviceTime, 0);
            $volumelefts = ($dataLimit - (float) ($DataUserOut['used_traffic'] ?? 0)) / pow(1024, 3);
            $volumeleft = safe_divide($volumelefts, $volumeDivisor, 0);
            $pricelast = round($timeleft * $volumeleft * ($nameloc['price_product'] + intval($sumproduct['SUM(price)'])), 2);
        } else {
            $pricelast = 0;
        }
    }
    $pricelast = intval($pricelast);
    if (intval($pricelast) != 0) {
        $Balance_id_cancel = select("user", "*", "id", $nameloc['id_user'], "select");
        $Balance_id_cancel_fee = intval($Balance_id_cancel['Balance']) + intval($pricelast);
        update("user", "Balance", $Balance_id_cancel_fee, "id", $nameloc['id_user']);
        sendmessage($nameloc['id_user'], "💰کاربر گرامی مبلغ $pricelast تومان به موجودی شما اضافه گردید.", null, 'HTML');
    }
    $ManagePanel->RemoveUser($nameloc['Service_location'], $requestcheck['username']);
    update("cancel_service", "status", "accept", "username", $requestcheck['username']);
    update("invoice", "status", "removedbyadmin", "username", $requestcheck['username']);
    sendmessage($from_id, "❌ مبلغ $pricelast تومان به موجودی کاربر اضافه گردید.", null, 'HTML');
    sendmessage($nameloc['id_user'], "✅ کاربری گرامی درخواست حذف شما با نام کاربری  {$nameloc['username']} موافقت گردید.", null, 'HTML');
    $text_report = "⭕️ یک ادمین سرویس کاربر که درخواست حذف داشت را تایید کرد
        
اطلاعات کاربر تایید کننده  : 

🪪 آیدی عددی : <code>$from_id</code>
💰 مبلغ بازگشتی : $pricelast تومان
👤 نام کاربری : {$requestcheck['username']}
        آیدی عددی درخواست کننده کنسل کردن : {$nameloc['id_user']}";
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $otherreport,
            'text' => $text_report,
            'parse_mode' => "HTML"
        ]);
    }
    return;
}

