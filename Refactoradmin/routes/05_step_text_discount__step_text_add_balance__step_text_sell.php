<?php
rf_set_module('admin/routes/05_step_text_discount__step_text_add_balance__step_text_sell.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($text == "متن دکمه کد هدیه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_Discount']}</code>";
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('text_Discount', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_Discount")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_Discount");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "دکمه افزایش موجودی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_Add_Balance']}</code>", $backadmin, 'HTML');
    step('text_Add_Balance', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_Add_Balance")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_Add_Balance");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن دکمه خرید اشتراک" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_sell']}</code>", $backadmin, 'HTML');
    step('text_sell', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_sell")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_sell");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن دکمه زیرمجموعه گیری" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_affiliates']}</code>", $backadmin, 'HTML');
    step('text_affiliates', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_affiliates")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_affiliates");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن دکمه لیست تعرفه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_Tariff_list']}</code>", $backadmin, 'HTML');
    step('text_Tariff_list', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_Tariff_list")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_Tariff_list");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن توضیحات لیست تعرفه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_dec_Tariff_list']}</code>", $backadmin, 'HTML');
    step('text_dec_Tariff_list', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_dec_Tariff_list")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_dec_Tariff_list");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن انتخاب لوکیشن" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['textselectlocation']}</code>", $backadmin, 'HTML');
    step('textselectlocation', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "textselectlocation")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "textselectlocation");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن پیش فاکتور" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_pishinvoice']}</code>", $backadmin, 'HTML');
    sendmessage($from_id, "نام های فارسی متغییر : 
username : نام کاربری کانفیگ 
name_product : نام محصول
Service_time : زمان سرویس
price : قیمت سرویس
Volume : حجم سرویس
userBalance : موجودی کاربر 
note : یادداشت

⚠️ حتما این نام ها باید داخل آکلاد باشند ", null, 'HTML');
    step('text_pishinvoice', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_pishinvoice")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_pishinvoice");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن بعد خرید" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['textafterpay']}</code>", $backadmin, 'HTML');
    sendmessage($from_id, "نام های فارسی متغییر : 
username : نام کاربری کانفیگ 
name_service : نام محصول
day : زمان سرویس
location : موقعیت سرویس
volume : حجم سرویس
config : لینک ساب
links : کانفیگ بدون کپی شدن
links2 : لینک ساب بدون کپی شدن

⚠️ حتما این نام ها باید داخل آکلاد باشند ", null, 'HTML');
    step('text_afterpaytext', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_afterpaytext")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "textafterpay");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن بعد خرید ibsng" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['textafterpayibsng']}</code>", $backadmin, 'HTML');
    sendmessage($from_id, "نام های فارسی متغییر : 
username : نام کاربری کانفیگ 
name_service : نام محصول
day : زمان سرویس
location : موقعیت سرویس
volume : حجم سرویس
config : لینک ساب
links : کانفیگ بدون کپی شدن
links2 : لینک ساب بدون کپی شدن

⚠️ حتما این نام ها باید داخل آکلاد باشند ", null, 'HTML');
    step('text_afterpaytextibsng', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_afterpaytextibsng")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "textafterpayibsng");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن کارت به کارت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_cart']}</code>", $backadmin, 'HTML');
    sendmessage($from_id, "نام های فارسی متغییر : 
price : مبلغ تراکنش
card_number : شماره کارت 
name_card : نام دارنده کارت
⚠️ حتما این نام ها باید داخل آکلاد باشند ", null, 'HTML');
    step('text_cart', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_cart")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_cart");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "تنظیم متن کارت به کارت خودکار" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_cart_auto']}</code>", $backadmin, 'HTML');
    sendmessage($from_id, "نام های فارسی متغییر : 
price : مبلغ تراکنش
card_number : شماره کارت 
name_card : نام دارنده کارت
⚠️ حتما این نام ها باید داخل آکلاد باشند ", null, 'HTML');
    step('text_cart_auto', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_cart_auto")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_cart_auto");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن بعد گرفتن اکانت تست" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['textaftertext']}</code>", $backadmin, 'HTML');
    sendmessage($from_id, "نام های فارسی متغییر : 
username : نام کاربری کانفیگ 
name_service : نام محصول
day : زمان سرویس
location : موقعیت سرویس
volume : حجم سرویس
config : لینک اتصال
links : کانفیگ بدون کپی شدن
links2 : لینک ساب بدون کپی

⚠️ حتما این نام ها باید داخل آکلاد باشند ", null, 'HTML');
    step('text_aftertesttext', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_aftertesttext")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "textaftertext");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن بعد گرفتن اکانت دستی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['textmanual']}</code>", $backadmin, 'HTML');
    sendmessage($from_id, "نام های فارسی متغییر : 
username : نام کاربری کانفیگ 
name_service : نام محصول
location : موقعیت سرویس
config : اطلاعات سرویس

⚠️ حتما این نام ها باید داخل آکلاد باشند ", null, 'HTML');
    step('text_textmanual', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن کرون تست" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['crontest']}</code>", $backadmin, 'HTML');
    sendmessage($from_id, "نام های فارسی متغییر : 
username : نام کاربری کانفیگ 

⚠️ حتما این نام ها باید داخل آکلاد باشند ", null, 'HTML');
    step('text_crontest', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_crontest")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "crontest");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن بعد گرفتن اکانت دستی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['textmanual']}</code>", $backadmin, 'HTML');
    sendmessage($from_id, "نام های فارسی متغییر : 
username : نام کاربری کانفیگ 
name_service : نام محصول
location : موقعیت سرویس
config : اطلاعات سرویس

⚠️ حتما این نام ها باید داخل آکلاد باشند ", null, 'HTML');
    step('text_textmanual', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_textmanual")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "textmanual");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "متن بعد گرفتن اکانت WGDashboard" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_wgdashboard']}</code>", $backadmin, 'HTML');
    sendmessage($from_id, "نام های فارسی متغییر : 
username : نام کاربری کانفیگ 
name_service : نام محصول
day : زمان سرویس
location : موقعیت سرویس
volume : حجم سرویس

⚠️ حتما این نام ها باید داخل آکلاد باشند ", null, 'HTML');
    step('text_wgdashboard', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_wgdashboard")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_wgdashboard");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "دکمه تمدید" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_extend']}</code>", $backadmin, 'HTML');
    step('text_extend', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_extend")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_extend");
    step('home', $from_id);
    return;
}

