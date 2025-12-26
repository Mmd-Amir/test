<?php
rf_set_module('routes/usertest/01_create_test_service.php');
if (!$rf_chain2_handled && ($user['step'] == "createusertest" || preg_match('/locationtest_(.*)/', $datain, $dataget) || ($text == $datatextbot['text_usertest'] || $datain == "usertestbtn" || $text == "usertest"))) {
    $rf_chain2_handled = true;
    if (!check_active_btn($setting['keyboardmain'], "text_usertest")) {
        sendmessage($from_id, "📌 سرویس تست در حال حاضر در دسترس نیست .", null, 'HTML');
        rf_stop();
    }
    $userlimit = select("user", "*", "id", $from_id, "select");
    if ($userlimit['limit_usertest'] <= 0 && !in_array($from_id, $admin_ids)) {
        sendmessage($from_id, $textbotlang['users']['usertest']['limitwarning'], $keyboard_buy, 'html');
        rf_stop();
    }
    if ($setting['get_number'] == "onAuthenticationphone" && $user['step'] != "get_number" && $user['number'] == "none") {
        sendmessage($from_id, $textbotlang['users']['number']['Confirming'], $request_contact, 'HTML');
        step('get_number', $from_id);
    }
    if ($user['number'] == "none" && $setting['get_number'] == "onAuthenticationphone")
        rf_stop();
    $locationproduct = select("marzban_panel", "*", "TestAccount", "ONTestAccount", "count");
    if ($locationproduct == 1) {
        $panel = select("marzban_panel", "*", "TestAccount", "ONTestAccount", "select");
        if ($panel['hide_user'] != null) {
            $list_user = json_decode($panel['hide_user'], true);
            if (in_array($from_id, $list_user)) {
                sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpanel'], null, 'HTML');
                rf_stop();
            }
        }
        $location = $panel['code_panel'];
    } else {
        if (isset($dataget[1])) {
            $location = $dataget[1];
        } else {
            if ($user['step'] != "createusertest") {
                rf_stop();
            } else {
                $location = $user['Processing_value_one'];
            }
        }
    }
    $marzban_list_get = select("marzban_panel", "*", "code_panel", $location, "select");
    if ($marzban_list_get['MethodUsername'] == $textbotlang['users']['customusername'] || $marzban_list_get['MethodUsername'] == "نام کاربری دلخواه + عدد رندوم") {
        if ($user['step'] != "createusertest") {
            step('createusertest', $from_id);
            update("user", "Processing_value_one", $location, "id", $from_id);
            sendmessage($from_id, $textbotlang['users']['selectusername'], $backuser, 'html');
            rf_stop();
        }
    } else {
        $name_panel = $location;
    }
    if ($user['step'] == "createusertest") {
        $name_panel = $user['Processing_value_one'];
        if (!preg_match('~(?!_)^[a-z][a-z\d_]{2,32}(?<!_)$~i', $text)) {
            sendmessage($from_id, $textbotlang['users']['invalidusername'], $backuser, 'HTML');
            rf_stop();
        }
    } else {
        deletemessage($from_id, $message_id);
    }
    if ($marzban_list_get['type'] == "Manualsale") {
        $stmt = $pdo->prepare("SELECT * FROM manualsell WHERE codepanel = :codepanel AND codeproduct = :codeproduct AND status = 'active'");
        $value = "usertest";
        $stmt->bindParam(':codepanel', $marzban_list_get['code_panel']);
        $stmt->bindParam(':codeproduct', $value);
        $stmt->execute();
        $configexits = $stmt->rowCount();
        if (intval($configexits) == 0) {
            sendmessage($from_id, "❌ موجودی این سرویس به پایان رسیده.", null, 'HTML');
            rf_stop();
        }
    }
    $limit_usertest = $userlimit['limit_usertest'] - 1;
    update("user", "limit_usertest", $limit_usertest, "id", $from_id);
    $randomString = bin2hex(random_bytes(4));
    $text = strtolower($text);
    $marzban_list_get = select("marzban_panel", "*", "code_panel", $name_panel, "select");
    $text = strtolower($text);
    $username_ac = generateUsername($from_id, $marzban_list_get['MethodUsername'], $user['username'], $randomString, $text, $marzban_list_get['namecustom'], $user['namecustom']);
    $username_ac = strtolower($username_ac);
    $DataUserOut = $ManagePanel->DataUser($marzban_list_get['name_panel'], $username_ac);
    $random_number = rand(1000000, 9999999);
    if (isset($DataUserOut['username']) || in_array($username_ac, $usernameinvoice)) {
        $username_ac = $random_number . "_" . $username_ac;
    }
    $datac = array(
        'expire' => strtotime(date("Y-m-d H:i:s", strtotime("+" . $marzban_list_get['time_usertest'] . "hours"))),
        'data_limit' => $marzban_list_get['val_usertest'] * 1048576,
        'from_id' => $from_id,
        'username' => $username_ac,
        'type' => 'usertest'
    );
    $date = time();
    $notifctions = json_encode(array(
        'volume' => false,
        'time' => false,
    ));
    $stmt = $connect->prepare("INSERT IGNORE INTO invoice (id_user, id_invoice, username,time_sell, Service_location, name_product, price_product, Volume, Service_time,Status,notifctions) VALUES (?, ?,  ?, ?, ?, ?, ?,?,?,?,?)");
    $Status = "active";
    $info_product['name_product'] = "سرویس تست";
    $info_product['price_product'] = "0";
    $Status = "active";
    $stmt->bind_param("sssssssssss", $from_id, $randomString, $username_ac, $date, $marzban_list_get['name_panel'], $info_product['name_product'], $info_product['price_product'], $marzban_list_get['val_usertest'], $marzban_list_get['time_usertest'], $Status, $notifctions);
    $stmt->execute();
    $stmt->close();
    $dataoutput = $ManagePanel->createUser($marzban_list_get['name_panel'], "usertest", $username_ac, $datac);
    if ($dataoutput['username'] == null) {
        $dataoutput['msg'] = json_encode($dataoutput['msg']);
        sendmessage($from_id, $textbotlang['users']['usertest']['errorcreat'], $keyboard, 'html');
        $texterros = "
⭕️ یک کاربر قصد دریافت اکانت  تست داشت که ساخت کانفیگ با خطا مواجه شده و به کاربر کانفیگ داده نشد
✍️ دلیل خطا : 
{$dataoutput['msg']}
آیدی کابر : $from_id
نام کاربری کاربر : @$username
نام پنل : {$marzban_list_get['name_panel']}";
        if (strlen($setting['Channel_Report'] ?? '') > 0) {
            telegram('sendmessage', [
                'chat_id' => $setting['Channel_Report'],
                'message_thread_id' => $errorreport,
                'text' => $texterros,
                'parse_mode' => "HTML"
            ]);
        }
        step('home', $from_id);
        update("invoice", "Status", "Unsuccessful", "id_invoice", $randomString);
        rf_stop();
    }
    $output_config_link = "";
    $config = "";
    $output_config_link = $marzban_list_get['sublink'] == "onsublink" ? $dataoutput['subscription_url'] : "";
    if ($marzban_list_get['config'] == "onconfig" && is_array($dataoutput['configs'])) {
        for ($i = 0; $i < count($dataoutput['configs']); ++$i) {
            $config .= "\n" . $dataoutput['configs'][$i];
        }
    }

    $usertestinfo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['help']['btninlinebuy'], 'callback_data' => "helpbtn"],
            ]
        ]
    ]);
    if ($marzban_list_get['type'] == "WGDashboard") {
        $datatextbot['textaftertext'] = "✅ سرویس با موفقیت ایجاد شد

👤 نام کاربری سرویس : {username}
🌿 نام سرویس:  {name_service}
‏🇺🇳 لوکیشن: {location}
⏳ مدت زمان: {day}  ساعت
🗜 حجم سرویس:  {volume} مگابایت

🧑‍🦯 شما میتوانید شیوه اتصال را  با فشردن دکمه زیر و انتخاب سیستم عامل خود را دریافت کنید";
    }
    $datatextbot['textaftertext'] = $marzban_list_get['type'] == "ibsng" || $marzban_list_get['type'] == "mikrotik" ? $datatextbot['textafterpayibsng'] : $datatextbot['textaftertext'];
    $textcreatuser = str_replace('{username}', $dataoutput['username'], $datatextbot['textaftertext']);
    $textcreatuser = str_replace('{name_service}', "تست", $textcreatuser);
    $textcreatuser = str_replace('{location}', $marzban_list_get['name_panel'], $textcreatuser);
    $textcreatuser = str_replace('{day}', $marzban_list_get['time_usertest'], $textcreatuser);
    $textcreatuser = str_replace('{volume}', $marzban_list_get['val_usertest'], $textcreatuser);
    $textcreatuser = applyConnectionPlaceholders($textcreatuser, $output_config_link, $config);
    if ($marzban_list_get['type'] == "ibsng" || $marzban_list_get['type'] == "mikrotik") {
        $textcreatuser = str_replace('{password}', $dataoutput['subscription_url'], $textcreatuser);
        update("invoice", "user_info", $dataoutput['subscription_url'], "id_invoice", $randomString);
    }
    sendMessageService($marzban_list_get, $dataoutput['configs'], $output_config_link, $dataoutput['username'], $usertestinfo, $textcreatuser, $randomString);
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'HTML');
    step('home', $from_id);
    if ($marzban_list_get['MethodUsername'] == "متن دلخواه + عدد ترتیبی" || $marzban_list_get['MethodUsername'] == "نام کاربری + عدد به ترتیب" || $marzban_list_get['MethodUsername'] == "آیدی عددی+عدد ترتیبی" || $marzban_list_get['MethodUsername'] == "متن دلخواه نماینده + عدد ترتیبی") {
        $value = intval($user['number_username']) + 1;
        update("user", "number_username", $value, "id", $from_id);
        if ($marzban_list_get['MethodUsername'] == "متن دلخواه + عدد ترتیبی" || $marzban_list_get['MethodUsername'] == "متن دلخواه نماینده + عدد ترتیبی") {
            $value = intval($setting['numbercount']) + 1;
            update("setting", "numbercount", $value);
        }
    }
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['Admin']['ManageUser']['mangebtnuser'], 'callback_data' => 'manageuser_' . $from_id],
            ],
        ]
    ]);
    $timejalali = jdate('Y/m/d H:i:s');
    $text_report = "📣 جزئیات ساخت اکانت تست در ربات شما ثبت شد .
