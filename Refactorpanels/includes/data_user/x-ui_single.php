<?php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/includes/data_user/x-ui_single.php');
}

    $user_data = get_clinets($username, $Get_Data_Panel['name_panel']);
    if (!empty($user_data['error'])) {
        return array(
            'status' => 'Unsuccessful',
            'msg' => $user_data['error']
        );
    } elseif (!empty($user_data['status']) && $user_data['status'] != 200) {
        return array(
            'status' => 'Unsuccessful',
            'msg' => json_encode($user_data)
        );
    }
    $user_data = json_decode($user_data['body'], true);

    if (!is_array($user_data)) {
        return array(
            'status' => 'Unsuccessful',
            'msg' => 'object invalid'
        );
    }
    if (empty($user_data['obj'])) {
        return array(
            'status' => 'Unsuccessful',
            'msg' => "User not found"
        );
    }
    $user_data = $user_data['obj'];
    $expire = $user_data['expiryTime'] / 1000;
    if ($user_data['enable']) {
        $user_data['enable'] = "active";
    } else {
        $user_data['enable'] = "disabled";
    }
    if ((intval($user_data['total'])) != 0) {
        if ((intval($user_data['total']) - ($user_data['up'] + $user_data['down'])) <= 0)
            $user_data['enable'] = "limited";
    }
    if (intval($user_data['expiryTime']) != 0) {
        if ($expire - time() <= 0)
            $user_data['enable'] = "expired";
    }
    if ($user_data['expiryTime'] < -10000) {
        $user_data['enable'] = "on_hold";
        $expire = 0;
    }
    $subscriptionUrl = rtrim($Get_Data_Panel['linksubx'], '/') . "/{$user_data['subId']}";
    $linksub = $subscriptionUrl;
    $links_user_raw = outputlunk($subscriptionUrl);
    if (!is_string($links_user_raw)) {
        $links_user_raw = '';
    }
    if (isBase64($links_user_raw)) {
        $links_user_raw = base64_decode($links_user_raw);
    }
    $links_user = preg_split('/\R/', trim($links_user_raw));
    if (!is_array($links_user)) {
        $links_user = [];
    }
    $links_user = array_values(array_filter(array_map('trim', $links_user), function ($ln) {
        return $ln !== '';
    }));
    $singleLink = $links_user[0] ?? null;
    if (!$singleLink || !preg_match('/^(vless|vmess|trojan):\/\//i', $singleLink)) {
        if (is_file('cookie.txt')) {
            @unlink('cookie.txt');
        }
        login($Get_Data_Panel['code_panel']);
        $singleLink = get_single_link_smart(
            $Get_Data_Panel['url_panel'],
            $Get_Data_Panel['inboundid'],
            $subscriptionUrl,
            $username,
            $Get_Data_Panel['name_panel'],
            $Get_Data_Panel['code_panel'] ?? null
        );
        if (is_file('cookie.txt')) {
            @unlink('cookie.txt');
        }
        if (!$singleLink) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => 'Unable to build single link'
            );
        }
        array_unshift($links_user, $singleLink);
    }
    if ($inoice != false)
        $linksub = "https://$domainhosts/sub/" . $inoice['id_invoice'];
    $user_data['lastOnline'] = $user_data['lastOnline'] == 0 ? "offline" : date('Y-m-d H:i:s', $user_data['lastOnline'] / 1000);
    $Output = array(
        'status' => $user_data['enable'],
        'username' => $user_data['email'],
        'data_limit' => $user_data['total'],
        'expire' => $expire,
        'online_at' => $user_data['lastOnline'],
        'used_traffic' => $user_data['up'] + $user_data['down'],
        'links' => $links_user,
        'subscription_url' => $linksub,
        'sub_updated_at' => null,
        'sub_last_user_agent' => null,
    );



return $Output;
