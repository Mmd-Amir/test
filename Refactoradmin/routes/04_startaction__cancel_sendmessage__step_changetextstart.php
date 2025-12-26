<?php
rf_set_module('admin/routes/04_startaction__cancel_sendmessage__step_changetextstart.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($datain == "startaction")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    if (!isset($userdata['typeservice'])) {
        sendmessage($from_id, "❌ خطایی رخ داده لطفا مراحل ارسال پیام از اول انجام دهید", $keyboardadmin, 'HTML');
        return;
    }
    $agent = $userdata['agent'];
    $typeservice = $userdata['typeservice'];
    $typeusermessage = $userdata['typeusermessage'];
    $text = $userdata['message'];
    $cancelmessage = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "لغو عملیات", 'callback_data' => 'cancel_sendmessage'],
            ],
        ]
    ]);

    if ($typeservice == "unpinmessage") {
        $userlist = json_encode(select("user", "id", null, null, "fetchAll"));
        $message_id = Editmessagetext($from_id, $message_id, "✅ عملیات آغاز گردید پس از پایان اطلاع رسانی خواهد شد.", $cancelmessage);
        $dataunpin = json_encode(array(
            "id_admin" => $from_id,
            'type' => "unpinmessage",
            "id_message" => $message_id['result']['message_id']
        ));
        file_put_contents("cronbot/users.json", $userlist);
        file_put_contents('cronbot/info', $dataunpin);
    } elseif ($typeservice == "sendmessage") {
        if ($agent == "all") {
            if ($typeusermessage == "all") {
                $userslist = json_encode(select("user", "id", "User_Status", "Active", "fetchAll"));
            } elseif ($typeusermessage == "customer") {
                if ($userdata['selectpanel'] == "all") {
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id) AND u.User_Status = 'Active'");
                } else {
                    $panel = select("marzban_panel", "*", "code_panel", $userdata['selectpanel'], "select");
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id AND i.Service_location = '{$panel['name_panel']}') AND u.User_Status = 'Active'");
                }
                $stmt->execute();
                $userslist = json_encode($stmt->fetchAll());
            } elseif ($typeusermessage == "nonecustomer") {
                $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE NOT EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id) AND u.User_Status = 'Active'");
                $stmt->execute();
                $userslist = json_encode($stmt->fetchAll());
            }
        } else {
            if ($typeusermessage == "all") {
                $userslist = json_encode(select("user", "id", "agent", $agent, "fetchAll"));
            } elseif ($typeusermessage == "customer") {
                if ($userdata['selectpanel'] == "all") {
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.agent =  :agent AND EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id) AND u.User_Status = 'Active'");
                } else {
                    $panel = select("marzban_panel", "*", "code_panel", $userdata['selectpanel'], "select");
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE  u.agent =  :agent AND EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id AND i.Service_location = '{$panel['name_panel']}') AND u.User_Status = 'Active'");
                }
                $stmt->bindParam(':agent', $agent, PDO::PARAM_STR);
                $stmt->execute();
                $userslist = json_encode($stmt->fetchAll());
            } elseif ($typeusermessage == "nonecustomer") {
                $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.agent =  :agent AND NOT EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id) AND u.User_Status = 'Active'");
                $stmt->bindParam(':agent', $agent, PDO::PARAM_STR);
                $stmt->execute();
                $userslist = json_encode($stmt->fetchAll());
            }
        }
        $message_id = Editmessagetext($from_id, $message_id, "✅ عملیات آغاز گردید پس از پایان اطلاع رسانی خواهد شد.", $cancelmessage);
        $data = json_encode(array(
            "id_admin" => $from_id,
            'type' => "sendmessage",
            "id_message" => $message_id['result']['message_id'],
            "message" => $userdata['message'],
            "pingmessage" => $userdata['typepinmessage'],
            "btnmessage" => $userdata['btntypemessage']
        ));
        file_put_contents("cronbot/users.json", $userslist);
        file_put_contents('cronbot/info', $data);
    } elseif ($typeservice == "forwardmessage") {
        if ($agent == "all") {
            if ($typeusermessage == "all") {
                $userslist = json_encode(select("user", "id", "User_Status", "Active", "fetchAll"));
            } elseif ($typeusermessage == "customer") {
                if ($userdata['selectpanel'] == "all") {
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id) AND u.User_Status = 'Active'");
                } else {
                    $panel = select("marzban_panel", "*", "code_panel", $userdata['selectpanel'], "select");
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id AND i.Service_location = '{$panel['name_panel']}') AND u.User_Status = 'Active'");
                }
                $stmt->execute();
                $userslist = json_encode($stmt->fetchAll());
            } elseif ($typeusermessage == "nonecustomer") {
                $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE NOT EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id) AND u.User_Status = 'Active'");
                $stmt->execute();
                $userslist = json_encode($stmt->fetchAll());
            }
        } else {
            if ($typeusermessage == "all") {
                $userslist = json_encode(select("user", "id", "agent", $agent, "fetchAll"));
            } elseif ($typeusermessage == "customer") {
                if ($userdata['selectpanel'] == "all") {
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.agent =  :agent AND EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id) AND u.User_Status = 'Active'");
                } else {
                    $panel = select("marzban_panel", "*", "code_panel", $userdata['selectpanel'], "select");
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.agent =  :agent AND EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id AND i.Service_location = '{$panel['name_panel']}') AND u.User_Status = 'Active'");
                }
                $stmt->bindParam(':agent', $agent, PDO::PARAM_STR);
                $stmt->execute();
                $userslist = json_encode($stmt->fetchAll());
            } elseif ($typeusermessage == "nonecustomer") {
                $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.agent =  :agent AND NOT EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id) AND u.User_Status = 'Active'");
                $stmt->bindParam(':agent', $agent, PDO::PARAM_STR);
                $stmt->execute();
                $userslist = json_encode($stmt->fetchAll());
            }
        }
        $message_id = Editmessagetext($from_id, $message_id, "✅ عملیات آغاز گردید پس از پایان اطلاع رسانی خواهد شد.", $cancelmessage);
        $data = json_encode(array(
            "id_admin" => $from_id,
            'type' => "forwardmessage",
            "id_message" => $message_id['result']['message_id'],
            "message" => $userdata['message'],
            "pingmessage" => $userdata['typepinmessage'],
        ));
        file_put_contents("cronbot/users.json", $userslist);
        file_put_contents('cronbot/info', $data);
    } elseif ($typeservice == "xdaynotmessage") {
        $timedaystamp = intval($userdata['daynoyuse']) * 86400;
        $timenouser = time() - $timedaystamp;
        if ($agent == "all") {
            $stmt = $pdo->prepare("SELECT id FROM user  WHERE last_message_time < $timenouser");
            $stmt->execute();
            $userslist = json_encode($stmt->fetchAll());
        } else {
            if ($typeusermessage == "all") {
                if ($typeusermessage == "all") {
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.last_message_time < :time");
                    $stmt->bindParam(':time', $timenouser, PDO::PARAM_STR);
                    $stmt->execute();
                    $userslist = json_encode($stmt->fetchAll());
                } elseif ($typeusermessage == "customer") {
                    if ($userdata['selectpanel'] == "all") {
                        $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.last_message_time < :time AND EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id);");
                    } else {
                        $panel = select("marzban_panel", "*", "code_panel", $userdata['selectpanel'], "select");
                        $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.last_message_time < :time AND EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id AND i.Service_location = '{$panel['name_panel']}');");
                    }
                    $stmt->bindParam(':time', $timenouser, PDO::PARAM_STR);
                    $stmt->execute();
                    $userslist = json_encode($stmt->fetchAll());
                } elseif ($typeusermessage == "nonecustomer") {
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.last_message_time < :time AND NOT EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id);");
                    $stmt->bindParam(':time', $timenouser, PDO::PARAM_STR);
                    $stmt->execute();
                    $userslist = json_encode($stmt->fetchAll());
                }
            } elseif ($typeusermessage == "customer") {
                if ($userdata['selectpanel'] == "all") {
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.agent =  :agent AND u.last_message_time < :time AND EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id);");
                } else {
                    $panel = select("marzban_panel", "*", "code_panel", $userdata['selectpanel'], "select");
                    $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.agent =  :agent AND u.last_message_time < :time AND EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id AND i.Service_location = '{$panel['name_panel']}');");
                }
                $stmt->bindParam(':agent', $agent, PDO::PARAM_STR);
                $stmt->bindParam(':time', $timenouser, PDO::PARAM_STR);
                $stmt->execute();
                $userslist = json_encode($stmt->fetchAll());
            } elseif ($typeusermessage == "nonecustomer") {
                $stmt = $pdo->prepare("SELECT u.id FROM user u WHERE u.agent =  :agent AND u.last_message_time < :time AND NOT EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id);");
                $stmt->bindParam(':agent', $agent, PDO::PARAM_STR);
                $stmt->bindParam(':time', $timenouser, PDO::PARAM_STR);
                $stmt->execute();
                $userslist = json_encode($stmt->fetchAll());
            }
        }
        $message_id = Editmessagetext($from_id, $message_id, "✅ عملیات آغاز گردید پس از پایان اطلاع رسانی خواهد شد.", $cancelmessage);
        $data = json_encode(array(
            "id_admin" => $from_id,
            'type' => "xdaynotmessage",
            "id_message" => $message_id['result']['message_id'],
            "message" => $userdata['message'],
            "pingmessage" => $userdata['typepinmessage'],
            "btnmessage" => $userdata['btntypemessage']
        ));
        file_put_contents("cronbot/users.json", $userslist);
        file_put_contents('cronbot/info', $data);
    }
    return;
}

