<?php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/includes/data_user/WGDashboard.php');
}

    $UsernameData = get_userwg($username, $Get_Data_Panel['name_panel']);
    if (isset($UsernameData['status']) && $UsernameData['status'] === false && !isset($UsernameData['id'])) {
        return array(
            'status' => 'Unsuccessful',
            'msg' => isset($UsernameData['msg']) ? $UsernameData['msg'] : ''
        );
    }
    $invoiceinfo = select("invoice", "*", "username", $username, "select");
    $infoconfig = isset($invoiceinfo['user_info']) ? json_decode($invoiceinfo['user_info'], true) : json_encode(array());
    if (!isset($UsernameData['id'])) {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => isset($UsernameData['msg']) ? $UsernameData['msg'] : ''
        );
    } else {
        $jobtime = [];
        $jobvolume = [];
        foreach ($UsernameData['jobs'] as $job) {
            if ($job['Field'] == "total_data") {
                $jobvolume = $job;
            } elseif ($job['Field'] == "date") {
                $jobtime = $job;
            }
        }
        if (intval($invoiceinfo['Service_time']) == 0) {
            $expire = 0;
        } else {
            if (isset($jobtime['Value'])) {
                $expire = strtotime($jobtime['Value']);
            } else {
                $expire = 0;
            }
        }
        $status = "active";
        if (!$UsernameData['configuration']['Status'])
            $status = "disabled";
        if ($expire != 0 and $expire - time() < 0) {
            $status = "expired";
        }
        $data_useage = ($UsernameData['total_data'] * pow(1024, 3)) + ($UsernameData['cumu_data'] * pow(1024, 3));
        if (($jobvolume['Value'] * pow(1024, 3)) < $data_useage) {
            $status = "limited";
        }
        $download_config = downloadconfig($Get_Data_Panel['name_panel'], $UsernameData['id']);
        if (isset($download_config['status']) && $download_config['status'] === false) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => isset($download_config['msg']) ? $download_config['msg'] : ''
            );
        }
        if (!empty($download_config['status']) && $download_config['status'] != 200) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $download_config['status']
            );
        }
        if (!empty($download_config['error'])) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $download_config['error']
            );
        }
        $download_config = json_decode($download_config['body'], true)['data'];
        $Output = array(
            'status' => $status,
            'username' => $UsernameData['name'],
            'data_limit' => $jobvolume['Value'] * pow(1024, 3),
            'expire' => $expire,
            'online_at' => null,
            'used_traffic' => $data_useage,
            'links' => [],
            'subscription_url' => strval($download_config['file']),
            'sub_updated_at' => null,
            'sub_last_user_agent' => null,
        );
    }


return $Output;
