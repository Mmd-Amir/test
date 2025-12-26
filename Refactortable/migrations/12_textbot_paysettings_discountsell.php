<?php
rf_set_module('migrations/12_textbot_paysettings_discountsell.php');

try {
    $result = $connect->query("SHOW TABLES LIKE 'textbot'");
    $table_exists = ($result->num_rows > 0);
    $text_roll = "
♨️ قوانین استفاده از خدمات ما

1- به اطلاعیه هایی که داخل کانال گذاشته می شود حتما توجه کنید.
2- در صورتی که اطلاعیه ای در مورد قطعی در کانال گذاشته نشده به اکانت پشتیبانی پیام دهید
3- سرویس ها را از طریق پیامک ارسال نکنید برای ارسال پیامک می توانید از طریق ایمیل ارسال کنید.
    ";
    $text_dec_fq = " 
 💡 سوالات متداول ⁉️

1️⃣ فیلترشکن شما آیپی ثابته؟ میتونم برای صرافی های ارز دیجیتال استفاده کنم؟

✅ به دلیل وضعیت نت و محدودیت های کشور سرویس ما مناسب ترید نیست و فقط لوکیشن‌ ثابته.

2️⃣ اگه قبل از منقضی شدن اکانت، تمدیدش کنم روزهای باقی مانده می سوزد؟

✅ خیر، روزهای باقیمونده اکانت موقع تمدید حساب میشن و اگه مثلا 5 روز قبل از منقضی شدن اکانت 1 ماهه خودتون اون رو تمدید کنید 5 روز باقیمونده + 30 روز تمدید میشه.

3️⃣ اگه به یک اکانت بیشتر از حد مجاز متصل شیم چه اتفاقی میافته؟

✅ در این صورت حجم سرویس شما زود تمام خواهد شد.

4️⃣ فیلترشکن شما از چه نوعیه؟

✅ فیلترشکن های ما v2ray است و پروتکل‌های مختلفی رو ساپورت میکنیم تا حتی تو دورانی که اینترنت اختلال داره بدون مشکل و افت سرعت بتونید از سرویستون استفاده کنید.

5️⃣ فیلترشکن از کدوم کشور است؟

✅ سرور فیلترشکن ما از کشور  آلمان است

6️⃣ چطور باید از این فیلترشکن استفاده کنم؟

✅ برای آموزش استفاده از برنامه، روی دکمه «📚 آموزش» بزنید.

7️⃣ فیلترشکن وصل نمیشه، چیکار کنم؟

✅ به همراه یک عکس از پیغام خطایی که میگیرید به پشتیبانی مراجعه کنید.

8️⃣ فیلترشکن شما تضمینی هست که همیشه مواقع متصل بشه؟

✅ به دلیل قابل پیش‌بینی نبودن وضعیت نت کشور، امکان دادن تضمین نیست فقط می‌تونیم تضمین کنیم که تمام تلاشمون رو برای ارائه سرویس هر چه بهتر انجام بدیم.

9️⃣ امکان بازگشت وجه دارید؟

✅ امکان بازگشت وجه در صورت حل نشدن مشکل از سمت ما وجود دارد.

💡 در صورتی که جواب سوالتون رو نگرفتید میتونید به «پشتیبانی» مراجعه کنید.";
    $text_channel = "   
        ⚠️ کاربر گرامی؛ شما عضو چنل ما نیستید
از طریق دکمه زیر وارد کانال شده و عضو شوید
پس از عضویت دکمه بررسی عضویت را کلیک کنید";
    $text_invoice = "📇 پیش فاکتور شما:
👤 نام کاربری:  {username}
🔐 نام سرویس: {name_product}
📆 مدت اعتبار: {Service_time} روز
💶 قیمت:  {price} تومان
👥 حجم اکانت: {Volume} گیگ
🗒 یادداشت محصول : {note}
💵 موجودی کیف پول شما : {userBalance}
          
💰 سفارش شما آماده پرداخت است";
    $textafterpay = "✅ سرویس با موفقیت ایجاد شد

👤 نام کاربری سرویس : {username}
🌿 نام سرویس:  {name_service}
‏🇺🇳 لوکیشن: {location}
⏳ مدت زمان: {day}  روز
🗜 حجم سرویس:  {volume} گیگابایت

{connection_links}
🧑‍🦯 شما میتوانید شیوه اتصال را  با فشردن دکمه زیر و انتخاب سیستم عامل خود را دریافت کنید";
    $text_wgdashboard = "✅ سرویس با موفقیت ایجاد شد

👤 نام کاربری سرویس : {username}
🌿 نام سرویس:  {name_service}
‏🇺🇳 لوکیشن: {location}
⏳ مدت زمان: {day}  روز
🗜 حجم سرویس:  {volume} گیگابایت

🧑‍🦯 شما میتوانید شیوه اتصال را  با فشردن دکمه زیر و انتخاب سیستم عامل خود را دریافت کنید";
    $textafterpayibsng = "✅ سرویس با موفقیت ایجاد شد

👤 نام کاربری سرویس : {username}
🔑 رمز عبور سرویس :  <code>{password}</code>
🌿 نام سرویس:  {name_service}
‏🇺🇳 لوکیشن: {location}
⏳ مدت زمان: {day}  روز
🗜 حجم سرویس:  {volume} گیگابایت

🧑‍🦯 شما میتوانید شیوه اتصال را  با فشردن دکمه زیر و انتخاب سیستم عامل خود را دریافت کنید";
    $textmanual = "✅ سرویس با موفقیت ایجاد شد

👤 نام کاربری سرویس : {username}
🌿 نام سرویس:  {name_service}
‏🇺🇳 لوکیشن: {location}

 اطلاعات سرویس :
{connection_links}
🧑‍🦯 شما میتوانید شیوه اتصال را  با فشردن دکمه زیر و انتخاب سیستم عامل خود را دریافت کنید";
    $textaftertext = "✅ سرویس با موفقیت ایجاد شد

👤 نام کاربری سرویس : {username}
🌿 نام سرویس:  {name_service}
‏🇺🇳 لوکیشن: {location}
⏳ مدت زمان: {day}  ساعت
🗜 حجم سرویس:  {volume} مگابایت

{connection_links}
🧑‍🦯 شما میتوانید شیوه اتصال را  با فشردن دکمه زیر و انتخاب سیستم عامل خود را دریافت کنید";
    $textconfigtest = "با سلام خدمت شما کاربر گرامی 
سرویس تست شما با نام کاربری {username} به پایان رسیده است
امیدواریم تجربه‌ی خوبی از آسودگی و سرعت سرویستون داشته باشین. در صورتی که از سرویس‌ تست خودتون راضی بودین، میتونید سرویس اختصاصی خودتون رو تهیه کنید و از داشتن اینترنت آزاد با نهایت کیفیت لذت ببرید😉🔥
🛍 برای تهیه سرویس با کیفیت می توانید از دکمه زیر استفاده نمایید";
    $textcart = "برای افزایش موجودی، مبلغ <code>{price}</code>  تومان  را به شماره‌ی حساب زیر واریز کنید 👇🏻

        ====================
        <code>{card_number}</code>
        {name_card}
        ====================

❌ این تراکنش به مدت ۳۰ دقیقه (نیم ساعت) اعتبار دارد و پس از آن امکان پرداخت این تراکنش وجود نخواهد داشت.
‼مبلغ باید همان مبلغی که در بالا ذکر شده واریز نمایید.
‼️امکان برداشت وجه از کیف پول نیست.
‼️مسئولیت واریز اشتباهی با شماست.
🔝بعد از پرداخت  دکمه پرداخت کردم را زده سپس تصویر رسید را ارسال نمایید
💵بعد از تایید پرداختتون توسط ادمین کیف پول شما شارژ خواهد شد و در صورتی که سفارشی داشته باشین انجام خواهد شد";
    $textcartauto = "برای تایید فوری لطفا دقیقاً مبلغ زیر واریز شود. در غیر این صورت تایید پرداخت شما ممکن است با تاخیر مواجه شود.⚠️
            برای افزایش موجودی، مبلغ <code>{price}</code>  ریال  را به شماره‌ی حساب زیر واریز کنید 👇🏻

        ==================== 
        <code>{card_number}</code>
        {name_card}
        ====================
        
💰دقیقا مبلغی را که در بالا ذکر شده واریز نمایید تا بصورت آنی تایید شود.
‼️امکان برداشت وجه از کیف پول نیست.
🔝لزومی به ارسال رسید نیست، اما در صورتی که بعد از گذشت مدتی واریز شما تایید نشد، عکس رسید خود را ارسال کنید.";
    $insertQueries = [
        ['text_start', 'سلام خوش آمدید'],
        ['text_usertest', '🔑 اکانت تست'],
        ['text_Purchased_services', '🛍 سرویس های من'],
        ['text_support', '☎️ پشتیبانی'],
        ['text_help', '📚 آموزش'],
        ['text_bot_off', '❌ ربات خاموش است، لطفا دقایقی دیگر مراجعه کنید'],
        ['text_roll', $text_roll],
        ['text_fq', '❓ سوالات متداول'],
        ['text_dec_fq', $text_dec_fq],
        ['text_sell', '🔐 خرید اشتراک'],
        ['text_Add_Balance', '💰 افزایش موجودی'],
        ['text_channel', $text_channel],
        ['text_Discount', '🎁 کد هدیه'],
        ['text_Tariff_list', '💵 تعرفه اشتراک ها'],
        ['text_dec_Tariff_list', 'تنظیم نشده است'],
        ['text_Account_op', '🎛 حساب کاربری'],
        ['text_affiliates', '👥 زیر مجموعه گیری'],
        ['text_pishinvoice', $text_invoice],
        ['accountwallet', '🏦 کیف پول + شارژ'],
        ['carttocart', '💳 کارت به کارت'],
        ['textnowpayment', '💵 پرداخت ارزی 1'],
        ['textnowpaymenttron', '💵 واریز رمزارز ترون'],
        ['textsnowpayment', '💸 پرداخت با ارز دیجیتال'],
        ['iranpay1', '💸 درگاه  پرداخت ریالی'],
        ['iranpay2', '💸 درگاه  پرداخت ریالی دوم'],
        ['iranpay3', '💸 درگاه  پرداخت ریالی سوم'],
        ['aqayepardakht', '🔵 درگاه آقای پرداخت'],
        ['zarinpey', '🟠 زرین پی'],
        ['mowpayment', '💸 پرداخت با ارز دیجیتال'],
        ['zarinpal', '🟡 زرین پال'],
        ['textafterpay', $textafterpay],
        ['textafterpayibsng', $textafterpayibsng],
        ['textaftertext', $textaftertext],
        ['textmanual', $textmanual],
        ['textselectlocation', '📌 موقعیت سرویس را انتخاب نمایید.'],
        ['crontest', $textconfigtest],
        ['textpaymentnotverify', 'درگاه ریالی'],
        ['textrequestagent', '👨‍💻 درخواست نمایندگی'],
        ['textpanelagent', '👨‍💻 پنل نمایندگی'],
        ['text_wheel_luck', '🎲 گردونه شانس'],
        ['text_cart', $textcart],
        ['text_cart_auto', $textcartauto],
        ['text_star_telegram', "💫 Star Telegram"],
        ['text_request_agent_dec', '📌 توضیحات خود را برای ثبت درخواست نمایندگی ارسال نمایید.'],
        ['text_extend', '♻️ تمدید سرویس'],
        ['text_wgdashboard', $text_wgdashboard]
    ];
    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE textbot (
        id_text varchar(600) PRIMARY KEY NOT NULL,
        text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table textbot" . mysqli_error($connect);
        }

        foreach ($insertQueries as $query) {
            $connect->query("INSERT INTO textbot (id_text, text) VALUES ('$query[0]', '$query[1]')");
        }
    } else {
        foreach ($insertQueries as $query) {
            $connect->query("INSERT IGNORE INTO textbot (id_text, text) VALUES ('$query[0]', '$query[1]')");
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}

try {
    $result = $connect->query("SHOW TABLES LIKE 'PaySetting'");
    $table_exists = ($result->num_rows > 0);
    $main = 20000;
    $max = 1000000;
    $settings = [
        ['Cartstatus', 'oncard'],
        ['CartDirect', '@cart'],
        ['cardnumber', '603700000000'],
        ['namecard', 'تنظیم نشده'],
        ['Cartstatuspv', 'offcardpv'],
        ['apinowpayment', '0'],
        ['nowpaymentstatus', 'offnowpayment'],
        ['digistatus', 'offdigi'],
        ['statusSwapWallet', 'offnSolutions'],
        ['statusaqayepardakht', 'offaqayepardakht'],
        ['merchant_id_aqayepardakht', '0'],
        ['minbalance', '20000'],
        ['maxbalance', '1000000'],
        ['marchent_tronseller', '0'],
        ['walletaddress', ''],
        ['statuscardautoconfirm', 'offautoconfirm'],
        ['urlpaymenttron', 'https://bot.tronado.cloud/api/v1/Order/GetOrderToken'],
        ['statustarnado', 'offternado'],
        ['apiternado', '0'],
        ['chashbackcart', '0'],
        ['chashbackstar', '0'],
        ['chashbackperfect', '0'],
        ['chashbackaqaypardokht', '0'],
        ['chashbackiranpay1', '0'],
        ['chashbackiranpay2', '0'],
        ['chashbackplisio', '0'],
        ['chashbackzarinpal', '0'],
        ['chashbackzarinpey', '0'],
        ['checkpaycartfirst', 'offpayverify'],
        ['zarinpalstatus', 'offzarinpal'],
        ['merchant_zarinpal', '0'],
        ['zarinpeystatus', 'offzarinpey'],
        ['token_zarinpey', '0'],
        ['minbalancecart', $main],
        ['maxbalancecart', $max],
        ['minbalancestar', $main],
        ['maxbalancestar', $max],
        ['minbalanceplisio', $main],
        ['maxbalanceplisio', $max],
        ['minbalancedigitaltron', $main],
        ['maxbalancedigitaltron', $max],
        ['minbalanceiranpay1', $main],
        ['maxbalanceiranpay1', $max],
        ['minbalanceiranpay2', $main],
        ['maxbalanceiranpay2', $max],
        ['minbalanceaqayepardakht', $main],
        ['maxbalanceaqayepardakht', $max],
        ['minbalancepaynotverify', $main],
        ['maxbalancepaynotverify', $max],
        ['minbalanceperfect', $main],
        ['maxbalanceperfect', $max],
        ['minbalancezarinpal', $main],
        ['maxbalancezarinpal', $max],
        ['minbalancezarinpey', $main],
        ['maxbalancezarinpey', $max],
        ['minbalanceiranpay', $main],
        ['maxbalanceiranpay', $max],
        ['minbalancenowpayment', $main],
        ['maxbalancenowpayment', $max],
        ['statusiranpay3', 'oniranpay3'],
        ['apiiranpay', '0'],
        ['chashbackiranpay3', '0'],
        ['helpcart', '2'],
        ['helpaqayepardakht', '2'],
        ['helpstar', '2'],
        ['helpplisio', '2'],
        ['helpiranpay1', '2'],
        ['helpiranpay2', '2'],
        ['helpiranpay3', '2'],
        ['helpperfectmony', '2'],
        ['helpzarinpal', '2'],
        ['helpzarinpey', '2'],
        ['helpnowpayment', '2'],
        ['helpofflinearze', '2'],
        ['autoconfirmcart', 'offauto'],
        ['cashbacknowpayment', '0'],
        ['statusstar', '0'],
        ['statusnowpayment', '0'],
        ['Exception_auto_cart', '{}'],
        ['marchent_floypay', '0'],
    ];
    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE PaySetting (
        NamePay varchar(500) PRIMARY KEY NOT NULL,
        ValuePay TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table PaySetting" . mysqli_error($connect);
        }

        foreach ($settings as $setting) {
            $connect->query("INSERT INTO PaySetting (NamePay, ValuePay) VALUES ('{$setting[0]}', '{$setting[1]}')");
        }
    } else {
        foreach ($settings as $setting) {
            $connect->query("INSERT IGNORE INTO PaySetting (NamePay, ValuePay) VALUES ('{$setting[0]}', '{$setting[1]}')");
        }





    }
} catch (Exception $e) {
    rf_log_exception($e);
}
//----------------------- [ Discount ] --------------------- //
try {
    $result = $connect->query("SHOW TABLES LIKE 'DiscountSell'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE DiscountSell (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        codeDiscount varchar(1000)  NOT NULL,
        price varchar(200)  NOT NULL,
        limitDiscount varchar(500)  NOT NULL,
        agent varchar(500)  NOT NULL,
        usefirst varchar(100)  NOT NULL,
        useuser varchar(100)  NOT NULL,
        code_product varchar(100)  NOT NULL,
        code_panel varchar(100)  NOT NULL,
        time varchar(100)  NOT NULL,
        type varchar(100)  NOT NULL,
        usedDiscount varchar(500) NOT NULL)");
        if (!$result) {
            echo "table DiscountSell" . mysqli_error($connect);
        }
    } else {
        $Check_filde = $connect->query("SHOW COLUMNS FROM DiscountSell LIKE 'agent'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE DiscountSell ADD agent VARCHAR(100)");
            echo "The agent discount field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM DiscountSell LIKE 'type'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE DiscountSell ADD type VARCHAR(100)");
            echo "The agent type field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM DiscountSell LIKE 'time'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE DiscountSell ADD time VARCHAR(100)");
            echo "The agent time field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM DiscountSell LIKE 'code_panel'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE DiscountSell ADD code_panel VARCHAR(100)");
            echo "The code_panel discount field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM DiscountSell LIKE 'code_product'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE DiscountSell ADD code_product VARCHAR(100)");
            echo "The code_product discount field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM DiscountSell LIKE 'useuser'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE DiscountSell ADD useuser VARCHAR(100)");
            echo "The useuser discount field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM DiscountSell LIKE 'usefirst'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE DiscountSell ADD usefirst VARCHAR(100)");
            echo "The usefirst discount field was added ✅";
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
