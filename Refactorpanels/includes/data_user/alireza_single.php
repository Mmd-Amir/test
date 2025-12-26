<?php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/includes/data_user/alireza_single.php');
}

    $UsernameData2 = get_clinetsalireza($username, $Get_Data_Panel['name_panel']);
    if (!is_array($UsernameData2)) {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => "user not found"
        );
    }
    $UsernameData = $UsernameData2[1];
    $UsernameData2 = $UsernameData2[0];
    $expire = $UsernameData['expiryTime'] / 1000;
    if (!$UsernameData['id']) {
        if (!isset($UsernameData['msg']))
            $UsernameData['msg'] = null;
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => $UsernameData['msg']
        );
    } else {
        if ($UsernameData['enable']) {
            $UsernameData['enable'] = "active";
        } else {
            $UsernameData['enable'] = "deactivev";
        }
        $subId = $UsernameData2['subId'];
        $status_user = get_onlineclialireza($Get_Data_Panel['name_panel'], $username);
        if ((intval($UsernameData['total'])) != 0) {
            if ((intval($UsernameData['total']) - ($UsernameData['up'] + $UsernameData['down'])) <= 0)
                $UsernameData['enable'] = "limited";
        }
        if (intval($UsernameData['expiryTime']) != 0) {
            if ($expire - time() <= 0)
                $UsernameData['enable'] = "expired";
        }
        $Output = array(
            'status' => $UsernameData['enable'],
            'username' => $UsernameData['email'],
            'data_limit' => $UsernameData['total'],
            'expire' => $expire,
            'online_at' => $status_user,
            'used_traffic' => $UsernameData['up'] + $UsernameData['down'],
            'links' => [outputlunk($Get_Data_Panel['linksubx'] . "/{$UsernameData2['subId']}")],
            'subscription_url' => $Get_Data_Panel['linksubx'] . "/{$UsernameData2['subId']}",
            'sub_updated_at' => null,
            'sub_last_user_agent' => null,
        );
    }


return $Output;
