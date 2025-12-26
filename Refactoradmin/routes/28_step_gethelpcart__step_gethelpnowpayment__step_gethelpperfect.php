<?php
rf_set_module('admin/routes/28_step_gethelpcart__step_gethelpnowpayment__step_gethelpperfect.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "gethelpcart")) {
    $rf_admin_handled = true;

    if ($text) {
        if (intval($text) == 2) {
            update("PaySetting", "ValuePay", "2", "NamePay", "helpcart");
        } else {
            $data = json_encode(array(
                'type' => "text",
                'text' => $text
            ));
            update("PaySetting", "ValuePay", $data, "NamePay", "helpcart");
        }
    } elseif ($photo) {
        $data = json_encode(array(
            'type' => "photo",
            'text' => $caption,
            'photoid' => $photoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpcart");
    } elseif ($video) {
        $data = json_encode(array(
            'type' => "video",
            'text' => $caption,
            'videoid' => $videoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpcart");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش nowpayment" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .
۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید
۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("gethelpnowpayment", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gethelpnowpayment")) {
    $rf_admin_handled = true;

    if ($text) {
        if (intval($text) == 2) {
            update("PaySetting", "ValuePay", "2", "NamePay", "helpnowpayment");
        } else {
            $data = json_encode(array(
                'type' => "text",
                'text' => $text
            ));
            update("PaySetting", "ValuePay", $data, "NamePay", "helpnowpayment");
        }
    } elseif ($photo) {
        $data = json_encode(array(
            'type' => "photo",
            'text' => $caption,
            'photoid' => $photoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpnowpayment");
    } elseif ($video) {
        $data = json_encode(array(
            'type' => "video",
            'text' => $caption,
            'videoid' => $videoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpnowpayment");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $nowpayment_setting_keyboard, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش پرفکت مانی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .
۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید
۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("gethelpperfect", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gethelpperfect")) {
    $rf_admin_handled = true;

    if ($text) {
        if (intval($text) == 2) {
            update("PaySetting", "ValuePay", "0", "NamePay", "helpperfectmony");
        } else {
            $data = json_encode(array(
                'type' => "text",
                'text' => $text
            ));
            update("PaySetting", "ValuePay", $data, "NamePay", "helpperfectmony");
        }
    } elseif ($photo) {
        $data = json_encode(array(
            'type' => "photo",
            'text' => $caption,
            'photoid' => $photoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpperfectmony");
    } elseif ($video) {
        $data = json_encode(array(
            'type' => "video",
            'text' => $caption,
            'videoid' => $videoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpperfectmony");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش plisio" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .
۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید
۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("gethelpplisio", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gethelpplisio")) {
    $rf_admin_handled = true;

    if ($text) {
        if (intval($text) == 2) {
            update("PaySetting", "ValuePay", "0", "NamePay", "helpplisio");
        } else {
            $data = json_encode(array(
                'type' => "text",
                'text' => $text
            ));
            update("PaySetting", "ValuePay", $data, "NamePay", "helpplisio");
        }
    } elseif ($photo) {
        $data = json_encode(array(
            'type' => "photo",
            'text' => $caption,
            'photoid' => $photoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpplisio");
    } elseif ($video) {
        $data = json_encode(array(
            'type' => "video",
            'text' => $caption,
            'videoid' => $videoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpplisio");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش ارزی ریالی اول" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .
۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید
۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("gethelpiranpay1", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gethelpiranpay1")) {
    $rf_admin_handled = true;

    if ($text) {
        if (intval($text) == 2) {
            update("PaySetting", "ValuePay", "0", "NamePay", "helpcart");
        } else {
            $data = json_encode(array(
                'type' => "text",
                'text' => $text
            ));
            update("PaySetting", "ValuePay", $data, "NamePay", "helpiranpay1");
        }
    } elseif ($photo) {
        $data = json_encode(array(
            'type' => "photo",
            'text' => $caption,
            'photoid' => $photoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpiranpay1");
    } elseif ($video) {
        $data = json_encode(array(
            'type' => "video",
            'text' => $caption,
            'videoid' => $videoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpiranpay1");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش ارزی ریالی  دوم" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .
۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید
۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("helpiranpay2", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "helpiranpay2")) {
    $rf_admin_handled = true;

    if ($text) {
        if (intval($text) == 2) {
            update("PaySetting", "ValuePay", "0", "NamePay", "helpiranpay2");
        } else {
            $data = json_encode(array(
                'type' => "text",
                'text' => $text
            ));
            update("PaySetting", "ValuePay", $data, "NamePay", "helpiranpay2");
        }
    } elseif ($photo) {
        $data = json_encode(array(
            'type' => "photo",
            'text' => $caption,
            'photoid' => $photoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpiranpay2");
    } elseif ($video) {
        $data = json_encode(array(
            'type' => "video",
            'text' => $caption,
            'videoid' => $videoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpiranpay2");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش ارزی ریالی سوم" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .
۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید
۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("helpiranpay3", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "helpiranpay3")) {
    $rf_admin_handled = true;

    if ($text) {
        if (intval($text) == 2) {
            update("PaySetting", "ValuePay", "0", "NamePay", "helpiranpay3");
        } else {
            $data = json_encode(array(
                'type' => "text",
                'text' => $text
            ));
            update("PaySetting", "ValuePay", $data, "NamePay", "helpiranpay3");
        }
    } elseif ($photo) {
        $data = json_encode(array(
            'type' => "photo",
            'text' => $caption,
            'photoid' => $photoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpiranpay3");
    } elseif ($video) {
        $data = json_encode(array(
            'type' => "video",
            'text' => $caption,
            'videoid' => $videoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpiranpay3");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش درگاه اقای پرداخت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .
۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید
۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("helpaqayepardakht", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "helpaqayepardakht")) {
    $rf_admin_handled = true;

    if ($text) {
        if (intval($text) == 2) {
            update("PaySetting", "ValuePay", "0", "NamePay", "helpcart");
        } else {
            $data = json_encode(array(
                'type' => "text",
                'text' => $text
            ));
            update("PaySetting", "ValuePay", $data, "NamePay", "helpaqayepardakht");
        }
    } elseif ($photo) {
        $data = json_encode(array(
            'type' => "photo",
            'text' => $caption,
            'photoid' => $photoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpaqayepardakht");
    } elseif ($video) {
        $data = json_encode(array(
            'type' => "video",
            'text' => $caption,
            'videoid' => $videoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpaqayepardakht");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش زرین پال" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .
۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید
۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("helpzarinpal", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "helpzarinpal")) {
    $rf_admin_handled = true;

    if ($text) {
        if (intval($text) == 2) {
            update("PaySetting", "ValuePay", "0", "NamePay", "helpcart");
        } else {
            $data = json_encode(array(
                'type' => "text",
                'text' => $text
            ));
            update("PaySetting", "ValuePay", $data, "NamePay", "helpzarinpal");
        }
    } elseif ($photo) {
        $data = json_encode(array(
            'type' => "photo",
            'text' => $caption,
            'photoid' => $photoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpzarinpal");
    } elseif ($video) {
        $data = json_encode(array(
            'type' => "video",
            'text' => $caption,
            'videoid' => $videoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpzarinpal");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش  ارزی افلاین" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .
۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید
۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("helpofflinearze", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "helpofflinearze")) {
    $rf_admin_handled = true;

    if ($text) {
        if (intval($text) == 2) {
            update("PaySetting", "ValuePay", "0", "NamePay", "helpofflinearze");
        } else {
            $data = json_encode(array(
                'type' => "text",
                'text' => $text
            ));
            update("PaySetting", "ValuePay", $data, "NamePay", "helpofflinearze");
        }
    } elseif ($photo) {
        $data = json_encode(array(
            'type' => "photo",
            'text' => $caption,
            'photoid' => $photoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpofflinearze");
    } elseif ($video) {
        $data = json_encode(array(
            'type' => "video",
            'text' => $caption,
            'videoid' => $videoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpofflinearze");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "💰 مبلغ عضویت نمایندگی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 قیمت درخواست  عضویت  برای نمایندگی را ارسال کنید.", $backadmin, 'HTML');
    step("getpricereqagent", $from_id);
    return;
}

