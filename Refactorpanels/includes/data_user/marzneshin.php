<?php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/includes/data_user/marzneshin.php');
}

    $UsernameData = getuserm($username, $Get_Data_Panel['name_panel']);
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
        if (isset($UsernameData['detail']) && $UsernameData['detail']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['detail']
            );
        } elseif (!isset($UsernameData['username'])) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => "Unsuccessful"
            );
        } else {
            if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $UsernameData['subscription_url'])) {
                $UsernameData['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($UsernameData['subscription_url'], "/");
            }
            $UsernameData['status'] = "active";
            if (!$UsernameData['enabled']) {
                $UsernameData['status'] = "disabled";
            }
            if ($UsernameData['expire_strategy'] == "start_on_first_use") {
                $UsernameData['status'] = "on_hold";
            }
            if ($UsernameData['expired']) {
                $UsernameData['status'] = "expired";
            }
            if (($UsernameData['data_limit'] - $UsernameData['used_traffic'] <= 0) and $UsernameData['data_limit'] != null) {
                $UsernameData['status'] = "limtied";
            }
            $UsernameData['links'] = outputlunk($UsernameData['subscription_url']);
            if (isBase64($UsernameData['links'])) {
                $UsernameData['links'] = base64_decode($UsernameData['links']);
            }
            $links_user = explode("\n", trim($UsernameData['links']));
            if ($UsernameData['data_limit'] == null) {
                $UsernameData['data_limit'] = 0;
            }
            if (isset($UsernameData['expire_date'])) {
                $expiretime = strtotime(($UsernameData['expire_date']));
            } else {
                $expiretime = 0;
            }
            if ($inoice != false) {
                $UsernameData['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
            }
            $Output = array(
                'status' => $UsernameData['status'],
                'username' => $UsernameData['username'],
                'data_limit' => $UsernameData['data_limit'],
                'expire' => $expiretime,
                'online_at' => $UsernameData['online_at'],
                'used_traffic' => $UsernameData['used_traffic'],
                'links' => $links_user,
                'subscription_url' => $UsernameData['subscription_url'],
                'sub_updated_at' => $UsernameData['sub_updated_at'],
                'sub_last_user_agent' => $UsernameData['sub_last_user_agent'],
                'uuid' => null
            );
        }
    }


return $Output;
