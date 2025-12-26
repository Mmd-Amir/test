<?php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/includes/data_user/marzban.php');
}

    $UsernameData = getuser($username, $Get_Data_Panel['name_panel']);
    if (!empty($UsernameData['error'])) {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => $UsernameData['error']
        );
    } elseif (!empty($UsernameData['status']) && $UsernameData['status'] == 500) {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => $UsernameData['status']
        );
    } else {
        $UsernameData = json_decode($UsernameData['body'], true);
        if (!empty($UsernameData['detail'])) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['detail']
            );
        }
        if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $UsernameData['subscription_url'])) {
            $UsernameData['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($UsernameData['subscription_url'], "/");
        }
        if ($new_marzban) {
            // PHP 8.1+: avoid passing null to strtotime() (deprecated). Keep legacy behavior (false) when empty.
            $expireRaw = $UsernameData['expire'] ?? null;
            if ($expireRaw === null || $expireRaw === '' ) {
                $UsernameData['expire'] = false;
            } elseif (is_numeric($expireRaw)) {
                $UsernameData['expire'] = (int)$expireRaw;
            } else {
                $t = strtotime((string)$expireRaw);
                $UsernameData['expire'] = ($t === false) ? false : $t;
            }
            $UsernameData['links'] = base64_decode(outputlunk($UsernameData['subscription_url']));
            $UsernameData['links'] = explode("\n", $UsernameData['links']);
            $sublist_update = get_list_update($name_panel, $username);
            if (!empty($sublist_update['error'])) {
                return array(
                    'status' => 'Unsuccessful',
                    'msg' => $sublist_update['error']
                );
            } elseif (!empty($sublist_update['status']) && $sublist_update['status'] == 500) {
                return array(
                    'status' => 'Unsuccessful',
                    'msg' => $sublist_update['status']
                );
            }
            $sublist_update_body = json_decode($sublist_update['body'], true);
            if (!empty($sublist_update_body['updates']) && is_array($sublist_update_body['updates'])) {
                $first_update = $sublist_update_body['updates'][0];
                $UsernameData['sub_updated_at'] = isset($first_update['created_at']) ? $first_update['created_at'] : null;
                $UsernameData['sub_last_user_agent'] = isset($first_update['user_agent']) ? $first_update['user_agent'] : null;
            } else {
                $UsernameData['sub_updated_at'] = isset($UsernameData['sub_updated_at']) ? $UsernameData['sub_updated_at'] : null;
                $UsernameData['sub_last_user_agent'] = isset($UsernameData['sub_last_user_agent']) ? $UsernameData['sub_last_user_agent'] : null;
            }
        } else {
            $UsernameData['expire'] = $UsernameData['expire'];
        }
        if ($inoice != false) {
            $UsernameData['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
        }
        if ($new_marzban) {
            $UsernameData['proxies'] = isset($UsernameData['proxy_settings']) ? $UsernameData['proxy_settings'] : null;
        }
        $Output = array(
            'status' => $UsernameData['status'],
            'username' => $UsernameData['username'],
            'data_limit' => $UsernameData['data_limit'],
            'expire' => $UsernameData['expire'],
            'online_at' => $UsernameData['online_at'],
            'used_traffic' => $UsernameData['used_traffic'],
            'links' => $UsernameData['links'],
            'subscription_url' => $UsernameData['subscription_url'],
            'sub_updated_at' => $UsernameData['sub_updated_at'],
            'sub_last_user_agent' => $UsernameData['sub_last_user_agent'],
            'uuid' => $UsernameData['proxies'],
            'data_limit_reset' => $UsernameData['data_limit_reset_strategy']
        );
    }


return $Output;