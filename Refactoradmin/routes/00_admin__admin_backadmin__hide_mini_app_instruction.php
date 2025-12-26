<?php
rf_set_module('admin/routes/00_admin__admin_backadmin__hide_mini_app_instruction.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && (in_array($text, $textadmin) || $datain == "admin")) {
    $rf_admin_handled = true;

    if ($datain == "admin")
        deletemessage($from_id, $message_id);
    if ($buyreport == "0" || $otherservice == "0" || $otherreport == "0" || $paymentreports == "0" || $reporttest == "0" || $errorreport == "0") {
        sendmessage($from_id, $textbotlang['Admin']['activebottext'], $active_panell, 'HTML');
        return;
    }
    $version_mini_app = file_get_contents('app/version');
    activecron();
    $text_admin = sprintf($text_panel_admin_login_template, $version, $version_mini_app);
    sendmessage($from_id, $text_admin, $keyboardadmin, 'HTML');
    $miniAppInstructionHidden = isset($user['hide_mini_app_instruction']) ? (string) $user['hide_mini_app_instruction'] : '0';
    if ($miniAppInstructionHidden !== '1') {
        sendmessage($from_id, $miniAppInstructionText, null, 'HTML');
    }
    return;
}

if (!$rf_admin_handled && ($text == $textbotlang['Admin']['backadmin'])) {
    $rf_admin_handled = true;

    if ($buyreport == "0" || $otherservice == "0" || $otherreport == "0" || $paymentreports == "0" || $reporttest == "0" || $errorreport == "0") {
        sendmessage($from_id, $textbotlang['Admin']['activebottext'], $active_panell, 'HTML');
        return;
    }
    $version_mini_app = file_get_contents('app/version');
    $text_admin = sprintf($text_panel_admin_login_template, $version, $version_mini_app);
    sendmessage($from_id, $text_admin, $keyboardadmin, 'HTML');
    step('home', $from_id);
    return;
    return;
}

if (!$rf_admin_handled && ($datain == "hide_mini_app_instruction")) {
    $rf_admin_handled = true;

    if (!in_array($from_id, $admin_ids))
        return;
    if (($user['hide_mini_app_instruction'] ?? '0') !== '1') {
        update("user", "hide_mini_app_instruction", "1", "id", $from_id);
        $user['hide_mini_app_instruction'] = '1';
    }
    $confirmationKeyboard = json_encode(['inline_keyboard' => []]);
    $confirmationText = $miniAppInstructionText . "\n\n✅ این پیام دیگر برای شما نمایش داده نخواهد شد.";
    Editmessagetext($from_id, $message_id, $confirmationText, $confirmationKeyboard, 'HTML');
    return;
    return;
}

