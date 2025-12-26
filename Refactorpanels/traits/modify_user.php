<?php
// Auto-refactored from legacy panels.php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/traits/modify_user.php');
}

trait ManagePanelModifyUserTrait
{
function Modifyuser($username, $name_panel, $config = array())
{
    global $new_marzban;
    $Output = array();
    $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel, "select");
    if ($Get_Data_Panel['type'] == "marzban") {
        if ($new_marzban) {
            $result = getuser($username, $name_panel);
            $result = json_decode($result['body'], true);
            $config['proxy_settings'] = $result['proxy_settings'];
        }
        $modify = Modifyuser($name_panel, $username, $config);
        if (!empty($modify['error'])) {
            return array(
                'status' => false,
                'msg' => $modify['error']
            );
        } elseif (!empty($modify['status']) && $modify['status'] == 500) {
            return array(
                'status' => false,
                'msg' => 'error code : ' . $modify['status']
            );
        }
        $modifycheck = json_decode($modify['body'], true);
        if (!empty($modifycheck['detail'])) {
            return array(
                'status' => false,
                'msg' => $modifycheck['detail']
            );
        }
        return array(
            'status' => true,
            'data' => $modify
        );
    } elseif ($Get_Data_Panel['type'] == "marzneshin") {
        $config['username'] = $username;
        $modify = Modifyuserm($name_panel, $username, $config);
        if (!empty($modify['error'])) {
            return array(
                'status' => false,
                'msg' => $modify['error']
            );
        } elseif (!empty($modify['status']) && $modify['status'] == 500) {
            return array(
                'status' => false,
                'msg' => 'error code : ' . $modify['status']
            );
        }
        $modifycheck = json_decode($modify['body'], true);
        if (!empty($modifycheck['detail'])) {
            return array(
                'status' => false,
                'msg' => $modifycheck['detail']
            );
        }
        return array(
            'status' => true,
            'data' => $modify
        );
    } elseif ($Get_Data_Panel['type'] == "x-ui_single") {
        $clients = get_clinets($username, $name_panel);
        if (!empty($clients['error'])) {
            return array(
                'status' => false,
                'msg' => $clients['error']
            );
        } elseif (!empty($clients['status']) && $clients['status'] != 200) {
            return array(
                'status' => false,
                'msg' => json_encode($clients)
            );
        }
        $clients = json_decode($clients['body'], true);
        if (!is_array($clients)) {
            return array(
                'status' => false,
                'msg' => 'object invalid'
            );
        }
        if (empty($clients['obj'])) {
            return array(
                'status' => false,
                'msg' => "User not found"
            );
        }
        $clients = $clients['obj'];
        $configs = array(
            'id' => intval($clients['inboundId']),
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "id" => $clients['uuid'],
                            "flow" => "",
                            "email" => $clients['email'],
                            "totalGB" => $clients['total'],
                            "expiryTime" => $clients['expiryTime'],
                            "enable" => true,
                            "subId" => $clients['subId'],
                        )
                    ),
                    'decryption' => 'none',
                    'fallbacks' => array(),
                )
            ),
        );
        $configs['settings'] = json_encode(array_replace_recursive(json_decode($configs['settings'], true), json_decode($config['settings'], true)));
        $modify = updateClient($Get_Data_Panel['name_panel'], $clients['uuid'], $configs);
        if (!empty($modify['error'])) {
            return array(
                'status' => false,
                'msg' => $modify['error']
            );
        } elseif (!empty($modify['status']) && $modify['status'] != 200) {
            return array(
                'status' => false,
                'msg' => 'error code : ' . $modify['status']
            );
        }
        $modify = json_decode($modify['body'], true);
        if (!$modify['success']) {
            return array(
                'status' => false,
                'msg' => 'error :' . $modify['msg']
            );
        }
        return array(
            'status' => true,
            'data' => $modify
        );
    } elseif ($Get_Data_Panel['type'] == "alireza_single") {
        $clients = get_clinetsalireza($username, $name_panel)[0];
        $configs = array(
            'id' => intval($Get_Data_Panel['inboundid']),
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "id" => $clients['id'],
                            "flow" => $clients['flow'],
                            "email" => $clients['email'],
                            "totalGB" => $clients['totalGB'],
                            "expiryTime" => $clients['expiryTime'],
                            "enable" => true,
                            "subId" => $clients['subId'],
                        )
                    ),
                    'decryption' => 'none',
                    'fallbacks' => array(),
                )
            ),
        );
        $configs['settings'] = json_encode(array_replace_recursive(json_decode($configs['settings'], true), json_decode($config['settings'], true)));
        $modify = updateClientalireza($Get_Data_Panel['name_panel'], $username, $configs);
        if (!empty($modify['error'])) {
            return array(
                'status' => false,
                'msg' => $modify['error']
            );
        } elseif (!empty($modify['status']) && $modify['status'] != 200) {
            return array(
                'status' => false,
                'msg' => 'error code : ' . $modify['status']
            );
        }
        $modify = json_decode($modify['body'], true);
        if (!$modify['success']) {
            return array(
                'status' => false,
                'msg' => 'error :' . $modify['msg']
            );
        }
        return array(
            'status' => true,
            'data' => $modify
        );
    } elseif ($Get_Data_Panel['type'] == "hiddify") {
        $modify = updateuserhi($username, $name_panel, $config);
        if (!empty($modify['error'])) {
            return array(
                'status' => false,
                'msg' => $modify['error']
            );
        } elseif (!empty($modify['status']) && $modify['status'] != 200) {
            return array(
                'status' => false,
                'msg' => 'error code : ' . $modify['status']
            );
        }
        $modify = json_decode($modify['body'], true);
        return array(
            'status' => true,
            'data' => $modify
        );
    } elseif ($Get_Data_Panel['type'] == "WGDashboard") {
        $data_user = get_userwg($username, $name_panel);
        if (isset($data_user['status']) && $data_user['status'] === false && !isset($data_user['id'])) {
            return array(
                'status' => false,
                'msg' => isset($data_user['msg']) ? $data_user['msg'] : ''
            );
        }
        $configs = array(
            "DNS" => $data_user['DNS'],
            "allowed_ip" => $data_user['allowed_ip'],
            "endpoint_allowed_ip" => "0.0.0.0/0",
            "jobs" => $data_user['jobs'],
            "id" => $data_user['id'],
            "keepalive" => $data_user['keepalive'],
            "mtu" => $data_user['mtu'],
            "name" => $data_user['name'],
            "preshared_key" => $data_user['preshared_key'],
            "private_key" => $data_user['private_key']
        );
        $configs = array_merge($configs, $config);
        $modify = updatepear($Get_Data_Panel['name_panel'], $configs);
        if (isset($modify['status']) && $modify['status'] === false) {
            return array(
                'status' => false,
                'msg' => isset($modify['msg']) ? $modify['msg'] : ''
            );
        }
        if (!empty($modify['error'])) {
            return array(
                'status' => false,
                'msg' => $modify['error']
            );
        } elseif (!empty($modify['status']) && $modify['status'] != 200) {
            return array(
                'status' => false,
                'msg' => 'error code : ' . $modify['status']
            );
        }
        $modify = json_decode($modify['body'], true);
        return array(
            'status' => true,
            'data' => $modify
        );
    } elseif ($Get_Data_Panel['type'] == "s_ui") {
        $clients = GetClientsS_UI($username, $name_panel);
        if (!$clients)
            return [];
        $usernameac = $username;
        $configs = array(
            "object" => 'clients',
            'action' => "edit",
            "data" => array(
                "id" => $clients['id'],
                "enable" => $clients['enable'],
                "name" => $usernameac,
                "config" => $clients['config'],
                "inbounds" => $clients['inbounds'],
                "links" => $clients['links'],
                "volume" => $clients['volume'],
                "expiry" => $clients['expiry'],
                "desc" => $clients['desc']
            ),
        );
        $configs['data'] = array_merge($configs['data'], $config);
        $configs['data'] = json_encode($configs['data'], true);
        $modify = updateClientS_ui($Get_Data_Panel['name_panel'], $configs);
        return array(
            'status' => true,
            'data' => $modify
        );
    }
}
}
