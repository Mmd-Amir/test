<?php
rf_set_module('admin/routes/17_step_getmaxbuyagent__searchorder__step_getusernameconfigandordedrs.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "getmaxbuyagent")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "تغییرات با موفقیت اعمال شد", $keyboardadmin, 'HTML');
    update("user", "maxbuyagent", $text, "id", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "searchorder")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['order']['vieworderusername'], $backadmin, 'HTML');
    step('GetusernameconfigAndOrdedrs', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GetusernameconfigAndOrdedrs" || strpos($text, "/config ") !== false || preg_match('/manageinvoice_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    if ($user['step'] == "GetusernameconfigAndOrdedrs") {
        $usernameconfig = $text;
        $sql = "SELECT * FROM invoice WHERE username LIKE CONCAT('%', :username, '%') OR note  LIKE CONCAT('%', :notes, '%')";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $usernameconfig, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $usernameconfig, PDO::PARAM_STR);
    } elseif ($text[0] == "/") {
        $usernameconfig = explode(" ", $text)[1];
        $sql = "SELECT * FROM invoice WHERE username LIKE CONCAT('%', :username, '%') OR note  LIKE CONCAT('%', :notes, '%')";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $usernameconfig, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $usernameconfig, PDO::PARAM_STR);
    } else {
        $usernameconfig = select("invoice", "*", "id_invoice", $datagetr[1], "select")['username'];
        $sql = "SELECT * FROM invoice WHERE username = :username OR note  = :notes";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $usernameconfig, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $usernameconfig, PDO::PARAM_STR);
    }
    $stmt->execute();
    step("home", $from_id);
    if ($stmt->rowCount() > 1) {
        $keyboardlists = [
            'inline_keyboard' => [],
        ];
        $keyboardlists['inline_keyboard'][] = [
            ['text' => "عملیات", 'callback_data' => "action"],
            ['text' => "وضعیت سرویس", 'callback_data' => "Status"],
            ['text' => "نام کاربری", 'callback_data' => "username"],
        ];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $keyboardlists['inline_keyboard'][] = [
                [
                    'text' => "مشاهده اطلاعات",
                    'callback_data' => "manageinvoice_" . $row['id_invoice']
                ],
                [
                    'text' => $row['Status'],
                    'callback_data' => "username"
                ],
                [
                    'text' => $row['username'],
                    'callback_data' => $row['username']
                ],
            ];
        }
        $keyboardlists = json_encode($keyboardlists);
        sendmessage($from_id, "⚠️ بیشتر از یک سرویس یافت از لیست زیر سرویس صحیح را انتخاب کنید", $keyboardlists, 'HTML');
        return;
    }
    $OrderUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$OrderUser) {
        sendmessage($from_id, $textbotlang['Admin']['order']['notfound'], null, 'HTML');
        return;
    }
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    $keyboardlists['inline_keyboard'][] = [
        ['text' => "♻️ بروزرسانی", 'callback_data' => "manageinvoice_" . $OrderUser['id_invoice']],
    ];
    $keyboardlists['inline_keyboard'][] = [
        ['text' => $textbotlang['Admin']['ManageUser']['removeservice'], 'callback_data' => "removeservice-" . $OrderUser['id_invoice']],
        ['text' => $textbotlang['Admin']['ManageUser']['removeserviceandback'], 'callback_data' => "removeserviceandback-" . $OrderUser['id_invoice']],
    ];
    $keyboardlists['inline_keyboard'][] = [
        ['text' => "🗑 حذف کامل سرویس", 'callback_data' => "removefull-" . $OrderUser['id_invoice']],
    ];
    if (isset($OrderUser['time_sell'])) {
        $datatime = jdate('Y/m/d H:i:s', $OrderUser['time_sell']);
    } else {
        $datatime = $textbotlang['Admin']['ManageUser']['dataorder'];
    }
    if ($OrderUser['name_product'] == "سرویس تست") {
        $OrderUser['Service_time'] = $OrderUser['Service_time'] . "ساعته";
        $OrderUser['Volume'] = $OrderUser['Volume'] . "مگابایت";
    } else {
        $OrderUser['Service_time'] = $OrderUser['Service_time'] . "روزه";
        $OrderUser['Volume'] = $OrderUser['Volume'] . "گیگابایت";
    }
    $stmt = $pdo->prepare("SELECT value FROM service_other WHERE username = :username AND type = 'extend_user' AND status = 'paid' ORDER BY time DESC LIMIT 20");
    $stmt->execute([
        ':username' => $OrderUser['username'],
    ]);
    if ($stmt->rowCount() != 0) {
        $service_other = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!($service_other == false || !(is_string($service_other['value']) && is_array(json_decode($service_other['value'], true))))) {
            $service_other = json_decode($service_other['value'], true);
            $codeproduct = select("product", "name_product", "code_product", $service_other['code_product'], "select");
            if ($codeproduct != false) {
                $OrderUser['name_product'] = $codeproduct['name_product'];
                $OrderUser['Volume'] = $codeproduct['Volume_constraint'];
                $OrderUser['Service_time'] = $codeproduct['Service_time'];
            }
        }
    }
    $text_order = "
