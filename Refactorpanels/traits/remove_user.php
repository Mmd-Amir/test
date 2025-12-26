<?php
// Auto-refactored from legacy panels.php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/traits/remove_user.php');
}

trait ManagePanelRemoveUserTrait
{
function RemoveUser($name_panel, $username)
{
    $Output = array();
    $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel, "select");
    if ($Get_Data_Panel['type'] == "marzban") {
        $UsernameData = removeuser($Get_Data_Panel['name_panel'], $username);
        if (!empty($UsernameData['status']) && $UsernameData['status'] != 200) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['status']
            );
        } elseif (!empty($UsernameData['error'])) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['error']
            );
        }
        $UsernameData = json_decode($UsernameData['body'], true);
        if ($UsernameData['detail'] != "User successfully deleted") {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['detail']
            );
        } else {
            $Output = array(
                'status' => 'successful',
                'username' => $username,
            );
        }
    } elseif ($Get_Data_Panel['type'] == "marzneshin") {
        $UsernameData = removeuserm($Get_Data_Panel['name_panel'], $username);
        if (isset($UsernameData['detail']) && $UsernameData['detail']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['detail']
            );
        } else {
            $Output = array(
                'status' => 'successful',
                'username' => $username,
            );
        }
    } elseif ($Get_Data_Panel['type'] == "x-ui_single") {
        $UsernameData = removeClient($Get_Data_Panel['name_panel'], $username);
        if (!empty($UsernameData['status']) && $UsernameData['status'] != 200) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['status']
            );
        } elseif (!empty($UsernameData['error'])) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['error']
            );
        }
        $UsernameData = json_decode($UsernameData['body'], true);
        if (!$UsernameData['success']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['msg']
            );
        } else {
            $Output = array(
                'status' => 'successful',
                'username' => $username,
            );
        }
    } elseif ($Get_Data_Panel['type'] == "alireza_single") {
        $UsernameData = removeClientalireza_single($Get_Data_Panel['name_panel'], $username);
        if (!$UsernameData['success']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['msg']
            );
        } else {
            $Output = array(
                'status' => 'successful',
                'username' => $username,
            );
        }
    } elseif ($Get_Data_Panel['type'] == "hiddify") {
        $data_user = getdatauser($username, $name_panel);
        removeuserhi($name_panel, $data_user['uuid']);
        $Output = array(
            'status' => 'successful',
            'msg' => ""
        );
    } elseif ($Get_Data_Panel['type'] == "Manualsale") {
        update("manualsell", "status", "delete", "username", $username);
        $Output = array(
            'status' => 'successful',
            'username' => $username,
        );
    } elseif ($Get_Data_Panel['type'] == "WGDashboard") {
        $UsernameData = remove_userwg($Get_Data_Panel['name_panel'], $username);
        if (!$UsernameData['status']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['msg']
            );
        } else {
            $Output = array(
                'status' => 'successful',
                'username' => $username,
            );
        }
    } elseif ($Get_Data_Panel['type'] == "s_ui") {
        $UsernameData = removeClientS_ui($Get_Data_Panel['name_panel'], $username);
        if (!$UsernameData['success']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['msg']
            );
        } else {
            $Output = array(
                'status' => 'successful',
                'username' => $username,
            );
        }
    } elseif ($Get_Data_Panel['type'] == "ibsng") {
        $UsernameData = deleteUserIBSng($Get_Data_Panel['name_panel'], $username);
        if (!$UsernameData['status']) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['msg']
            );
        } else {
            $Output = array(
                'status' => 'successful',
                'username' => $username,
            );
        }
    } elseif ($Get_Data_Panel['type'] == "mikrotik") {
        $UsernameData = GetUsermikrotik($Get_Data_Panel['name_panel'], $username)[0];
        if (isset($UsernameData['error'])) {
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => $UsernameData['msg']
            );
        } else {
            deleteUser_mikrotik($Get_Data_Panel['name_panel'], $UsernameData['.id']);
            $Output = array(
                'status' => 'successful',
                'username' => $username,
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
