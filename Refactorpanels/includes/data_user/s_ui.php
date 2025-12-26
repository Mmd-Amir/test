<?php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/includes/data_user/s_ui.php');
}

    $UsernameData = GetClientsS_UI($username, $Get_Data_Panel['name_panel']);
    $onlinestatus = get_onlineclients_ui($Get_Data_Panel['name_panel'], $username);
    if (!isset($UsernameData['id'])) {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => $UsernameData['msg']
        );
    } else {
        $links = [];
        if (is_array($UsernameData['links'])) {
            foreach ($UsernameData['links'] as $config) {
                $links[] = $config['uri'];
            }
        }
        $data_limit = $UsernameData['volume'];
        $useage = $UsernameData['up'] + $UsernameData['down'];
        $RemainingVolume = $data_limit - $useage;
        $expire = $UsernameData['expiry'];
        if ($UsernameData['enable']) {
            $UsernameData['enable'] = "active";
        } elseif ($data_limit != 0 and $RemainingVolume < 0) {
            $UsernameData['enable'] = "limited";
        } elseif ($expire - time() < 0 and $expire != 0) {
            $UsernameData['enable'] = "expired";
        } else {
            $UsernameData['enable'] = "disabled";
        }
        $setting_app = get_settig($Get_Data_Panel['name_panel']);
        $url = explode(":", $Get_Data_Panel['url_panel']);
        $url_sub = $url[0] . ":" . $url[1] . ":" . $setting_app['subPort'] . $setting_app['subPath'] . $username;
        $Output = array(
            'status' => $UsernameData['enable'],
            'username' => $UsernameData['name'],
            'data_limit' => $data_limit,
            'expire' => $expire,
            'online_at' => $onlinestatus,
            'used_traffic' => $useage,
            'links' => $links,
            'subscription_url' => $url_sub,
            'sub_updated_at' => null,
            'sub_last_user_agent' => null,
        );
    }


return $Output;
