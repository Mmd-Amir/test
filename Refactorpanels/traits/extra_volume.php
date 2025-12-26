<?php
// Auto-refactored from legacy panels.php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/traits/extra_volume.php');
}

trait ManagePanelExtraVolumeTrait
{
function extra_volume($username_account, $code_panel, $limit_volume_new)
{
    $panel = select("marzban_panel", "*", "code_panel", $code_panel, "select");
    $invoice = select("invoice", "*", "username", $username_account, "select");
    if ($panel == false) {
        return array(
            'status' => false,
            'msg' => 'data not found'
        );
    }
    $notif_value = json_decode($invoice['notifctions'], true);
    $notifctions = json_encode(array(
        'volume' => false,
        'time' => $notif_value['time'],
    ));
    update("invoice", "notifctions", $notifctions, 'id_invoice', $invoice['id_invoice']);
    $user_info = $this->DataUser($panel['name_panel'], $username_account);
    if ($user_info['status'] == "Unsuccessful") {
        return array(
            'status' => false,
            'msg' => $user_info['msg']
        );
    }
    $old_limit_volume = $user_info['data_limit'];
    $new_limit = $limit_volume_new == 0 ? 0 : ($limit_volume_new * pow(1024, 3)) + $old_limit_volume;
    $inbound_id = isset($panel['inboundid']) ? $panel['inboundid'] : 1;
    $inbounds = is_string($panel['inbounds']) ? json_decode($panel['inbounds']) : "{}";
    if ($panel['type'] != "WGDashboard") {
        update("invoice", 'user_info', null, "username", $username_account);
    }
    update("invoice", 'uuid', null, "username", $username_account);
    update("invoice", 'Status', "active", "username", $username_account);
    if ($panel['type'] == "marzban") {
        $data = array(
            'data_limit' => $new_limit,
            'inbounds' => $inbounds,
        );
        if ($invoice != false && $invoice['uuid'] != null) {
            $data['proxies'] = json_decode($invoice['uuid'], true);
        }
    } elseif ($panel['type'] == "marzneshin") {
        $data = array(
            'data_limit' => $new_limit,
        );
    } elseif ($panel['type'] == "x-ui_single") {
        $data = array(
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "totalGB" => $new_limit,
                        )
                    ),
                )
            ),
        );
    } elseif ($panel['type'] == "alireza_single") {
        $data = array(
            'id' => intval($inbound_id),
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "totalGB" => $new_limit,
                        )
                    ),
                )
            ),
        );
    } elseif ($panel['type'] == "hiddify") {
        $data_limit = ($user_info['data_limit'] / pow(1024, 3)) + $limit_volume_new;
        $datauser = getdatauser($username_account, $panel['name_panel']);
        $data = array(
            "current_usage_GB" => $datauser['current_usage_GB'],
            "usage_limit_GB" => $new_limit / pow(1024, 3),
        );
    } elseif ($panel['type'] == "WGDashboard") {
        $allowResponse = allowAccessPeers($panel['name_panel'], $username_account);
        if (isset($allowResponse['status']) && $allowResponse['status'] === false) {
            return array(
                'status' => false,
                'msg' => isset($allowResponse['msg']) ? $allowResponse['msg'] : ''
            );
        }
        $datauser = get_userwg($username_account, $panel['name_panel']);
        if (isset($datauser['status']) && $datauser['status'] === false && !isset($datauser['id'])) {
            return array(
                'status' => false,
                'msg' => isset($datauser['msg']) ? $datauser['msg'] : ''
            );
        }
        $count = 0;
        foreach ($datauser['jobs'] as $jobsvolume) {
            if ($jobsvolume['Field'] == "total_data") {
                break;
            }
            $count += 1;
        }
        if (isset($datauser['jobs'][$count])) {
            $datam = array(
                "Job" => $datauser['jobs'][$count],
            );
            $deleteJob = deletejob($panel['name_panel'], $datam);
            if (isset($deleteJob['status']) && $deleteJob['status'] === false) {
                return array(
                    'status' => false,
                    'msg' => isset($deleteJob['msg']) ? $deleteJob['msg'] : ''
                );
            }
        } else {
            $resetResult = $this->ResetUserDataUsage($username_account, $panel['name_panel']);
            if (isset($resetResult['status']) && $resetResult['status'] === false) {
                return array(
                    'status' => false,
                    'msg' => isset($resetResult['msg']) ? $resetResult['msg'] : ''
                );
            }
        }
        $log = setjob($panel['name_panel'], "total_data", $new_limit / pow(1024, 3), $datauser['id']);
        if (isset($log['status']) && $log['status'] === false) {
            return array(
                'status' => false,
                'msg' => isset($log['msg']) ? $log['msg'] : ''
            );
        }
        return array(
            'status' => true,
            'data' => $log
        );
    } elseif ($panel['type'] == "s_ui") {
        $data = array(
            "volume" => $new_limit,
        );
    }
    $extra_volume = $this->Modifyuser($username_account, $panel['name_panel'], $data);
    if ($extra_volume['status'] == false) {
        return array(
            'status' => false,
            'msg' => $extra_volume['msg']
        );
    }
    return $extra_volume;
}
}
