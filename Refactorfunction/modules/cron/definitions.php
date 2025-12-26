<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/cron/definitions.php'); }

if (!function_exists('getCronJobDefinitions')) {
    function getCronJobDefinitions(): array
    {
        return [
            'statusday' => [
                'script' => 'statusday.php',
                'admin_label' => 'کرون وضعیت روزانه',
                'instruction' => '🕒 بررسی وضعیت روزانه — %s',
                'default' => ['unit' => 'minute', 'value' => 15],
            ],
            'croncard' => [
                'script' => 'croncard.php',
                'admin_label' => 'کرون کارت‌به‌کارت',
                'instruction' => '💳 انجام تراکنش‌های کارت‌به‌کارت — %s',
                'default' => ['unit' => 'minute', 'value' => 1],
            ],
            'notifications' => [
                'script' => 'NoticationsService.php',
                'admin_label' => 'کرون اعلان‌ها',
                'instruction' => '🔔 سرویس اعلان‌ها (Notification Service) — %s',
                'default' => ['unit' => 'minute', 'value' => 1],
            ],
            'payment_expire' => [
                'script' => 'payment_expire.php',
                'admin_label' => 'کرون انقضای پرداخت',
                'instruction' => '💳 بررسی انقضای پرداخت‌ها — %s',
                'default' => ['unit' => 'minute', 'value' => 5],
            ],
            'sendmessage' => [
                'script' => 'sendmessage.php',
                'admin_label' => 'کرون ارسال پیام',
                'instruction' => '📩 ارسال پیام‌ها — %s',
                'default' => ['unit' => 'minute', 'value' => 1],
            ],
            'plisio' => [
                'script' => 'plisio.php',
                'admin_label' => 'کرون Plisio',
                'instruction' => '💰 پردازش پرداخت‌های Plisio — %s',
                'default' => ['unit' => 'minute', 'value' => 3],
            ],
            'activeconfig' => [
                'script' => 'activeconfig.php',
                'admin_label' => 'کرون فعال‌سازی تنظیمات',
                'instruction' => '⚙️ فعال‌سازی تنظیمات جدید — %s',
                'default' => ['unit' => 'minute', 'value' => 1],
            ],
            'disableconfig' => [
                'script' => 'disableconfig.php',
                'admin_label' => 'کرون غیرفعال‌سازی تنظیمات',
                'instruction' => '🚫 غیرفعال‌سازی تنظیمات قدیمی — %s',
                'default' => ['unit' => 'minute', 'value' => 1],
            ],
            'iranpay' => [
                'script' => 'iranpay1.php',
                'admin_label' => 'کرون ایران‌پی',
                'instruction' => '🇮🇷 بررسی وضعیت پرداخت ایران‌پی — %s',
                'default' => ['unit' => 'minute', 'value' => 1],
            ],
            'backup' => [
                'script' => 'backupbot.php',
                'admin_label' => 'کرون بکاپ',
                'instruction' => '🗂 تهیه نسخه‌ی پشتیبان (Backup) — %s',
                'default' => ['unit' => 'hour', 'value' => 5],
            ],
            'gift' => [
                'script' => 'gift.php',
                'admin_label' => 'کرون هدایا',
                'instruction' => '🎁 ارسال هدایا (Gift System) — %s',
                'default' => ['unit' => 'minute', 'value' => 2],
            ],
            'lottery' => [
                'script' => 'lottery.php',
                'admin_label' => 'قرعه‌کشی شبانه',
                'instruction' => '🎁 قرعه‌کشی شبانه — %s',
                'default' => ['unit' => 'minute', 'value' => 1],
            ],
            'expireagent' => [
                'script' => 'expireagent.php',
                'admin_label' => 'کرون انقضای نمایندگان',
                'instruction' => '👥 بررسی انقضای نمایندگان — %s',
                'default' => ['unit' => 'minute', 'value' => 30],
            ],
            'on_hold' => [
                'script' => 'on_hold.php',
                'admin_label' => 'کرون سرویس‌های معلق',
                'instruction' => '⏸ بررسی وضعیت سفارش‌های معلق — %s',
                'default' => ['unit' => 'minute', 'value' => 15],
            ],
            'configtest' => [
                'script' => 'configtest.php',
                'admin_label' => 'کرون تست تنظیمات',
                'instruction' => '🧪 تست تنظیمات سیستم — %s',
                'default' => ['unit' => 'minute', 'value' => 2],
            ],
            'uptime_node' => [
                'script' => 'uptime_node.php',
                'admin_label' => 'کرون Uptime نود',
                'instruction' => '🌐 بررسی Uptime نودها — %s',
                'default' => ['unit' => 'minute', 'value' => 15],
            ],
            'uptime_panel' => [
                'script' => 'uptime_panel.php',
                'admin_label' => 'کرون Uptime پنل',
                'instruction' => '🖥 بررسی Uptime پنل‌ها — %s',
                'default' => ['unit' => 'minute', 'value' => 15],
            ],
        ];
    }
}


if (!function_exists('getDefaultCronSchedules')) {
    function getDefaultCronSchedules(): array
    {
        $defaults = [];
        foreach (getCronJobDefinitions() as $key => $definition) {
            $defaults[$key] = $definition['default'];
        }

        return $defaults;
    }
}
