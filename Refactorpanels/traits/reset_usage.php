<?php
// Auto-refactored from legacy panels.php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/traits/reset_usage.php');
}

trait ManagePanelResetUsageTrait
{
function ResetUserDataUsage($username, $name_panel)
{
    $panel = select("marzban_panel", "*", "name_panel", $name_panel, "select");
    if ($panel == false) {
        return array(
            'status' => false,
            'msg' => 'data not found'
        );
    }
    if ($panel['type'] == "marzban") {
        $reset = ResetUserDataUsage($username, $panel['name_panel']);
        if (!empty($reset['status']) && $reset['status'] != 200) {
            return array(
                'status' => false,
                'msg' => 'error code : ' . $reset['status']
            );
        } elseif (!empty($reset['error'])) {
            return array(
                'status' => false,
                'msg' => 'error  : ' . $reset['error']
            );
        }
        $reset = json_decode($reset['body'], true);
        if (!empty($reset['detail'])) {
            return array(
                'status' => false,
                'msg' => $reset['detail']
            );
        }
        return array(
            'status' => true,
            'msg' => 'successful'
        );
    } elseif ($panel['type'] == "marzneshin") {
        $reset = ResetUserDataUsagem($username, $panel['name_panel']);
        if (!empty($reset['status']) && $reset['status'] != 200) {
            return array(
                'status' => false,
                'msg' => 'error code : ' . $reset['status']
            );
        } elseif (!empty($reset['error'])) {
            return array(
                'status' => false,
                'msg' => 'error  : ' . $reset['error']
            );
        }
        $reset = json_decode($reset['body'], true);
        if (!empty($reset['detail'])) {
            return array(
                'status' => false,
                'msg' => $reset['detail']
            );
        }
        return array(
            'status' => true,
            'msg' => 'successful'
        );
    } elseif ($panel['type'] == 'x-ui_single') {
        $reset = ResetUserDataUsagex_uisin($username, $panel['name_panel']);
        if (!empty($reset['status']) && $reset['status'] != 200) {
            return array(
                'status' => false,
                'msg' => 'error code : ' . $reset['status']
            );
        } elseif (!empty($reset['error'])) {
            return array(
                'status' => false,
                'msg' => 'error  : ' . $reset['error']
            );
        }
        $reset = json_decode($reset['body'], true);
        if (!$reset['success']) {
            return array(
                'status' => false,
                'msg' => 'error :' . $reset['msg']
            );
        }
        return array(
            'status' => true,
            'data' => $reset
        );
    } elseif ($panel['type'] == 'alireza_single') {
        $reset = ResetUserDataUsagealirezasin($username, $panel['name_panel']);
        if (!empty($reset['status']) && $reset['status'] != 200) {
            return array(
                'status' => false,
                'msg' => 'error code : ' . $reset['status']
            );
        } elseif (!empty($reset['error'])) {
            return array(
                'status' => false,
                'msg' => 'error  : ' . $reset['error']
            );
        }
        $reset = json_decode($reset['body'], true);
        if (!$reset['success']) {
            return array(
                'status' => false,
                'msg' => 'error :' . $reset['msg']
            );
        }
        return array(
            'status' => true,
            'data' => $reset
        );
    } elseif ($panel['type'] == "WGDashboard") {
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
        $reset = ResetUserDataUsagewg($datauser['id'], $panel['name_panel']);
        if (isset($reset['status']) && $reset['status'] === false) {
            return array(
                'status' => false,
                'msg' => isset($reset['msg']) ? $reset['msg'] : ''
            );
        }
        if (!empty($reset['status']) && $reset['status'] != 200) {
            return array(
                'status' => false,
                'msg' => 'error code : ' . $reset['status']
            );
        } elseif (!empty($reset['error'])) {
            return array(
                'status' => false,
                'msg' => 'error  : ' . $reset['error']
            );
        }
        $reset = json_decode($reset['body'], true);
        return array(
            'status' => true,
            'data' => $reset
        );
    } elseif ($panel['type'] == "hiddify") {
        return array(
            'status' => true
        );
    } elseif ($panel['type'] == "s_ui") {
        ResetUserDataUsages_ui($username, $name_panel);
        return array(
            'status' => true
        );
    }
}
}