🛒 شماره سفارش  :  <code>{$OrderUser['id_invoice']}</code>
🛒  وضعیت سفارش در ربات : <code>{$OrderUser['Status']}</code>
🙍‍♂️ شناسه کاربر : <code>{$OrderUser['id_user']}</code>
👤 نام کاربری اشتراک :  <code>{$OrderUser['username']}</code> 
📍 موقعیت سرویس :  {$OrderUser['Service_location']}
🛍 نام محصول :  {$OrderUser['name_product']}
💰 قیمت پرداختی سرویس : {$OrderUser['price_product']} تومان
⚜️ حجم سرویس خریداری شده : {$OrderUser['Volume']}
⏳ زمان سرویس خریداری شده : {$OrderUser['Service_time']} 
📆 تاریخ خرید : $datatime  
";
    $DataUserOut = $ManagePanel->DataUser($OrderUser['Service_location'], $OrderUser['username']);
    if ($DataUserOut['status'] == "Unsuccessful") {
        $keyboard_json = json_encode($keyboardlists);
        sendmessage($from_id, "کاربر در پنل وجود ندارد", $keyboardadmin, 'html');
        sendmessage($from_id, $text_order, $keyboard_json, 'HTML');
        step('home', $from_id);
        return;
    }
    if ($DataUserOut['online_at'] == "online") {
        $lastonline = 'آنلاین';
    } elseif ($DataUserOut['online_at'] == "offline") {
        $lastonline = 'آفلاین';
    } else {
        if (isset($DataUserOut['online_at']) && $DataUserOut['online_at'] !== null) {
            $dateString = $DataUserOut['online_at'];
            $lastonline = jdate('Y/m/d H:i:s', strtotime($dateString));
        } else {
            $lastonline = "متصل نشده";
        }
    }
    #-------------status----------------#
    $status = $DataUserOut['status'];
    $status_var = [
        'active' => $textbotlang['users']['stateus']['active'],
        'limited' => $textbotlang['users']['stateus']['limited'],
        'disabled' => $textbotlang['users']['stateus']['disabled'],
        'expired' => $textbotlang['users']['stateus']['expired'],
        'on_hold' => $textbotlang['users']['stateus']['on_hold'],
        'Unknown' => $textbotlang['users']['stateus']['Unknown'],
        'deactivev' => $textbotlang['users']['stateus']['disabled'],
    ][$status];
    #--------------[ expire ]---------------#
    $expirationDate = $DataUserOut['expire'] ? jdate('Y/m/d', $DataUserOut['expire']) : $textbotlang['users']['stateus']['Unlimited'];
    #-------------[ data_limit ]----------------#
    $LastTraffic = $DataUserOut['data_limit'] ? formatBytes($DataUserOut['data_limit']) : $textbotlang['users']['stateus']['Unlimited'];
    #---------------[ RemainingVolume ]--------------#
    $output = $DataUserOut['data_limit'] - $DataUserOut['used_traffic'];
    $RemainingVolume = $DataUserOut['data_limit'] ? formatBytes($output) : "نامحدود";
    #---------------[ used_traffic ]--------------#
    $usedTrafficGb = $DataUserOut['used_traffic'] ? formatBytes($DataUserOut['used_traffic']) : $textbotlang['users']['stateus']['Notconsumed'];
    #--------------[ day ]---------------#
    $timeDiff = $DataUserOut['expire'] - time();
    $day = $DataUserOut['expire'] ? floor($timeDiff / 86400) . $textbotlang['users']['stateus']['day'] : $textbotlang['users']['stateus']['Unlimited'];
    #--------------[ subsupdate ]---------------#
    $lastupdate = "";
    if ($DataUserOut['sub_updated_at'] !== null) {
        $sub_updated = $DataUserOut['sub_updated_at'];
        $dateTime = new DateTime($sub_updated, new DateTimeZone('UTC'));
        $dateTime->setTimezone(new DateTimeZone('Asia/Tehran'));
        $lastupdate = jdate('Y/m/d H:i:s', $dateTime->getTimestamp());
    }
    $limitValue = isset($DataUserOut['data_limit']) ? (float) $DataUserOut['data_limit'] : 0;
    $usedTrafficValue = isset($DataUserOut['used_traffic']) ? (float) $DataUserOut['used_traffic'] : 0;
    $Percent = safe_divide(($limitValue - $usedTrafficValue) * 100, $limitValue, 100);
    if ($Percent < 0) {
        $Percent = -$Percent;
    }
    $Percent = round($Percent, 2);
    $text_order .= "
  
 وضعیت سرویس : $status_var
        
