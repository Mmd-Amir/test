<?php
rf_set_module('routes/shop/02_buy_service_checkout.php');
if (!$rf_chain2_handled && ($user['step'] == "endstepuser" || $user['step'] == "endstepusers" || preg_match('/prodcutservice_(.*)/', $datain, $dataget) || $user['step'] == "getvolumecustomuser")) {
    $rf_chain2_handled = true;
    $userdate = json_decode($user['Processing_value'], true);
    if ($user['step'] == "getvolumecustomuser") {
        if (!ctype_digit($text)) {
            sendmessage($from_id, $textbotlang['Admin']['customvolume']['invalidtime'], $backuser, 'HTML');
            rf_stop();
        }
        $marzban_list_get = select("marzban_panel", "*", "name_panel", $userdate['name_panel'], "select");
        $maintime = json_decode($marzban_list_get['maintime'], true);
        $maintime = $maintime[$user['agent']];
        $maxtime = json_decode($marzban_list_get['maxtime'], true);
        $maxtime = $maxtime[$user['agent']];
        if (intval($text) > intval($maxtime) || intval($text) < intval($maintime)) {
            $texttime = "❌ زمان ارسال شده نامعتبر است . زمان باید بین $maintime روز تا $maxtime روز باشد";
            sendmessage($from_id, $texttime, $backuser, 'HTML');
            rf_stop();
        }
        $prodcut = "customvolume_" . $text . "_" . $user['Processing_value_one'];
    } elseif ($user['step'] == "endstepusers" || $user['step'] == "endstepuser") {
        $prodcut = $user['Processing_value_one'];
    } else {
        $prodcut = $dataget[1];
    }
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $userdate['name_panel'], "select");
    if ($marzban_list_get['status'] == "disable") {
        sendmessage($from_id, "❌ این پنل در دسترس نیست لطفا از پنل دیگری خرید را انجام دهید.", $backuser, 'html');
        step("home", $from_id);
        rf_stop();
    }
    if ($marzban_list_get['MethodUsername'] == $textbotlang['users']['customusername'] || $marzban_list_get['MethodUsername'] == "نام کاربری دلخواه + عدد رندوم") {
        if (!preg_match('~(?!_)^[a-z][a-z\d_]{2,32}(?<!_)$~i', $text)) {
            sendmessage($from_id, $textbotlang['users']['invalidusername'], $backuser, 'HTML');
            rf_stop();
        }
        $loc = $user['Processing_value_one'];
    } else {
        $loc = $prodcut;
    }
    update("user", "Processing_value_one", $loc, "id", $from_id);
    $eextraprice = json_decode($marzban_list_get['pricecustomvolume'], true);
    $custompricevalue = $eextraprice[$user['agent']];
    $eextraprice = json_decode($marzban_list_get['pricecustomtime'], true);
    $customtimevalueprice = $eextraprice[$user['agent']];
    $parts = explode("_", $loc);
    if ($parts[0] == "customvolume") {
        $info_product['Volume_constraint'] = $parts[2];
        $info_product['name_product'] = $textbotlang['users']['customsellvolume']['title'];
        $info_product['code_product'] = $textbotlang['users']['customsellvolume']['title'];
        $info_product['Service_time'] = $parts[1];
        $info_product['price_product'] = ($parts[2] * $custompricevalue) + ($parts[1] * $customtimevalueprice);
    } else {
        $info_product = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM product WHERE code_product = '$loc' AND (Location = '{$userdate['name_panel']}'or Location = '/all') LIMIT 1"));
    }
    if (!isset($info_product['price_product'])) {
        sendmessage($from_id, "❌ خطایی در تایید  انجام شده است لطفا مراحل پرداخت را مجددا انجام دهید", $keyboard, 'HTML');
        rf_stop();
    }
    if (intval($user['pricediscount']) != 0) {
        $resultper = ($info_product['price_product'] * $user['pricediscount']) / 100;
        $info_product['price_product'] = $info_product['price_product'] - $resultper;
    }
    $randomString = bin2hex(random_bytes(2));
    $text = strtolower($text);
    $username_ac = generateUsername($from_id, $marzban_list_get['MethodUsername'], $username, $randomString, $text, $marzban_list_get['namecustom'], $user['namecustom']);
    $username_ac = strtolower($username_ac);
    $DataUserOut = $ManagePanel->DataUser($marzban_list_get['name_panel'], $username_ac);
    $random_number = rand(1000000, 9999999);
    $usernameinvoice = select("invoice", "username", null, null, "FETCH_COLUMN", ['cache' => true]);
    if (isset($DataUserOut['username']) || (is_array($usernameinvoice) && in_array($username_ac, $usernameinvoice))) {
        $username_ac = $random_number . "_" . $username_ac;
    }
    if (isset($username_ac))
        update("user", "Processing_value_tow", $username_ac, "id", $from_id);
    if (intval($info_product['Volume_constraint']) == 0)
        $info_product['Volume_constraint'] = $textbotlang['users']['stateus']['Unlimited'];
    if (intval($info_product['Service_time']) == 0)
        $info_product['Service_time'] = $textbotlang['users']['stateus']['Unlimited'];
    $info_product_price_product = number_format($info_product['price_product']);
    $userBalance = number_format($user['Balance']);
    $replacements = [
        '{username}' => $username_ac,
        '{name_product}' => $info_product['name_product'],
        '{Service_time}' => $info_product['Service_time'],
        '{note}' => $info_product['note'],
        '{price}' => $info_product_price_product,
        '{Volume}' => $info_product['Volume_constraint'],
        '{userBalance}' => $userBalance
    ];
    $textin = strtr($datatextbot['text_pishinvoice'], $replacements);
    if (intval($info_product['Volume_constraint']) == 0) {
        $textin = str_replace('گیگ', "", $textin);
    }
    if ($user['step'] != "getvolumecustomuser" && !in_array($marzban_list_get['MethodUsername'], ["نام کاربری دلخواه", "نام کاربری دلخواه + عدد رندوم"])) {
        Editmessagetext($from_id, $message_id, $textin, $payment);
    } else {
        sendmessage($from_id, $textin, $payment, 'HTML');
    }
    step('payment', $from_id);
}
if (!$rf_chain2_handled && ($user['step'] == "payment" && $datain == "confirmandgetservice" || $datain == "confirmandgetserviceDiscount")) {
    $rf_chain2_handled = true;
    $userdate = json_decode($user['Processing_value'], true);
    Editmessagetext($from_id, $message_id, $text_inline, json_encode(['inline_keyboard' => []]));
    // $pats for customm service
    $parts = explode("_", $user['Processing_value_one']);
    // $partsdic for discount value
    $partsdic = explode("_", $user['Processing_value_four']);
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $userdate['name_panel'], "select");
    if ($marzban_list_get['status'] == "disable") {
        sendmessage($from_id, "❌ این پنل در دسترس نیست لطفا از پنل دیگری خرید را انجام دهید.", $backuser, 'html');
        step("home", $from_id);
        rf_stop();
    }
    $eextraprice = json_decode($marzban_list_get['pricecustomvolume'], true);
    $custompricevalue = $eextraprice[$user['agent']];
    $eextraprice = json_decode($marzban_list_get['pricecustomtime'], true);
    $customtimevalueprice = $eextraprice[$user['agent']];
    if ($parts[0] == "customvolume") {
        $info_product['Volume_constraint'] = $parts[2];
        $info_product['name_product'] = $textbotlang['users']['customsellvolume']['title'];
        $info_product['code_product'] = "customvolume";
        $info_product['Service_time'] = $parts[1];
        $info_product['price_product'] = ($parts[2] * $custompricevalue) + ($parts[1] * $customtimevalueprice);
        $info_product['data_limit_reset'] = "no_reset";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM product WHERE code_product = :code_product AND (Location = :location OR Location = '/all') LIMIT 1");
        $stmt->execute([
            ':code_product' => $user['Processing_value_one'],
            ':location' => $userdate['name_panel']
        ]);
        $info_product = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!isset($info_product['price_product']))
        rf_stop();
    if ($datain == "confirmandgetserviceDiscount") {
        $discountcode = select("DiscountSell", "*", "codeDiscount", $partsdic[0], "count");
        if ($discountcode == 0) {
            sendmessage($from_id, "❌ امکان خرید با این کد کد تخفیف وجود ندارد", null, 'HTML');
            rf_stop();
        }
        $priceproduct = $partsdic[1];
    } else {
        $priceproduct = $info_product['price_product'];
    }
    $username_ac = strtolower($user['Processing_value_tow']);
    $DataUserOut = $ManagePanel->DataUser($marzban_list_get['name_panel'], $username_ac);
    $usernameinvoice = select("invoice", "username", null, null, "FETCH_COLUMN", ['cache' => true]);
    if (isset($DataUserOut['username']) || (is_array($usernameinvoice) && in_array($username_ac, $usernameinvoice))) {
        sendmessage($from_id, "❌ لطفا مراحل خرید را مجددا انجام دهید", null, 'HTML');
        rf_stop();
    }
    $date = time();
    $randomString = bin2hex(random_bytes(4));
    $random_number = rand(1000000, 9999999);
    $id_invoice = select("invoice", "id_invoice", null, null, "FETCH_COLUMN", ['cache' => true]);
    if (is_array($id_invoice) && in_array($randomString, $id_invoice)) {
        $randomString = $random_number . $randomString;
    }
    if ($marzban_list_get['type'] == "Manualsale") {
        $marzban_list_get = select("marzban_panel", "*", "name_panel", $userdate['name_panel'], "select");
        $stmt = $pdo->prepare("SELECT * FROM manualsell WHERE codepanel = :codepanel AND codeproduct = :codeproduct AND status = 'active'");
        $stmt->bindParam(':codepanel', $marzban_list_get['code_panel']);
        $stmt->bindParam(':codeproduct', $info_product['code_product']);
        $stmt->execute();
        $configexits = $stmt->rowCount();
        if (intval($configexits) == 0) {
            sendmessage($from_id, "❌ موجودی این سرویس به پایان رسیده لطفا سرویسی دیگر را خریداری کنید.", null, 'HTML');
            rf_stop();
        }
    }
    if (intval($user['pricediscount']) != 0) {
        $result = ($priceproduct * $user['pricediscount']) / 100;
        $priceproduct = $priceproduct - $result;
        sendmessage($from_id, sprintf($textbotlang['users']['Discount']['discountapplied'], $user['pricediscount']), null, 'HTML');
    }
    $notifctions = json_encode(array(
        'volume' => false,
        'time' => false,
    ));
    $stmt = $connect->prepare("INSERT IGNORE INTO invoice (id_user, id_invoice, username,time_sell, Service_location, name_product, price_product, Volume, Service_time,Status,note,refral,notifctions) VALUES (?,  ?, ?, ?, ?, ?, ?,?,?,?,?,?,?)");
    $Status = "unpaid";
    $stmt->bind_param("sssssssssssss", $from_id, $randomString, $username_ac, $date, $marzban_list_get['name_panel'], $info_product['name_product'], $priceproduct, $info_product['Volume_constraint'], $info_product['Service_time'], $Status, $userdate['nameconfig'], $user['affiliates'], $notifctions);
    $stmt->execute();
    $stmt->close();
    if ($priceproduct > $user['Balance'] && $user['agent'] != "n2" && intval($priceproduct) != 0) {
        $marzbandirectpay = select("shopSetting", "*", "Namevalue", "statusdirectpabuy", "select")['value'];
        $Balance_prim = $priceproduct - $user['Balance'];
        if ($Balance_prim <= 1)
            $Balance_prim = 0;
        if ($marzbandirectpay == "offdirectbuy") {
            $minbalance = number_format(json_decode(select("PaySetting", "*", "NamePay", "minbalance", "select")['ValuePay'], true)[$user['agent']]);
            $maxbalance = number_format(json_decode(select("PaySetting", "*", "NamePay", "maxbalance", "select")['ValuePay'], true)[$user['agent']]);
            $bakinfos = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "account"],
                    ]
                ]
            ]);
            Editmessagetext($from_id, $message_id, sprintf($textbotlang['users']['Balance']['insufficientbalance'], $minbalance, $maxbalance), $bakinfos, 'HTML');
            step('getprice', $from_id);
        } else {
            update("user", "Processing_value", $Balance_prim, "id", $from_id);
            sendmessage($from_id, $textbotlang['users']['sell']['None-credit'], $step_payment, 'HTML');
            step('get_step_payment', $from_id);
            update("user", "Processing_value_one", $username_ac, "id", $from_id);
            update("user", "Processing_value_tow", "getconfigafterpay", "id", $from_id);
            if ($datain == "confirmandgetserviceDiscount")
                update("user", "Processing_value_four", "dis_{$partsdic[0]}", "id", $from_id);
        }
        rf_stop();
    }
    if (intval($user['maxbuyagent']) != 0 and $user['agent'] == "n2") {
        if (intval($user['Balance'] - $priceproduct) < intval("-" . $user['maxbuyagent'])) {
            sendmessage($from_id, $textbotlang['users']['Balance']['maxpurchasereached'], null, 'HTML');
            rf_stop();
        }
    }
    Editmessagetext($from_id, $message_id, "♻️ در حال ساختن سرویس شما...", null);
    if ($datain == "confirmandgetserviceDiscount") {
        $SellDiscountlimit = select("DiscountSell", "*", "codeDiscount", $partsdic[0], "select");
        if ($SellDiscountlimit != false) {
            $value = intval($SellDiscountlimit['usedDiscount']) + 1;
            $stmt = $connect->prepare("INSERT INTO Giftcodeconsumed (id_user,code) VALUES (?,?)");
            $stmt->bind_param("ss", $from_id, $partsdic[0]);
            $stmt->execute();
            update("DiscountSell", "usedDiscount", $value, "codeDiscount", $partsdic[0]);
            $text_report = "⭕️ یک کاربر با نام کاربری @$username  و آیدی عددی $from_id از کد تخفیف {$partsdic[0]} استفاده کرد.";
            if (strlen($setting['Channel_Report'] ?? '') > 0) {
                telegram('sendmessage', [
                    'chat_id' => $setting['Channel_Report'],
                    'message_thread_id' => $otherreport,
                    'text' => $text_report,
                    'parse_mode' => "HTML"
                ]);
            }
        }
    }
    $datetimestep = strtotime("+" . $info_product['Service_time'] . "days");
    if ($info_product['Service_time'] == 0) {
        $datetimestep = 0;
    } else {
        $datetimestep = strtotime(date("Y-m-d H:i:s", $datetimestep));
    }
    $datac = array(
        'expire' => $datetimestep,
        'data_limit' => $info_product['Volume_constraint'] * pow(1024, 3),
        'from_id' => $from_id,
        'username' => $username,
        'type' => 'buy'
    );
    $Shoppinginfo = [
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['help']['btninlinebuy'], 'callback_data' => "helpbtn"],
            ]
        ]
    ];
    $dataoutput = $ManagePanel->createUser($marzban_list_get['name_panel'], $info_product['code_product'], $username_ac, $datac);
    if (!isset($dataoutput['username']) || $dataoutput['username'] === null || $dataoutput['username'] === '') {
        $errorMessage = $dataoutput['msg'] ?? 'unknown error';
        if (is_array($errorMessage) || is_object($errorMessage)) {
            $errorMessage = json_encode($errorMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $errorMessage = (string) $errorMessage;
        }
        $dataoutput['msg'] = $errorMessage;
        sendmessage($from_id, $textbotlang['users']['sell']['ErrorConfig'], $keyboard, 'HTML');
        $texterros = "⭕️ خطای ساخت اشتراک
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
        rf_stop();
    }
    update("invoice", "Status", "active", "username", $username_ac);
    $output_config_link = "";
    $config = "";
    $output_config_link = $marzban_list_get['sublink'] == "onsublink" ? $dataoutput['subscription_url'] : "";
    if ($marzban_list_get['config'] == "onconfig" && is_array($dataoutput['configs'])) {
        for ($i = 0; $i < count($dataoutput['configs']); ++$i) {
            $config .= "\n" . $dataoutput['configs'][$i];
        }
    }
    $Shoppinginfo = json_encode($Shoppinginfo);
    $datatextbot['textafterpay'] = $marzban_list_get['type'] == "Manualsale" ? $datatextbot['textmanual'] : $datatextbot['textafterpay'];
    $datatextbot['textafterpay'] = $marzban_list_get['type'] == "WGDashboard" ? $datatextbot['text_wgdashboard'] : $datatextbot['textafterpay'];
    $datatextbot['textafterpay'] = $marzban_list_get['type'] == "ibsng" || $marzban_list_get['type'] == "mikrotik" ? $datatextbot['textafterpayibsng'] : $datatextbot['textafterpay'];
    if (intval($info_product['Service_time']) == 0)
        $info_product['Service_time'] = $textbotlang['users']['stateus']['Unlimited'];
    if (intval($info_product['Volume_constraint']) == 0)
        $info_product['Volume_constraint'] = $textbotlang['users']['stateus']['Unlimited'];
    $textcreatuser = str_replace('{username}', "<code>{$dataoutput['username']}</code>", $datatextbot['textafterpay']);
    $textcreatuser = str_replace('{name_service}', $info_product['name_product'], $textcreatuser);
    $textcreatuser = str_replace('{location}', $marzban_list_get['name_panel'], $textcreatuser);
    $textcreatuser = str_replace('{day}', $info_product['Service_time'], $textcreatuser);
    $textcreatuser = str_replace('{volume}', $info_product['Volume_constraint'], $textcreatuser);
    $textcreatuser = applyConnectionPlaceholders($textcreatuser, $output_config_link, $config);
    if (intval($info_product['Volume_constraint']) == 0) {
        $textcreatuser = str_replace('گیگابایت', "", $textcreatuser);
    }
    if ($marzban_list_get['type'] == "Manualsale" || $marzban_list_get['type'] == "ibsng" || $marzban_list_get['type'] == "mikrotik") {
        $textcreatuser = str_replace('{password}', $dataoutput['subscription_url'], $textcreatuser);
        update("invoice", "user_info", $dataoutput['subscription_url'], "id_invoice", $randomString);
    }
    sendMessageService($marzban_list_get, $dataoutput['configs'], $output_config_link, $dataoutput['username'], $Shoppinginfo, $textcreatuser, $randomString);
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'HTML');
    if (intval($priceproduct) != 0) {
        $Balance_prim = $user['Balance'] - $priceproduct;
        update("user", "Balance", $Balance_prim, "id", $from_id);
    }
    if ($marzban_list_get['MethodUsername'] == "متن دلخواه + عدد ترتیبی" || $marzban_list_get['MethodUsername'] == "نام کاربری + عدد به ترتیب" || $marzban_list_get['MethodUsername'] == "آیدی عددی+عدد ترتیبی" || $marzban_list_get['MethodUsername'] == "متن دلخواه نماینده + عدد ترتیبی") {
        $value = intval($user['number_username']) + 1;
        update("user", "number_username", $value, "id", $from_id);
        if ($marzban_list_get['MethodUsername'] == "متن دلخواه + عدد ترتیبی" || $marzban_list_get['MethodUsername'] == "متن دلخواه نماینده + عدد ترتیبی") {
            $value = intval($setting['numbercount']) + 1;
            update("setting", "numbercount", $value);
        }
    }
    $affiliatescommission = select("affiliates", "*", null, null, "select");
    $marzbanporsant_one_buy = select("affiliates", "*", null, null, "select");
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE name_product != 'سرویس تست'  AND id_user = :id_user AND Status != 'Unpaid'");
    $stmt->bindParam(':id_user', $from_id);
    $stmt->execute();
    $countinvoice = $stmt->rowCount();
    if ($affiliatescommission['status_commission'] == "oncommission" && ($user['affiliates'] != null && intval($user['affiliates']) != 0)) {
        if ($marzbanporsant_one_buy['porsant_one_buy'] == "on_buy_porsant") {
            if ($countinvoice == 1) {
                $result = ($priceproduct * $setting['affiliatespercentage']) / 100;
                $user_Balance = select("user", "*", "id", $user['affiliates'], "select");
                $Balance_prim = $user_Balance['Balance'] + $result;
                if (intval($setting['scorestatus']) == 1 and !in_array($user['affiliates'], $admin_ids)) {
                    sendmessage($user['affiliates'], "📌شما 2 امتیاز جدید کسب کردید.", null, 'html');
                    $scorenew = $user_Balance['score'] + 2;
                    update("user", "score", $scorenew, "id", $user['affiliates']);
                }
                update("user", "Balance", $Balance_prim, "id", $user['affiliates']);
                $result = number_format($result);
                $dateacc = date('Y/m/d H:i:s');
                $textadd = "🎁  پرداخت پورسانت 
        
        مبلغ $result تومان به حساب شما از طرف  زیر مجموعه تان به کیف پول شما واریز گردید";
                $textreportport = "
مبلغ $result به کاربر {$user['affiliates']} برای پورسانت از کاربر $from_id واریز گردید 
تایم : $dateacc";
                if (strlen($setting['Channel_Report'] ?? '') > 0) {
                    telegram('sendmessage', [
                        'chat_id' => $setting['Channel_Report'],
                        'message_thread_id' => $porsantreport,
                        'text' => $textreportport,
                        'parse_mode' => "HTML"
                    ]);
                }
                sendmessage($user['affiliates'], $textadd, null, 'HTML');
            }
        } else {

            $result = ($priceproduct * $setting['affiliatespercentage']) / 100;
            $user_Balance = select("user", "*", "id", $user['affiliates'], "select");
            $Balance_prim = $user_Balance['Balance'] + $result;
            if (intval($setting['scorestatus']) == 1 and !in_array($user['affiliates'], $admin_ids)) {
                sendmessage($user['affiliates'], "📌شما 2 امتیاز جدید کسب کردید.", null, 'html');
                $scorenew = $user_Balance['score'] + 2;
                update("user", "score", $scorenew, "id", $user['affiliates']);
            }
            update("user", "Balance", $Balance_prim, "id", $user['affiliates']);
            $result = number_format($result);
            $dateacc = date('Y/m/d H:i:s');
            $textadd = "🎁  پرداخت پورسانت 
        
        مبلغ $result تومان به حساب شما از طرف  زیر مجموعه تان به کیف پول شما واریز گردید";
            $textreportport = "
مبلغ $result به کاربر {$user['affiliates']} برای پورسانت از کاربر $from_id واریز گردید 
تایم : $dateacc";
            if (strlen($setting['Channel_Report'] ?? '') > 0) {
                telegram('sendmessage', [
                    'chat_id' => $setting['Channel_Report'],
                    'message_thread_id' => $porsantreport,
                    'text' => $textreportport,
                    'parse_mode' => "HTML"
                ]);
            }
            sendmessage($user['affiliates'], $textadd, null, 'HTML');
        }
    }
    if (intval($setting['scorestatus']) == 1 and !in_array($from_id, $admin_ids)) {
        sendmessage($from_id, "📌شما 1 امتیاز جدید کسب کردید.", null, 'html');
        $scorenew = $user['score'] + 1;
        update("user", "score", $scorenew, "id", $from_id);
    }
    $balanceformatsell = number_format(select("user", "Balance", "id", $from_id, "select")['Balance'], 0);
    $textonebuy = "";
    if ($countinvoice == 1) {
        $textonebuy = "📌 خرید اول کاربر";
    }
    $balanceformatsellbefore = number_format($user['Balance'], 0);
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['Admin']['ManageUser']['mangebtnuser'], 'callback_data' => 'manageuser_' . $from_id],
            ],
        ]
    ]);
    $timejalali = jdate('Y/m/d H:i:s');
    $text_report = "📣 جزئیات ساخت اکانت در ربات شما ثبت شد .