if (!$rf_admin_handled && ($datain == "cancel_sendmessage")) {
    $rf_admin_handled = true;

    file_put_contents('users.json', json_encode(array()));
    unlink('cronbot/users.json');
    unlink('cronbot/info');
    deletemessage($from_id, $message_id);
    sendmessage($from_id, "📌 ارسال پیام لغو گردید.", null, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "📝 تنظیم متن ربات" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $textbot, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "تنظیم متن شروع" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_start']}</code>";
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    sendmessage($from_id, "📌 متغییر های قابل استفاده 

⚠️نام کاربری : 
 <blockquote>{username}</blockquote>

⚠️نام اکانت :‌
<blockquote>{first_name}</blockquote>

⚠️نام خانوادگی اکانت :‌
<blockquote>{last_name}</blockquote>

⚠️زمان فعلی : 
<blockquote>{time}</blockquote>

⚠️ نسخه فعلی ربات  : 
<blockquote>{version}</blockquote>", null, "html");
    step('changetextstart', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "changetextstart")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_start");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "دکمه سرویس خریداری شده" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_Purchased_services']}</code>";
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('changetextinfo', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "changetextinfo")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_Purchased_services");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "دکمه اکانت تست" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_usertest']}</code>";
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('changetextusertest', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "changetextusertest")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_usertest");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن دکمه 📚 آموزش" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_help']}</code>", $backadmin, 'HTML');
    step('text_help', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_help")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_help");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن درخواست نمایندگی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['textrequestagent']}</code>", $backadmin, 'HTML');
    step('textrequestagent', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "textrequestagent")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "textrequestagent");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن دکمه  نمایندگی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['textpanelagent']}</code>", $backadmin, 'HTML');
    step('textpanelagent', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "textpanelagent")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "textpanelagent");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن دکمه ☎️ پشتیبانی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_support']}</code>", $backadmin, 'HTML');
    step('text_support', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_support")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_support");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "دکمه سوالات متداول" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_fq']}</code>", $backadmin, 'HTML');
    step('text_fq', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_fq")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_fq");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📝 تنظیم متن توضیحات سوالات متداول" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_dec_fq']}</code>", $backadmin, 'HTML');
    step('text_dec_fq', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_dec_fq")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_dec_fq");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📝 تنظیم متن توضیحات عضویت اجباری" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_channel']}</code>", $backadmin, 'HTML');
    step('text_channel', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_channel")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_channel");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن دکمه کیف پول" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['accountwallet']}</code>";
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('accountwallet', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "accountwallet")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "accountwallet");
    step('home', $from_id);
    return;
}