🔋 حجم سرویس : $LastTraffic
📥 حجم مصرفی : $usedTrafficGb
💢 حجم باقی مانده : $RemainingVolume ($Percent%)

📅 فعال تا تاریخ : $expirationDate ($day)

لینک اشتراک کاربر : 
<code>{$DataUserOut['subscription_url']}</code>

📶 اخرین زمان اتصال  : $lastonline
🔄 اخرین زمان آپدیت لینک اشتراک  : $lastupdate
#️⃣ کلاینت متصل شده :<code>{$DataUserOut['sub_last_user_agent']}</code>";
    if ($DataUserOut['status'] == "active") {
        $namestatus = '❌ خاموش کردن اکانت';
    } else {
        $namestatus = '💡 روشن کردن اکانت';
    }
    $keyboardlists['inline_keyboard'][] = [
        ['text' => $textbotlang['users']['extend']['title'], 'callback_data' => 'extendadmin_' . $OrderUser['id_invoice']],
        ['text' => $textbotlang['users']['stateus']['config'], 'callback_data' => 'config_' . $OrderUser['id_invoice']],
    ];
    $keyboardlists['inline_keyboard'][] = [
        ['text' => $namestatus, 'callback_data' => 'changestatusadmin_' . $OrderUser['id_invoice']],
    ];
    $keyboard_json = json_encode($keyboardlists);
    sendmessage($from_id, $text_order, $keyboard_json, 'HTML');
    $stmt = $pdo->prepare("SELECT * FROM service_other s WHERE username = '$usernameconfig' AND (status = 'paid' OR status IS NULL)");
    $stmt->execute();
    $list_service = $stmt->fetchAll();
    if ($list_service) {
        foreach ($list_service as $extend) {
            $extend_type = [
                'extend_user' => "تمدید",
                'extend_user_by_admin' => 'تمدید شده توسط ادمین',
                'extra_user' => "حجم اضافه",
                "extra_time_user" => "زمان اضافه",
                "transfertouser" => "انتقال به حساب دیگر",
                "extends_not_user" => "تمدید از نوع نبودن یوزر در لیست",
                "change_location" => "تغییر لوکیشن",
                'gift_time' => 'هدیه همگانی زمان',
                'gift_volume' => 'هدیه همگانی حجم'
            ][$extend['type']];
            $time_jalali = jdate('Y/m/d H:i:s', strtotime($extend['time']));

            $extendtext = "
📌 گزارش سرویس 
🔗  نوع سرویس : $extend_type
🕰 زمان انجام سرویس : {$extend['time']} \n\n($time_jalali)
💰مبلغ انجام سرویس : {$extend['price']}
👤 آیدی عددی کاربر : {$extend['id_user']}
👤 نام کاربری کانفیگ: {$extend['username']}";
            sendmessage($from_id, $extendtext, null, 'HTML');
        }
    }
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🛒 وضعیت قابلیت های فروشگاه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

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
    sendmessage($from_id, $textbotlang['Admin']['Status']['BotTitle'], $Bot_Status, 'HTML');
    return;
}