▫️آیدی عددی کاربر : <code>$from_id</code>
▫️نام کاربری کاربر :@$username
▫️نام کاربری کانفیگ :$username_ac
▫️نام کاربر : $first_name
▫️موقعیت سرویس : {$marzban_list_get['name_panel']}
▫️زمان خریداری شده : {$marzban_list_get['time_usertest']} ساعت
▫️حجم خریداری شده : {$marzban_list_get['val_usertest']} MB
▫️کد پیگیری: $randomString
▫️نوع کاربر : {$user['agent']}
▫️شماره تلفن کاربر : {$user['number']}
▫️زمان خرید : $timejalali";
    if (strlen($setting['Channel_Report'] ?? '') > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $reporttest,
            'text' => $text_report,
            'parse_mode' => "HTML",
            'reply_markup' => $Response
        ]);
    }
}
if (!$rf_chain2_handled && ($text == $datatextbot['text_help'] || $datain == "helpbtn" || $datain == "helpbtns" || $text == "/help" || $text == "help")) {
    $rf_chain2_handled = true;
    if (!check_active_btn($setting['keyboardmain'], "text_help")) {
        sendmessage($from_id, $textbotlang['users']['help']['disablehelp'], null, 'HTML');
        rf_stop();
    }
    if ($setting['categoryhelp'] == "1") {
        if ($datain == "helpbtns") {
            Editmessagetext($from_id, $message_id, "📌 یک دسته را انتخاب نمایید", $json_list_helpـcategory, 'HTML');
        } else {
            sendmessage($from_id, "📌 یک دسته را انتخاب نمایید", $json_list_helpـcategory, 'HTML');
        }
    } else {
        $helplist = select("help", "*", null, null, "fetchAll");
        $helpidos = ['inline_keyboard' => []];
        foreach ($helplist as $result) {
            $helpidos['inline_keyboard'][] = [
                ['text' => $result['name_os'], 'callback_data' => "helpos_{$result['id']}"]
            ];
        }
        if ($setting['linkappstatus'] == "1") {
            $helpidos['inline_keyboard'][] = [
                ['text' => "🔗 لینک دانلود برنامه", 'callback_data' => "linkappdownlod"],
            ];
        }
        $helpidos['inline_keyboard'][] = [
            ['text' => $textbotlang['users']['backmenu'], 'callback_data' => "backuser"],
        ];
        $json_list_help = json_encode($helpidos);
        if ($datain == "helpbtns") {
            Editmessagetext($from_id, $message_id, $textbotlang['users']['selectoption'], $json_list_help, 'HTML');
        } else {
            sendmessage($from_id, $textbotlang['users']['selectoption'], $json_list_help, 'HTML');
        }
    }
}
if (!$rf_chain2_handled && (preg_match('/^helpctgoryـ(.*)/', $datain, $dataget))) {
    $rf_chain2_handled = true;
    $helplist = select("help", "*", "category", $dataget[1], "fetchAll");
    $helpidos = ['inline_keyboard' => []];
    foreach ($helplist as $result) {
        $helpidos['inline_keyboard'][] = [
            ['text' => $result['name_os'], 'callback_data' => "helpos_{$result['id']}"]
        ];
    }
    $helpidos['inline_keyboard'][] = [
        ['text' => $textbotlang['users']['backmenu'], 'callback_data' => "helpbtns"],
    ];
    $json_list_help = json_encode($helpidos);
    Editmessagetext($from_id, $message_id, $textbotlang['users']['selectoption'], $json_list_help, 'HTML');
}
if (!$rf_chain2_handled && (preg_match('/^helpos_(.*)/', $datain, $dataget))) {
    $rf_chain2_handled = true;
    deletemessage($from_id, $message_id);
    $backinfoss = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "helpbtns"],
            ]
        ]
    ]);
    $helpid = $dataget[1];
    $helpdata = select("help", "*", "id", $helpid, "select");
    if ($helpdata !== false) {
        if (strlen($helpdata['Media_os']) != 0) {
            if ($helpdata['type_Media_os'] == "video") {
                $backinfoss = json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "helpbtn"],
                        ]
                    ]
                ]);
                telegram('sendvideo', [
                    'chat_id' => $from_id,
                    'video' => $helpdata['Media_os'],
                    'caption' => $helpdata['Description_os'],
                    'reply_markup' => $backinfoss,
                    'parse_mode' => "HTML"
                ]);
            } elseif ($helpdata['type_Media_os'] == "document") {
                $backinfoss = json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "helpbtn"],
                        ]
                    ]
                ]);
                telegram('sendDocument', [
                    'chat_id' => $from_id,
                    'document' => $helpdata['Media_os'],
                    'caption' => $helpdata['Description_os'],
                    'reply_markup' => $backinfoss,
                    'parse_mode' => "HTML"
                ]);
            } elseif ($helpdata['type_Media_os'] == "photo") {
                $backinfoss = json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "helpbtn"],
                        ]
                    ]
                ]);
                telegram('sendphoto', [
                    'chat_id' => $from_id,
                    'photo' => $helpdata['Media_os'],
                    'caption' => $helpdata['Description_os'],
                    'reply_markup' => $backinfoss,
                    'parse_mode' => "HTML"
                ]);
            }
        } else {
            sendmessage($from_id, $helpdata['Description_os'], $backinfoss, 'HTML');
        }
    }
}
if (!$rf_chain2_handled && ($text == $datatextbot['text_support'] || $datain == "supportbtns" || $text == "/support")) {
    $rf_chain2_handled = true;
    if (!check_active_btn($setting['keyboardmain'], "text_support")) {
        sendmessage($from_id, "❌ این دکمه غیرفعال می باشد", null, 'HTML');
        rf_stop();
    }
    if ($datain == "supportbtns") {
        Editmessagetext($from_id, $message_id, $textbotlang['users']['support']['btnsupport'], $supportoption);
    } else {
        sendmessage($from_id, $textbotlang['users']['support']['btnsupport'], $supportoption, 'HTML');
    }
}
if (!$rf_chain2_handled && ($datain == "support")) {
    $rf_chain2_handled = true;
    Editmessagetext($from_id, $message_id, "📌 بخش پشتیبانی که میخواهید پیام دهید را انتخاب نمایید.", $list_departman, 'HTML');
}
if (!$rf_chain2_handled && (preg_match('/^departman_(.*)/', $datain, $dataget))) {
    $rf_chain2_handled = true;
    $iddeparteman = $dataget[1];
    savedata("clear", "iddeparteman", $iddeparteman);
    deletemessage($from_id, $message_id);
    sendmessage($from_id, "📌 پیام خود را ارسال نمایید", $backuser, 'HTML');
    step("gettextticket", $from_id);
}
if (!$rf_chain2_handled && ($user['step'] == "gettextticket" && $text)) {
    $rf_chain2_handled = true;
    $userdata = json_decode($user['Processing_value'], true);
    $departeman = select("departman", "*", "id", $userdata['iddeparteman'], "select");
    $time = date('Y/m/d H:i:s');
    $timejalali = jdate('Y/m/d H:i:s');
    $randomString = bin2hex(random_bytes(4));
    $stmt = $pdo->prepare("INSERT IGNORE INTO support_message (Tracking,idsupport,iduser,name_departman,text,time,status) VALUES (:Tracking,:idsupport,:iduser,:name_departman,:text,:time,:status)");
    $status = "Unseen";
    $stmt->bindParam(':Tracking', $randomString);
    $stmt->bindParam(':idsupport', $departeman['idsupport']);
    $stmt->bindParam(':iduser', $from_id);
    $stmt->bindParam(':name_departman', $departeman['name_departman']);
    $stmt->bindParam(':text', $text, PDO::PARAM_STR);
    $stmt->bindParam(':time', $time);
    $stmt->bindParam(':status', $status);
    $stmt->execute();
    if ($photo) {
        sendphoto($departeman['idsupport'], $photoid, null);
    }
    if ($video) {
        sendvideo($departeman['idsupport'], $videoid, null);
    }
    $textsuppoer = "
    📣 پشتیبان عزیز یک پیام از سمت کاربر برای شما ارسال گردید.

آیدی عددی کاربر : <a href = \"tg://user?id=$from_id\">$from_id</a>
زمان ارسال : $timejalali
وضعیت پیام : پاسخ داده نشده
نام کاربری کاربر : @$username    
نام دپارتمان : {$departeman['name_departman']}

متن پیام : $text $caption";
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['support']['answermessage'], 'callback_data' => 'Responsesupport_' . $randomString],
            ],
        ]
    ]);
    sendmessage($departeman['idsupport'], $textsuppoer, $Response, 'HTML');
    sendmessage($from_id, "✅ پیام شما با موفقیت ارسال و پس از بررسی به شما پاسخ داده خواهد شد.", $keyboard, 'HTML');
    step("home", $from_id);
    step("home", $departeman['idsupport']);
}
if (!$rf_chain2_handled && (preg_match('/Responsesupport_(\w+)/', $datain, $dataget))) {
    $rf_chain2_handled = true;
    $idtraking = $dataget[1];
    $trakingdetail = select("support_message", "*", "Tracking", $idtraking);
    if ($trakingdetail['status'] == "Answered") {
        sendmessage($from_id, "❌ پیام توسط ادمین دیگری پاسخ داده شده.", null, 'HTML');
        rf_stop();
    }
    sendmessage($from_id, "📌 متن پیام خود را ارسال نمایید", $backuser, 'HTML');
    update("user", "Processing_value", $idtraking, "id", $from_id);
    step("getextsupport", $from_id);
}
if (!$rf_chain2_handled && ($user['step'] == "getextsupport")) {
    $rf_chain2_handled = true;
    $trakingdetail = select("support_message", "*", "Tracking", $user['Processing_value']);
    $time = date('Y/m/d H:i:s');
    update("support_message", "status", "Answered", "Tracking", $user['Processing_value']);
    update("support_message", "result", $text, "Tracking", $user['Processing_value']);
    $textSendAdminToUser = "
📩 یک پیام از سمت مدیریت برای شما ارسال گردید.
                    
متن پیام : 
$text";
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['support']['answermessage'], 'callback_data' => 'Responsesusera_' . $trakingdetail['Tracking']],
            ],
        ]
    ]);
    sendmessage($trakingdetail['iduser'], $textSendAdminToUser, $Response, 'HTML');
    sendmessage($from_id, "پیام با موفقیت ارسال گردید", null, 'HTML');
    step("home", $from_id);
}
if (!$rf_chain2_handled && (preg_match('/Responsesusera_(\w+)/', $datain, $dataget))) {
    $rf_chain2_handled = true;
    $idtraking = $dataget[1];
    sendmessage($from_id, "📌 متن پیام خود را ارسال نمایید", $backuser, 'HTML');
    update("user", "Processing_value", $idtraking, "id", $from_id);
    step("getextuserfors", $from_id);
}
if (!$rf_chain2_handled && ($user['step'] == "getextuserfors")) {
    $rf_chain2_handled = true;
    $trakingdetail = select("support_message", "*", "Tracking", $user['Processing_value']);
    step("home", $from_id);
    $time = date('Y/m/d H:i:s');
    $timejalali = jdate('Y/m/d H:i:s');
    Editmessagetext($from_id, $message_id, $text_inline, json_encode(['inline_keyboard' => []]));
    $randomString = bin2hex(random_bytes(4));
    $stmt = $pdo->prepare("INSERT IGNORE INTO support_message (Tracking,idsupport,iduser,name_departman,text,time,status) VALUES (:Tracking,:idsupport,:iduser,:name_departman,:text,:time,:status)");
    $status = "Customerresponse";
    $stmt->bindParam(':Tracking', $randomString);
    $stmt->bindParam(':idsupport', $trakingdetail['idsupport']);
    $stmt->bindParam(':iduser', $trakingdetail['iduser']);
    $stmt->bindParam(':name_departman', $trakingdetail['name_departman']);
    $stmt->bindParam(':text', $text, PDO::PARAM_STR);
    $stmt->bindParam(':time', $time);
    $stmt->bindParam(':status', $status);
    $stmt->execute();
    $textsuppoer = "
    📣 پشتیبان عزیز یک پیام از سمت کاربر برای شما ارسال گردید.

آیدی عددی کاربر : <a href = \"tg://user?id=$from_id\">$from_id</a>
زمان ارسال : $timejalali
وضعیت پیام : پاسخ مشتری
نام کاربری کاربر : @$username    
نام دپارتمان : {$trakingdetail['name_departman']}

متن پیام : $text";
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['support']['answermessage'], 'callback_data' => 'Responsesupport_' . $randomString],
            ],
        ]
    ]);
    if ($photo) {
        sendphoto($trakingdetail['idsupport'], $photoid, null);
    }
    if ($video) {
        sendvideo($trakingdetail['idsupport'], $videoid, null);
    }
    sendmessage($trakingdetail['idsupport'], $textsuppoer, $Response, 'HTML');
    sendmessage($from_id, "✅  پیام شما برای این درخواست با موفقیت ارسال گردید پس از بررسی پاسخ داده خواهد شد.", null, 'HTML');
}
if (!$rf_chain2_handled && ($datain == "fqQuestions")) {
    $rf_chain2_handled = true;
    sendmessage($from_id, $datatextbot['text_dec_fq'], null, 'HTML');
}
