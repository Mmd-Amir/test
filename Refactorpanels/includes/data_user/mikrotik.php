<?php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/includes/data_user/mikrotik.php');
}

    $UsernameData = GetUsermikrotik($Get_Data_Panel['name_panel'], $username)[0];
    if (isset($UsernameData['error'])) {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => $UsernameData['msg']
        );
    } else {
        $invocie = select("invoice", "*", "username", $username, "select");
        $traffic_get = GetUsermikrotik_volume($Get_Data_Panel['name_panel'], $UsernameData['.id']);
        $used_traffic = $traffic_get['total-upload'] + $traffic_get['total-download'];
        $data_limit = $invocie['Volume'] * pow(1024, 3);
        $expire = $invocie['time_sell'] + ($invocie['Service_time'] * 86400);
        $UsernameData['enable'] = "active";
        $Output = array(
            'status' => $UsernameData['enable'],
            'username' => $invocie['username'],
            'data_limit' => $data_limit,
            'expire' => $expire,
            'online_at' => null,
            'used_traffic' => $used_traffic,
            'links' => [],
            'subscription_url' => $UsernameData['password'],
            'sub_updated_at' => null,
            'sub_last_user_agent' => null,
        );
    }


return $Output;