$textonebuy
▫️آیدی عددی کاربر : <code>$from_id</code>
▫️نام کاربری کاربر :@$username
▫️نام کاربری کانفیگ :$username_ac
▫️نام کاربر : $first_name
▫️موقعیت سرویس سرویس : {$userdate['name_panel']}
▫️نام محصول :{$info_product['name_product']}
▫️زمان خریداری شده :{$info_product['Service_time']} روز
▫️حجم خریداری شده : {$info_product['Volume_constraint']} GB
▫️موجودی قبل خرید : $balanceformatsellbefore تومان
▫️موجودی بعد خرید : $balanceformatsell تومان
▫️کد پیگیری: $randomString
▫️نوع کاربر : {$user['agent']}
▫️شماره تلفن کاربر : {$user['number']}
▫️دسته بندی محصول : {$info_product['category']}
▫️قیمت محصول : {$info_product['price_product']} تومان
▫️قیمت نهایی : $priceproduct تومان
▫️زمان خرید : $timejalali";
    if (strlen($setting['Channel_Report'] ?? '') > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $buyreport,
            'text' => $text_report,
            'parse_mode' => "HTML",
            'reply_markup' => $Response
        ]);
    }
    update("user", "Processing_value_four", "none", "id", $from_id);
    step('home', $from_id);
}
if (!$rf_chain2_handled && ($datain == "aptdc")) {
    $rf_chain2_handled = true;
    sendmessage($from_id, $textbotlang['users']['Discount']['getcodesell'], $backuser, 'HTML');
    step('getcodesellDiscount', $from_id);
    deletemessage($from_id, $message_id);
}
