<?php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/includes/data_user/ibsng.php');
}

    $UsernameData = GetUserIBsng($Get_Data_Panel['name_panel'], $username);
    if (!$UsernameData['status']) {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => $UsernameData['msg']
        );
    } else {
        $UsernameData = $UsernameData['data'];
        $data_limit = $UsernameData['data_limit'];
        $expire = strtotime($UsernameData['absolute_expire_date']);
        $UsernameData['enable'] = "active";
        $Output = array(
            'status' => $UsernameData['enable'],
            'username' => $UsernameData['username'],
            'data_limit' => $data_limit,
            'expire' => $expire,
            'online_at' => strtolower($UsernameData['status']),
            'used_traffic' => $UsernameData['used_traffic'],
            'links' => [],
            'subscription_url' => $UsernameData['password'],
            'sub_updated_at' => null,
            'sub_last_user_agent' => null,
        );
    }


return $Output;