if (!$rf_admin_handled && ($text == $textbotlang['Admin']['backmenu'])) {
    $rf_admin_handled = true;

    if ($buyreport == "0" || $otherservice == "0" || $otherreport == "0" || $paymentreports == "0" || $reporttest == "0" || $errorreport == "0") {
        sendmessage($from_id, $textbotlang['Admin']['activebottext'], $setting_panel, 'HTML');
        return;
    }
    $currentStep = isset($user['step']) ? (string) $user['step'] : '';
    step('home', $from_id);
    if (in_array($currentStep, ["updatetime", "val_usertest", "getlimitnew", "GetusernameNew", "GeturlNew", "protocolset", "updatemethodusername", "GetNameNew", "getprotocol", "getprotocolremove", "GetpaawordNew", "updateextendmethod", "setpricechangelocation"])) {
        $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
        outtypepanel($typepanel['type'], $textbotlang['Admin']['Back-menu']);
    } else {
        $financialStepKeyboardMap = [
            'apiternado' => $trnado,
            'changecard' => $CartManage,
            'getnamecard' => $CartManage,
            'getnamecarttocart' => $CartManage,
            'getnamenowpayment' => $nowpayment_setting_keyboard,
            'getnamecarttopaynotverify' => $CartManage,
            'gettextnowpayment' => $NowPaymentsManage,
            'gettextnowpaymentTRON' => $tronnowpayments,
            'gettextiranpay2' => $Swapinokey,
            'gettextstartelegram' => $Swapinokey,
            'gettextiranpay3' => $trnado,
            'gettextiranpay1' => $iranpaykeyboard,
            'gettextaqayepardakht' => $aqayepardakht,
            'gettextzarinpal' => $keyboardzarinpal,
            'gettextzarinpey' => $keyboardzarinpey,
            'token_zarinpey' => $keyboardzarinpey,
            'merchant_id_aqayepardakht' => $aqayepardakht,
            'merchant_zarinpal' => $keyboardzarinpal,
            'apinowpayment' => $NowPaymentsManage,
            'getcashcart' => $CartManage,
            'getcashahaypar' => $CartManage,
            'getcashiranpay2' => $trnado,
            'getcashiranpay4' => $CartManage,
            'getcashiranpay1' => $Swapinokey,
            'getcashplisio' => $CartManage,
            'getcashnowpayment' => $nowpayment_setting_keyboard,
            'getcashzarinpal' => $keyboardzarinpal,
            'getcashzarinpey' => $keyboardzarinpey,
            'getmaincart' => $CartManage,
            'getmaxcart' => $CartManage,
            'getmainplisio' => $NowPaymentsManage,
            'getmaxplisio' => $NowPaymentsManage,
            'getmaindigitaltron' => $tronnowpayments,
            'getmaxdigitaltron' => $tronnowpayments,
            'getmainiranpay1' => $Swapinokey,
            'getmaaxiranpay1' => $Swapinokey,
            'getmainiranpay2' => $trnado,
            'getmaaxiranpay2' => $Swapinokey,
            'getmainaqayepardakht' => $aqayepardakht,
            'getmaaxaqayepardakht' => $aqayepardakht,
            'getmainaqzarinpal' => $aqayepardakht,
            'getmaaxzarinpal' => $aqayepardakht,
            'getmainzarinpey' => $keyboardzarinpey,
            'getmaaxzarinpey' => $keyboardzarinpey,
            'helpzarinpey' => $keyboardzarinpey,
            'gethelpcart' => $CartManage,
            'gethelpnowpayment' => $nowpayment_setting_keyboard,
            'gethelpperfect' => $CartManage,
            'gethelpplisio' => $CartManage,
            'gethelpiranpay1' => $CartManage,
            'getmainaqstar' => $Startelegram,
            'maxbalancestar' => $Startelegram,
            'getmainaqnowpayment' => $nowpayment_setting_keyboard,
            'maxbalancenowpayment' => $nowpayment_setting_keyboard,
            'gethelpstar' => $Startelegram,
            'chashbackstar' => $Startelegram,
        ];

        $productStepKeyboardMap = [
            'change_price' => $change_product,
            'change_note' => $change_product,
            'change_categroy' => $change_product,
            'change_name' => $change_product,
            'change_type_agent' => $change_product,
            'change_reset_data' => $change_product,
            'change_loc_data' => $change_product,
            'getlistpanel' => $change_product,
            'change_val' => $change_product,
            'change_time' => $change_product,
        ];

        if ($currentStep === 'walletaddresssiranpay') {
            $processingData = [];
            if (isset($user['Processing_value'])) {
                $decodedProcessing = json_decode($user['Processing_value'], true);
                if (is_array($decodedProcessing)) {
                    $processingData = $decodedProcessing;
                }
            }
            $walletOrigin = $processingData['walletaddress_origin'] ?? 'general';
            $keyboard = $walletOrigin === 'trnado' ? $trnado : $keyboardadmin;
            sendmessage($from_id, $textbotlang['Admin']['Back-menu'], $keyboard, 'HTML');
            return;
        }

        if (isset($financialStepKeyboardMap[$currentStep])) {
            $targetKeyboard = $financialStepKeyboardMap[$currentStep];
            sendmessage($from_id, $textbotlang['Admin']['Back-menu'], $targetKeyboard, 'HTML');
            return;
        }

        if (isset($productStepKeyboardMap[$currentStep])) {
            $targetKeyboard = $productStepKeyboardMap[$currentStep];
            sendmessage($from_id, $textbotlang['Admin']['Back-menu'], $targetKeyboard, 'HTML');
            return;
        }

        $shopSteps = [
            "selectloc",
            "get_limit",
            "selectlocedite",
            "GetPriceExtra",
            "GetPriceexstratime",
            "GetPricecustomtime",
            "GetPricecustomvolume",
            "get_code",
            "get_codesell",
            "minbalancebulk",
            "get_agent",
            "get_location",
            "getcategory",
            "get_time",
            "get_price",
            "gettimereset",
            "getnote",
            "endstep",
            "gettypeextra",
            "gettypeextracustom",
            "gettypeextratime",
            "gettypeextratimecustom",
            "gettypeextramain",
            "gettypeextramax",
            "gettypeextramaintime",
            "gettypeextramaxtime",
        ];

        if (in_array($currentStep, $shopSteps, true)) {
            sendmessage($from_id, $textbotlang['Admin']['Back-menu'], $shopkeyboard, 'HTML');
            return;
        } elseif (in_array($currentStep, ["addchannel", "removechannel"])) {
            sendmessage($from_id, $textbotlang['Admin']['Back-menu'], $channelkeyboard, 'HTML');
        } else {
            sendmessage($from_id, $textbotlang['Admin']['Back-Admin'], $keyboardadmin, 'HTML');
        }
    }
    return;
    return;
}

