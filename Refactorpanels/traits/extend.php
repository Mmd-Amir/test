<?php
// Auto-refactored from legacy panels.php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/traits/extend.php');
}

trait ManagePanelExtendTrait
{
function extend($Method_extend, $new_limit, $time_day, $username, $code_product, $name_panel)
{
    $panel = select("marzban_panel", "*", "code_panel", $name_panel, "select");
    $product = select("product", "*", "code_product", $code_product, "select");
    $invoice = select("invoice", "*", "username", $username, "select");
    if ($code_product == "custom_volume")
        $product = true;
    if ($panel == false || $product == false) {
        return array(
            'status' => false,
            'msg' => 'data not found'
        );
    }
    $data_user = $this->DataUser($panel['name_panel'], $username);
    if ($data_user['status'] == "Unsuccessful") {
        return array(
            'status' => false,
            'msg' => $data_user['msg']
        );
    }
    $notifctions = json_encode(array(
        'volume' => false,
        'time' => false,
    ));
    update("invoice", "notifctions", $notifctions, 'id_invoice', $invoice['id_invoice']);
    $data_limit_old = $data_user['data_limit'];
    $time_old = $data_user['expire'];
    $time_old = time() - $time_old > 0 ? time() : $time_old;
    $data_limit_new = $new_limit == 0 ? 0 : $new_limit * pow(1024, 3);
    $data_limit_new_add = $new_limit == 0 ? 0 : $data_limit_old + ($new_limit * pow(1024, 3));
    $time_new = $time_day == 0 ? 0 : time() + $time_day * 86400;
    $time_old = $time_old == 0 ? time() : $time_old;
    $time_new_add = $time_day == 0 ? 0 : $time_old + ($time_day * 86400);
    //inboud and proxies 
    $inbound_id = isset($panel['inboundid']) ? $panel['inboundid'] : 1;
    $inbounds = is_string($panel['inbounds']) ? json_decode($panel['inbounds']) : "{}";
    $inbounds = $product['inbounds'] != null ? json_decode($product['inbounds']) : $inbounds;
    if ($panel['type'] != "WGDashboard") {
        update("invoice", 'user_info', null, "username", $username);
    }
    update("invoice", 'uuid', null, "username", $username);
    update("invoice", 'Status', "active", "username", $username);
    if ($Method_extend == "ریست حجم و زمان") {
        $reset = $this->ResetUserDataUsage($username, $panel['name_panel']);
        if ($reset['status'] == false) {
            return array(
                'status' => false,
                'msg' => 'error reset : ' . $reset['msg']
            );
        }
    } elseif ($Method_extend == "اضافه شدن زمان و حجم به ماه بعد") {
        $data_limit_new = $data_limit_new_add;
        $time_new = $time_new_add;
    } elseif ($Method_extend == "ریست زمان و اضافه کردن حجم قبلی") {
        $data_limit_new = $data_limit_new_add;
    } elseif ($Method_extend == "ریست شدن حجم و اضافه شدن زمان") {
        $reset = $this->ResetUserDataUsage($username, $panel['name_panel']);
        if ($reset['status'] == false) {
            return array(
                'status' => false,
                'msg' => 'error reset : ' . $reset['msg']
            );
        }
        $time_new = $time_new_add;
    } elseif ($Method_extend == "اضافه شدن زمان و تبدیل حجم کل به حجم باقی مانده") {
        $reset = $this->ResetUserDataUsage($username, $panel['name_panel']);
        if ($reset['status'] == false) {
            return array(
                'status' => false,
                'msg' => 'error reset : ' . $reset['msg']
            );
        }
        $time_new = $time_new_add;
        $data_limit_last = $data_user['data_limit'] - $data_user['used_traffic'];
        $data_limit_last = $data_limit_last < 0 ? 0 : $data_limit_last;
        $data_limit_new = $data_limit_new + $data_limit_last;
    }
    if ($panel['type'] == "marzban") {
        $data = array(
            'data_limit' => $data_limit_new,
            'expire' => $time_new,
            'inbounds' => $inbounds,
        );
        if ($invoice != false && $invoice['uuid'] != null) {
            $data['proxies'] = json_decode($invoice['uuid'], true);
        }
    } elseif ($panel['type'] == "marzneshin") {
        $expire_strotegy = $time_new == 0 ? "never" : "fixed_date";
        $time_new = date('c', $time_new);
        $data = array(
            'username' => $username,
            'expire_date' => $time_new,
            'expire_strategy' => $expire_strotegy,
            'data_limit' => $data_limit_new
        );
    } elseif ($panel['type'] == "x-ui_single") {
        $data = array(
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "totalGB" => $data_limit_new,
                            "expiryTime" => $time_new * 1000,
                            "enable" => true,
                        )
                    ),
                    'decryption' => 'none',
                    'fallbacks' => array(),
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
                            "totalGB" => $data_limit_new,
                            "expiryTime" => $time_new * 1000,
                            "enable" => true,
                        )
                    ),
                    'decryption' => 'none',
                    'fallbacks' => array(),
                )
            ),
        );
    } elseif ($panel['type'] == "WGDashboard") {
        if ($data_user['status'] == "limited" || $data_user['status'] == "expired") {
            $reset = $this->ResetUserDataUsage($username, $panel['name_panel']);
            if ($reset['status'] == false) {
                return array(
                    'status' => false,
                    'msg' => 'error reset : ' . $reset['msg']
                );
            }
        }
        $allowResponse = allowAccessPeers($panel['name_panel'], $username);
        if (isset($allowResponse['status']) && $allowResponse['status'] === false) {
            return array(
                'status' => false,
                'msg' => isset($allowResponse['msg']) ? $allowResponse['msg'] : ''
            );
        }
        $datauser = get_userwg($username, $panel['name_panel']);
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
        $count = 0;
        foreach ($datauser['jobs'] as $jobsvolume) {
            if ($jobsvolume['Field'] == "total_data") {
                break;
            }
            $count += 1;
        }
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
        $time_new = date("Y-m-d H:i:s", $time_new);
        if ($time_day != 0) {
            $setJob = setjob($panel['name_panel'], "date", $time_new, $datauser['id']);
            if (isset($setJob['status']) && $setJob['status'] === false) {
                return array(
                    'status' => false,
                    'msg' => isset($setJob['msg']) ? $setJob['msg'] : ''
                );
            }
        }
        if ($new_limit != 0) {
            $setJob = setjob($panel['name_panel'], "total_data", $data_limit_new / pow(1024, 3), $datauser['id']);
            if (isset($setJob['status']) && $setJob['status'] === false) {
                return array(
                    'status' => false,
                    'msg' => isset($setJob['msg']) ? $setJob['msg'] : ''
                );
            }
        }
        return array(
            'status' => true
        );
    } elseif ($panel['type'] == "hiddify") {
        $day = $time_new - time();
        $data = array(
            "package_days" => $day / 86400,
            "usage_limit_GB" => $data_limit_new / pow(1024, 3),
            "start_date" => null
        );
        if (in_array($Method_extend, ["ریست حجم و زمان", "ریست شدن حجم و اضافه شدن زمان", "اضافه شدن زمان و تبدیل حجم کل به حجم باقی مانده"])) {
            $data['current_usage_GB'] = "0";
        }
    } elseif ($panel['type'] == "s_ui") {
        $data = array(
            "volume" => $data_limit_new,
            "expiry" => $time_new
        );
    }
    $extend = $this->Modifyuser($username, $panel['name_panel'], $data);
    if ($extend['status'] == false) {
        return array(
            'status' => false,
            'msg' => $extend['msg']
        );
    }
    return $extend;
}
}
