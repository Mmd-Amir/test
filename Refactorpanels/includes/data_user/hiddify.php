<?php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/includes/data_user/hiddify.php');
}

    $UsernameData = getdatauser($username, $Get_Data_Panel['name_panel']);
    if (!isset($UsernameData)) {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => "Not Connected TO paonel"
        );
    } elseif (isset($UsernameData['message'])) {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => $UsernameData['message']
        );
    } else {
        $startDate = $UsernameData['start_date'] ?? null;
        if ($startDate === null) {
            $date = 0;
        } else {
            $start_date = strtotime($startDate);
            $package_days = isset($UsernameData['package_days']) ? intval($UsernameData['package_days']) : 0;
            $end_date = $start_date + ($package_days * 86400);
            $date = strtotime(date("Y-m-d H:i:s", $end_date));
        }
        $usageLimit = isset($UsernameData['usage_limit_GB']) ? $UsernameData['usage_limit_GB'] * pow(1024, 3) : 0;
        $currentUsage = isset($UsernameData['current_usage_GB']) ? $UsernameData['current_usage_GB'] * pow(1024, 3) : 0;
        $uuid = $UsernameData['uuid'] ?? null;
        $linksuburl = $uuid ? "{$Get_Data_Panel['linksubx']}/{$uuid}/" : $Get_Data_Panel['linksubx'];
        $lastOnline = $UsernameData['last_online'] ?? null;
        if ($lastOnline == "1-01-01 00:00:00") {
            $lastOnline = null;
        }
        $remainingTraffic = $usageLimit - $currentUsage;
        if ($usageLimit > 0 && $remainingTraffic <= 0) {
            $status = "limited";
        } elseif ($date != 0 && ($date - time()) <= 0) {
            $status = "expired";
        } elseif ($startDate === null) {
            $status = "on_hold";
        } else {
            $status = "active";
        }
        if ($inoice != false) {
            $linksuburl = "https://$domainhosts/sub/" . $inoice['id_invoice'];
        }
        $Output = array(
            'status' => $status,
            'username' => $UsernameData['name'] ?? ($UsernameData['email'] ?? $username),
            'data_limit' => $usageLimit,
            'expire' => $date,
            'online_at' => $lastOnline,
            'used_traffic' => $currentUsage,
            'links' => [],
            'subscription_url' => $linksuburl,
            'sub_updated_at' => null,
            'sub_last_user_agent' => null,
        );
    }


return $Output;
