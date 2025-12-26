<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/panel/output.php'); }

if (!function_exists('outputlunk')) {
    function outputlunk($text)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $text);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 6000);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            return null;
        } else {
            return $response;
        }

        curl_close($ch);
    }
}


if (!function_exists('outputlunksub')) {
    function outputlunksub($url)
    {
        $ch = curl_init();
        var_dump($url);
        curl_setopt($ch, CURLOPT_URL, "$url/info");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $headers = array();
        $headers[] = 'Accept: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        return $result;
        curl_close($ch);
    }
}


if (!function_exists('outtypepanel')) {
    function outtypepanel($typepanel, $message)
    {
        global $from_id, $optionMarzban, $optionX_ui_single, $optionhiddfy, $optionalireza, $optionalireza_single, $optionmarzneshin, $option_mikrotik, $optionwg, $options_ui, $optioneylanpanel, $optionibsng;
        if ($typepanel == "marzban") {
            sendmessage($from_id, $message, $optionMarzban, 'HTML');
        } elseif ($typepanel == "x-ui_single") {
            sendmessage($from_id, $message, $optionX_ui_single, 'HTML');
        } elseif ($typepanel == "hiddify") {
            sendmessage($from_id, $message, $optionhiddfy, 'HTML');
        } elseif ($typepanel == "alireza_single") {
            sendmessage($from_id, $message, $optionalireza_single, 'HTML');
        } elseif ($typepanel == "marzneshin") {
            sendmessage($from_id, $message, $optionmarzneshin, 'HTML');
        } elseif ($typepanel == "WGDashboard") {
            sendmessage($from_id, $message, $optionwg, 'HTML');
        } elseif ($typepanel == "s_ui") {
            sendmessage($from_id, $message, $options_ui, 'HTML');
        } elseif ($typepanel == "ibsng") {
            sendmessage($from_id, $message, $optionibsng, 'HTML');
        } elseif ($typepanel == "mikrotik") {
            sendmessage($from_id, $message, $option_mikrotik, 'HTML');
        } elseif ($typepanel == "eylanpanel") {
            sendmessage($from_id, $message, $optioneylanpanel, 'HTML');
        }
    }
}


if (!function_exists('normalizeServiceConfigs')) {
    function normalizeServiceConfigs($configs, $subscriptionUrl = null)
    {
        $normalized = [];

        if (is_array($configs)) {
            foreach ($configs as $item) {
                if (!is_string($item)) {
                    continue;
                }
                $item = trim($item);
                if ($item === '') {
                    continue;
                }
                $normalized[] = $item;
            }
        } elseif (is_string($configs)) {
            $parts = preg_split("/\r\n|\n|\r/", $configs);
            if (is_array($parts)) {
                foreach ($parts as $part) {
                    $part = trim($part);
                    if ($part === '') {
                        continue;
                    }
                    $normalized[] = $part;
                }
            }
        }

        $subscriptionUrl = is_string($subscriptionUrl) ? trim($subscriptionUrl) : '';
        if (empty($normalized) && $subscriptionUrl !== '') {
            if (preg_match('/^https?:/i', $subscriptionUrl)) {
                $fetched = outputlunk($subscriptionUrl);
                if (is_string($fetched) && $fetched !== '') {
                    if (isBase64($fetched)) {
                        $fetched = base64_decode($fetched);
                    }
                    $parts = preg_split("/\r\n|\n|\r/", $fetched);
                    if (is_array($parts)) {
                        foreach ($parts as $part) {
                            $part = trim($part);
                            if ($part === '') {
                                continue;
                            }
                            $normalized[] = $part;
                        }
                    }
                }
            } else {
                $normalized[] = $subscriptionUrl;
            }
        }

        return array_values($normalized);
    }
}


if (!function_exists('channel')) {
    function channel(array $id_channel)
    {
        global $from_id;
        $channel_link = array();
        foreach ($id_channel as $channel) {
            $response = telegram('getChatMember', [
                'chat_id' => $channel,
                'user_id' => $from_id
            ]);
            if ($response['ok']) {
                if (!in_array($response['result']['status'], ['member', 'creator', 'administrator'])) {
                    $channel_link[] = $channel;
                }
            }
        }
        if (count($channel_link) == 0) {
            return [];
        } else {
            return $channel_link;
        }
    }
}


if (!function_exists('check_active_btn')) {
    function check_active_btn($keyboard, $text_var)
    {
        $trace_keyboard = json_decode($keyboard, true)['keyboard'];
        $status = false;
        foreach ($trace_keyboard as $key => $callback_set) {
            foreach ($callback_set as $keyboard_key => $keyboard) {
                if ($keyboard['text'] == $text_var) {
                    $status = true;
                    break;
                }
            }
        }
        return $status;
    }
}


if (!function_exists('savedata')) {
    function savedata($type, $namefiled, $valuefiled)
    {
        global $from_id;
        if ($type == "clear") {
            $datauser = [];
            $datauser[$namefiled] = $valuefiled;
            $data = json_encode($datauser);
            update("user", "Processing_value", $data, "id", $from_id);
        } elseif ($type == "save") {
            $userdata = select("user", "*", "id", $from_id, "select");
            $dataperevieos = json_decode($userdata['Processing_value'], true);
            $dataperevieos[$namefiled] = $valuefiled;
            update("user", "Processing_value", json_encode($dataperevieos), "id", $from_id);
        }
    }
}


if (!function_exists('step')) {
    function step($step, $from_id)
    {
        global $pdo;
        $stmt = $pdo->prepare('UPDATE user SET step = ? WHERE id = ?');
        $stmt->execute([$step, $from_id]);
        clearSelectCache('user');
    }
}


if (!function_exists('checkConnection')) {
    function checkConnection($address, $port)
    {
        $socket = @stream_socket_client("tcp://$address:$port", $errno, $errstr, 5);
        if ($socket) {
            fclose($socket);
            return true;
        } else {
            return false;
        }
    }
}
