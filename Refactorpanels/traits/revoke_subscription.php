<?php
// Auto-refactored from legacy panels.php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/traits/revoke_subscription.php');
}

trait ManagePanelRevokeSubscriptionTrait
{
function Revoke_sub($name_panel, $username)
{
    $Output = array();
    $ManagePanel = new ManagePanel();
    $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel, "select");
    if ($Get_Data_Panel['type'] == "marzban") {
        $revoke_sub = revoke_sub($username, $name_panel);
        if (isset($revoke_sub['detail']) && $revoke_sub['detail']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => $revoke_sub['detail']
            );
        } else {
            $config = new ManagePanel();
            $Data_User = $config->DataUser($name_panel, $username);
            if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $Data_User['subscription_url'])) {
                $Data_User['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($Data_User['subscription_url'], "/");
            }
            $Output = array(
                'status' => 'successful',
                'configs' => $Data_User['links'],
                'subscription_url' => $Data_User['subscription_url']
            );
        }
    } else if ($Get_Data_Panel['type'] == "marzneshin") {
        $revoke_sub = revoke_subm($username, $name_panel);
        if (isset($revoke_sub['detail']) && $revoke_sub['detail']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => $revoke_sub['detail']
            );
        } else {
            $config = new ManagePanel();
            $Data_User = $config->DataUser($name_panel, $username);
            $Data_User['links'] = [base64_decode(outputlunk($Data_User['subscription_url']))];
            $Output = array(
                'status' => 'successful',
                'configs' => $Data_User['links'],
                'subscription_url' => $Data_User['subscription_url']
            );
        }
    } elseif ($Get_Data_Panel['type'] == "x-ui_single") {
        $subId = bin2hex(random_bytes(8));
        $config = array(
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "id" => generateUUID(),
                            "enable" => true,
                            "subId" => $subId,
                        )
                    ),
                )
            )
        );
        $updateinbound = $ManagePanel->Modifyuser($username, $Get_Data_Panel['name_panel'], $config);
        if (!$updateinbound['status']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => 'Unsuccessful'
            );
        } else {
            $Output = array(
                'status' => 'successful',
                'configs' => [outputlunk($Get_Data_Panel['linksubx'] . "/{$subId}")],
                'subscription_url' => $Get_Data_Panel['linksubx'] . "/{$subId}",
            );
        }
    } elseif ($Get_Data_Panel['type'] == "alireza_single") {
        $subId = bin2hex(random_bytes(8));
        $config = array(
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "id" => generateUUID(),
                            "enable" => true,
                            "subId" => $subId,
                        )
                    ),
                )
            )
        );
        $updateinbound = $ManagePanel->Modifyuser($username, $Get_Data_Panel['name_panel'], $config);
        if (!$updateinbound['status']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => 'Unsuccessful'
            );
        } else {
            $Output = array(
                'status' => 'successful',
                'configs' => [outputlunk($Get_Data_Panel['linksubx'] . "/{$subId}")],
                'subscription_url' => $Get_Data_Panel['linksubx'] . "/{$subId}",
            );
        }
    } elseif ($Get_Data_Panel['type'] == "hiddify") {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => 'panel not supported'
        );
    } elseif ($Get_Data_Panel['type'] == "s_ui") {
        $clients = GetClientsS_UI($username, $name_panel);
        $password = bin2hex(random_bytes(16));
        $usernameac = $username;
        $configpanel = array(
            "object" => 'clients',
            'action' => "edit",
            "data" => json_encode(array(
                "id" => $clients['id'],
                "enable" => $clients['enable'],
                "name" => $usernameac,
                "config" => array(
                    "mixed" => array(
                        "username" => $usernameac,
                        "password" => generateAuthStr()
                    ),
                    "socks" => array(
                        "username" => $usernameac,
                        "password" => generateAuthStr()
                    ),
                    "http" => array(
                        "username" => $usernameac,
                        "password" => generateAuthStr()
                    ),
                    "shadowsocks" => array(
                        "name" => $usernameac,
                        "password" => $password
                    ),
                    "shadowsocks16" => array(
                        "name" => $usernameac,
                        "password" => $password
                    ),
                    "shadowtls" => array(
                        "name" => $usernameac,
                        "password" => $password
                    ),
                    "vmess" => array(
                        "name" => $usernameac,
                        "uuid" => generateUUID(),
                        "alterId" => 0
                    ),
                    "vless" => array(
                        "name" => $usernameac,
                        "uuid" => generateUUID(),
                        "flow" => ""
                    ),
                    "trojan" => array(
                        "name" => $usernameac,
                        "password" => generateAuthStr()
                    ),
                    "naive" => array(
                        "username" => $usernameac,
                        "password" => generateAuthStr()
                    ),
                    "hysteria" => array(
                        "name" => $usernameac,
                        "auth_str" => generateAuthStr()
                    ),
                    "tuic" => array(
                        "name" => $usernameac,
                        "uuid" => generateUUID(),
                        "password" => generateAuthStr()
                    ),
                    "hysteria2" => array(
                        "name" => $usernameac,
                        "password" => generateAuthStr()
                    )
                ),
                "inbounds" => $clients['inbounds'],
                "links" => [],
                "volume" => $clients['volume'],
                "expiry" => $clients['expiry'],
                "desc" => $clients['desc']
            )),
        );
        $result = updateClientS_ui($Get_Data_Panel['name_panel'], $configpanel);
        if (!$result['success']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => 'Unsuccessful'
            );
        } else {
            $setting_app = get_settig($Get_Data_Panel['name_panel']);
            $url = explode(":", $Get_Data_Panel['url_panel']);
            $url_sub = $url[0] . ":" . $url[1] . ":" . $setting_app['subPort'] . $setting_app['subPath'] . $username;
            $Output = array(
                'status' => 'successful',
                'configs' => [outputlunk($url_sub)],
                'subscription_url' => $url_sub,
            );
        }
    } else {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => 'Panel Not Found'
        );
    }
    return $Output;
}
}
