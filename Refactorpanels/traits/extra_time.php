<?php
// Auto-refactored from legacy panels.php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/traits/extra_time.php');
}

trait ManagePanelExtraTimeTrait
{
function extra_time($username_account, $code_panel, $limit_time_new)
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
        'volume' => $notif_value['volume'],
        'time' => false,
    ));
    update("invoice", "notifctions", $notifctions, 'id_invoice', $invoice['id_invoice']);
    $user_info = $this->DataUser($panel['name_panel'], $username_account);
    if ($user_info['status'] == "Unsuccessful") {
        return array(
            'status' => false,
            'msg' => $user_info['msg']
        );
    }
    $old_limit_time = $user_info['expire'];
    $old_limit_time = time() - $old_limit_time > 0 ? time() : $old_limit_time;
    $new_limit = $limit_time_new == 0 ? 0 : $limit_time_new * 86400 + $old_limit_time;
    $inbound_id = isset($panel['inboundid']) ? $panel['inboundid'] : 1;
    $inbounds = is_string($panel['inbounds']) ? json_decode($panel['inbounds']) : "{}";
    if ($panel['type'] != "WGDashboard") {
        update("invoice", 'user_info', null, "username", $username_account);
    }
    update("invoice", 'uuid', null, "username", $username_account);
    update("invoice", 'Status', "active", "username", $username_account);
    if ($panel['type'] == "marzban") {
        $data = array(
            'expire' => $new_limit,
            'inbounds' => $inbounds,
        );
        if ($invoice != false && $invoice['uuid'] != null) {
            $data['proxies'] = json_decode($invoice['uuid'], true);
        }
    } elseif ($panel['type'] == "marzneshin") {
        $data = array(
            'expire_date' => $new_limit,
            'expire_strategy' => "fixed_date",

        );
    } elseif ($panel['type'] == "x-ui_single") {
        $new_limit = $new_limit * 1000;
        $data = array(
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "expiryTime" => $new_limit,
                        )
                    ),
                )
            ),
        );
    } elseif ($panel['type'] == "alireza_single") {
        $new_limit = $new_limit * 1000;
        $data = array(
            'id' => intval($inbound_id),
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "expiryTime" => $new_limit,
                        )
                    ),
                )
            ),
        );
    } elseif ($panel['type'] == "hiddify") {
        $new_limit = ($old_limit_time / pow(1024, 3)) + $limit_time_new;
        $datauser = getdatauser($username_account, $panel['name_panel']);
        $data = array(
            "current_usage_GB" => $datauser['current_usage_GB'],
            "usage_limit_GB" => $datauser['usage_limit_GB'],
            "package_days" => $new_limit,
            "start_date" => null
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
            if ($jobsvolume['Field'] == "date") {
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
        }
        $log = setjob($panel['name_panel'], "date", date('Y-m-d H:i:s', $new_limit), $datauser['id']);
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
            "expiry" => $new_limit,
        );
    }
    $extra_time = $this->Modifyuser($username_account, $panel['name_panel'], $data);
    if ($extra_time['status'] == false) {
        return array(
            'status' => false,
            'msg' => $extra_time['msg']
        );
    }
    return $extra_time;
}
}
