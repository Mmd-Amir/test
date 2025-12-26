<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/telegram/messaging.php'); }

if (!function_exists('sendMessageService')) {
    function sendMessageService($panel_info, $config, $sub_link, $username_service, $reply_markup, $caption, $invoice_id, $user_id = null, $image = 'images.jpg')
    {
        global $setting, $from_id;
        $config = normalizeServiceConfigs($config);
        if (!check_active_btn($setting['keyboardmain'], "text_help"))
            $reply_markup = null;
        $user_id = $user_id == null ? $from_id : $user_id;
        $STATUS_SEND_MESSAGE_PHOTO = $panel_info['config'] == "onconfig" && count($config) != 1 ? false : true;
        $out_put_qrcode = "";
        if ($panel_info['type'] == "Manualsale" || $panel_info['type'] == "ibsng" || $panel_info['type'] == "mikrotik") {
        }
        if ($panel_info['sublink'] == "onsublink" && $panel_info['config']) {
            $out_put_qrcode = $sub_link;
        } elseif ($panel_info['sublink'] == "onsublink") {
            $out_put_qrcode = $sub_link;
        } elseif ($panel_info['config'] == "onconfig") {
            $out_put_qrcode = $config[0];
        }
        if ($STATUS_SEND_MESSAGE_PHOTO) {
            $urlimage = "$user_id$invoice_id.png";
            $qrCode = createqrcode($out_put_qrcode);
            file_put_contents($urlimage, $qrCode->getString());
            if (!addBackgroundImage($urlimage, $qrCode, $image)) {
                error_log("Unable to apply background image for QR code using path '{$image}'");
            }
            telegram('sendphoto', [
                'chat_id' => $user_id,
                'photo' => new CURLFile($urlimage),
                'reply_markup' => $reply_markup,
                'caption' => $caption,
                'parse_mode' => "HTML",
            ]);
            unlink($urlimage);
            if ($panel_info['type'] == "WGDashboard") {
                $urlimage = "{$panel_info['inboundid']}_{$username_service}.conf";
                file_put_contents($urlimage, $sub_link);
                sendDocument($user_id, $urlimage, "⚙️ کانفیگ شما");
                unlink($urlimage);
            }
        } else {
            sendmessage($user_id, $caption, $reply_markup, 'HTML');
        }
        if ($panel_info['config'] == "onconfig" && $setting['status_keyboard_config'] == "1") {
            if (is_array($config)) {
                $validConfigs = array_values(array_filter($config, function ($item) {
                    return is_string($item) && trim($item) !== '';
                }));

                if (!empty($validConfigs)) {
                    $keyboardPayload = keyboard_config($validConfigs, $invoice_id, false);
                    $configButtonCount = 0;
                    $keyboardData = json_decode($keyboardPayload, true);

                    if (is_array($keyboardData) && isset($keyboardData['inline_keyboard']) && is_array($keyboardData['inline_keyboard'])) {
                        foreach ($keyboardData['inline_keyboard'] as $row) {
                            if (!is_array($row)) {
                                continue;
                            }

                            foreach ($row as $button) {
                                if (!is_array($button)) {
                                    continue;
                                }

                                $buttonText = $button['text'] ?? '';
                                $callbackData = $button['callback_data'] ?? '';

                                if ($buttonText === 'دریافت کانفیگ' && is_string($callbackData) && strpos($callbackData, 'configget_') === 0) {
                                    ++$configButtonCount;
                                }
                            }
                        }
                    } else {
                        error_log('Failed to decode keyboard payload for configuration prompt');
                    }

                    if ($configButtonCount > 1) {
                        sendmessage($user_id, "📌 جهت دریافت کانفیگ روی دکمه دریافت کانفیگ کلیک کنید", $keyboardPayload, 'HTML');
                    }
                }
            }
        }
    }
}