if (!$rf_admin_handled && ($text == $textbotlang['Admin']['channel']['title'] && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['channel']['changechannel'], $backadmin, 'HTML');
    step('addchannel', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "addchannel")) {
    $rf_admin_handled = true;

    savedata("clear", "link", $text);
    sendmessage($from_id, "📌 یک نام برای دکمه عضویت چنل انتخاب نمایید.", $backadmin, 'HTML');
    step('getremark', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getremark")) {
    $rf_admin_handled = true;

    savedata("save", "remark", $text);
    sendmessage($from_id, "📌 لینک عضویت را ارسال کنید", $backadmin, 'HTML');
    step('getlinkjoin', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getlinkjoin")) {
    $rf_admin_handled = true;

    if (!filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, "آدرس عضویت صحیح نمی باشد", $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    if (!is_array($userdata)) {
        $userdata = [];
    }

    $remark = isset($userdata['remark']) ? (string) $userdata['remark'] : '';
    $link = isset($userdata['link']) ? (string) $userdata['link'] : '';

    sendmessage($from_id, "✅ کانال جوین اجباری با موفقیت ثبت گردید.", $channelkeyboard, 'HTML');
    step('home', $from_id);

    $insertChannel = function ($remarkValue) use ($pdo, $link, $text) {
        $stmt = $pdo->prepare("INSERT INTO channels (link, remark, linkjoin) VALUES (:link, :remark, :linkjoin)");
        $stmt->bindValue(':remark', $remarkValue, PDO::PARAM_STR);
        $stmt->bindValue(':link', $link, PDO::PARAM_STR);
        $stmt->bindValue(':linkjoin', $text, PDO::PARAM_STR);
        $stmt->execute();
    };

    try {
        $insertChannel($remark);
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Incorrect string value') !== false) {
            ensureTableUtf8mb4('channels');
            try {
                $insertChannel($remark);
            } catch (PDOException $retryException) {
                if (strpos($retryException->getMessage(), 'Incorrect string value') === false) {
                    throw $retryException;
                }

                $sanitisedRemark = is_string($remark) ? @iconv('UTF-8', 'UTF-8//IGNORE', $remark) : '';
                if ($sanitisedRemark === false) {
                    $sanitisedRemark = '';
                }
                $insertChannel($sanitisedRemark);
            }
        } else {
            throw $e;
        }
    }
    return;
}

if (!$rf_admin_handled && ($text == $textbotlang['Admin']['channel']['removechannelbtn'] && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['channel']['removechannel'], $list_channels_joins, 'HTML');
    step('removechannel', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "removechannel")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['channel']['removedchannel'], $channelkeyboard, 'HTML');
    step('home', $from_id);
    $stmt = $pdo->prepare("DELETE FROM channels WHERE link = :link");
    $stmt->bindParam(':link', $text, PDO::PARAM_STR);
    $stmt->execute();
    return;
}

if (!$rf_admin_handled && ($datain == "addnewadmin" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['getid'], $backadmin, 'HTML');
    step('addadmin', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "addadmin")) {
    $rf_admin_handled = true;

    $adminId = trim($text);
    if ($adminId === '') {
        sendmessage($from_id, $textbotlang['Admin']['manageadmin']['getid'], $backadmin, 'HTML');
        return;
    }
    update("user", "Processing_value", $adminId, "id", $from_id);
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['setrule'], $adminrule, 'HTML');
    step('getrule', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getrule")) {
    $rf_admin_handled = true;

    $rule = ['administrator', 'Seller', 'support'];
    if (!in_array($text, $rule)) {
        sendmessage($from_id, $textbotlang['Admin']['manageadmin']['invalidrule'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['addadminset'], $keyboardadmin, 'HTML');
    sendmessage($user['Processing_value'], $textbotlang['Admin']['manageadmin']['adminedsenduser'], null, 'HTML');
    step('home', $from_id);
    $usernamepanel = "root";
    $randomString = bin2hex(random_bytes(5));
    $stmt = $pdo->prepare("INSERT INTO admin (id_admin, username, password, rule) VALUES (:id_admin, :username, :password, :rule)");
    $stmt->bindParam(':id_admin', $user['Processing_value'], PDO::PARAM_STR);
    $stmt->bindParam(':username', $usernamepanel, PDO::PARAM_STR);
    $stmt->bindParam(':password', $randomString, PDO::PARAM_STR);
    $stmt->bindParam(':rule', $text, PDO::PARAM_STR);
    $stmt->execute();
    $text_report = sprintf($textbotlang['Admin']['reportgroup']['adminadded'], $username, $from_id, $text, $user['Processing_value']);
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

if (!$rf_admin_handled && (preg_match('/limitusertest_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['getid'], $backadmin, 'HTML');
    update("user", "Processing_value", $iduser, "id", $from_id);
    step('get_number_limit', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_number_limit")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['setlimit'], $keyboardadmin, 'HTML');
    $id_user_set = $text;
    step('home', $from_id);
    update("user", "limit_usertest", $text, "id", $user['Processing_value']);
    return;
}

if (!$rf_admin_handled && ($text == $textbotlang['Admin']['getlimitusertest']['setlimitbtn'] && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['limitall'], $backadmin, 'HTML');
    step('limit_usertest_allusers', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "limit_usertest_allusers")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['setlimitall'], $keyboardadmin, 'HTML');
    step('home', $from_id);
    update("user", "limit_usertest", $text);
    update("setting", "limit_usertest_all", $text);
    return;
}

if (!$rf_admin_handled && ($text == "📯 تنظیمات کانال" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['channel']['description'], $channelkeyboard, 'HTML');
    return;
}

