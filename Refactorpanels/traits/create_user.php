<?php
// Auto-refactored from legacy panels.php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/traits/create_user.php');
}

trait ManagePanelCreateUserTrait
{
function createUser($name_panel, $code_product, $usernameC, array $Data_Config)
{
    $Output = [];
    global $pdo, $domainhosts, $new_marzban;
    if (strlen($usernameC) < 3) {
        return array(
            "status" => "Unsuccessful",
            "msg" => "Username must be at least 3 characters long."
        );
    }
    // input time expire timestep use $Data_Config
    // input data_limit byte use $Data_Config
    // input username use $Data_Config
    // input from_id use $Data_Config
    // input type config use $Data_Config
    $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel, "select");
    if ($Get_Data_Panel == false) {
        $Output['status'] = 'Unsuccessful';
        $Output['msg'] = 'Panel Not Found';
        return $Output;
    }
    if ($Get_Data_Panel['subvip'] == "onsubvip") {
        $inoice = select("invoice", "*", "username", $usernameC, "select");
    } else {
        $inoice = false;
    }
    if (!in_array($code_product, ["usertest", "🛍 حجم دلخواه", "customvolume"])) {

        $stmt = $pdo->prepare("SELECT * FROM product WHERE (Location = :name_panel OR Location = '/all')  AND code_product = :code_product");
        $stmt->bindParam(':name_panel', $name_panel);
        $stmt->bindParam(':code_product', $code_product);
        $stmt->execute();
        $Get_Data_Product = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        if ($code_product == "usertest") {
            $Get_Data_Product['name_product'] = "usertest";
        } else {
            $Get_Data_Product['name_product'] = false;
        }
        $Get_Data_Product['data_limit_reset'] = "no_reset";
    }
    $expire = $Data_Config['expire'];
    $data_limit = $Data_Config['data_limit'];
    $note = "{$Data_Config['from_id']} | {$Data_Config['username']} | {$Data_Config['type']}";
    if ($Get_Data_Panel['type'] == "marzban") {
        //create user
        $ConnectToPanel = adduser($Get_Data_Panel['name_panel'], $data_limit, $usernameC, $expire, $note, $Get_Data_Product['data_limit_reset'], $Get_Data_Product['name_product']);
        if (!empty($ConnectToPanel['status']) && $ConnectToPanel['status'] == 500) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $ConnectToPanel['status']
            );
        }
        if (!empty($ConnectToPanel['error'])) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $ConnectToPanel['error']
            );
        }
        $data_Output = json_decode($ConnectToPanel['body'], true);
        if (!empty($data_Output['detail']) && $data_Output['detail']) {
            $Output['status'] = 'Unsuccessful';
            if ($data_Output['detail']) {
                $Output['msg'] = $data_Output['detail'];
            } else {
                $Output['msg'] = '';
            }
        } else {
            if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $data_Output['subscription_url'])) {
                $data_Output['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($data_Output['subscription_url'], "/");
            }
            if ($new_marzban) {
                $out_put_link = outputlunk($data_Output['subscription_url']);
                if (isBase64($out_put_link)) {
                    $data_Output['links'] = base64_decode(outputlunk($data_Output['subscription_url']));
                }
                $data_Output['links'] = explode("\n", $data_Output['links']);
            }
            if ($inoice != false) {
                $data_Output['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
            }
            $Output['status'] = 'successful';
            $Output['username'] = $data_Output['username'];
            $Output['subscription_url'] = $data_Output['subscription_url'];
            $Output['configs'] = $data_Output['links'];
        }
    } elseif ($Get_Data_Panel['type'] == "marzneshin") {
        //create user
        $ConnectToPanel = adduserm($Get_Data_Panel['name_panel'], $data_limit, $usernameC, $expire, $Get_Data_Product['name_product'], $note, $Get_Data_Product['data_limit_reset']);
        if (!empty($ConnectToPanel['status']) && $ConnectToPanel['status'] == 500) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $ConnectToPanel['status']
            );
        }
        if (!empty($ConnectToPanel['error'])) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $ConnectToPanel['error']
            );
        }
        $data_Output = json_decode($ConnectToPanel['body'], true);
        if (isset($data_Output['detail']) && $data_Output['detail']) {
            $Output['status'] = 'Unsuccessful';
            if ($data_Output['detail']) {
                $Output['msg'] = $data_Output['detail'];
            } else {
                $Output['msg'] = '';
            }
        } else {
            if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $data_Output['subscription_url'])) {
                $data_Output['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($data_Output['subscription_url'], "/");
            }
            $data_Output['links'] = outputlunk($data_Output['subscription_url']);
            if (isBase64($data_Output['links'])) {
                $data_Output['links'] = base64_decode($data_Output['links']);
            }
            $links_user = explode("\n", trim($data_Output['links']));
            $date = new DateTime($data_Output['expire']);
            if ($inoice != false) {
                $data_Output['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
            }
            $data_Output['expire'] = $date->getTimestamp();
            $Output['status'] = 'successful';
            $Output['username'] = $data_Output['username'];
            $Output['subscription_url'] = $data_Output['subscription_url'];
            $Output['configs'] = $links_user;
        }
    } elseif ($Get_Data_Panel['type'] == "x-ui_single") {
        $subId = bin2hex(random_bytes(8));
        if (isset($Get_Data_Product['inbounds']) and $Get_Data_Product['inbounds'] != null) {
            $inbounds = $Get_Data_Product['inbounds'];
        } else {
            $inbounds = $Get_Data_Panel['inboundid'];
        }
        $data_Output = addClient($Get_Data_Panel['name_panel'], $usernameC, $expire, $data_limit, generateUUID(), "", $subId, $inbounds, $Get_Data_Product['name_product'], $note);
        if (!empty($data_Output['error'])) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $data_Output['error']
            );
        } elseif (!empty($data_Output['status']) && $data_Output['status'] != 200) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $data_Output['status']
            );
        } else {
            $data_Output = json_decode($data_Output['body'], true);
            if (!$data_Output['success']) {
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['msg'];
            } else {
                $subscriptionUrl = rtrim($Get_Data_Panel['linksubx'], '/') . "/{$subId}";
                $singleLink = get_single_link_after_create(
                    $Get_Data_Panel['url_panel'],
                    $inbounds,
                    $subscriptionUrl,
                    $usernameC,
                    $Get_Data_Panel['name_panel'],
                    $Get_Data_Panel['code_panel'] ?? null
                );
                $links_user = [];
                if ($singleLink) {
                    $links_user[] = $singleLink;
                }
                $subscriptionLinks = get_subscription_links_with_retry($subscriptionUrl);
                if (is_array($subscriptionLinks)) {
                    foreach ($subscriptionLinks as $linkItem) {
                        if (!in_array($linkItem, $links_user, true)) {
                            $links_user[] = $linkItem;
                        }
                    }
                }
                if (empty($links_user)) {
                    $links_user[] = 'در دسترس نیست';
                }
                $Output['status'] = 'successful';
                $Output['username'] = $usernameC;
                $Output['subscription_url'] = $subscriptionUrl;
                $Output['configs'] = $links_user;
                if ($inoice != false) {
                    $Output['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
                }
            }
        }
    } elseif ($Get_Data_Panel['type'] == "alireza_single") {
        $subId = bin2hex(random_bytes(8));
        $Expireac = $expire * 1000;
        if (isset($Get_Data_Product['inbounds']) and $Get_Data_Product['inbounds'] != null) {
            $inbounds = $Get_Data_Product['inbounds'];
        } else {
            $inbounds = $Get_Data_Panel['inboundid'];
        }
        $data_Output = addClientalireza_singel($Get_Data_Panel['name_panel'], $usernameC, $Expireac, $data_limit, generateUUID(), "", $subId, $inbounds);
        if (!empty($data_Output['error'])) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $data_Output['error']
            );
        } elseif (!empty($data_Output['status']) && $data_Output['status'] != 200) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $data_Output['status']
            );
        } else {
            $data_Output = json_decode($data_Output['body'], true);
            if (!$data_Output['success']) {
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['msg'];
            } else {
                $Output['status'] = 'successful';
                $Output['username'] = $usernameC;
                $Output['subscription_url'] = $Get_Data_Panel['linksubx'] . "/{$subId}";
                $Output['configs'] = [outputlunk($Output['subscription_url'])];
                if ($inoice != false) {
                    $Output['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
                }
            }
        }
    } elseif ($Get_Data_Panel['type'] == "hiddify") {
        if ($expire != 0) {
            $current_timestamp = time();
            $diff_seconds = $expire - $current_timestamp;
            $diff_days = ceil($diff_seconds / (60 * 60 * 24));
        } else {
            $diff_days = 111111;
        }
        $uuid = generateUUID();
        $data = array(
            "uuid" => $uuid,
            "name" => $usernameC,
            "added_by_uuid" => $Get_Data_Panel['secret_code'],
            "current_usage_GB" => "0",
            "usage_limit_GB" => $data_limit / pow(1024, 3),
            "package_days" => $diff_days,
            "comment" => $note,
        );
        $data_Output = adduserhi($Get_Data_Panel['name_panel'], $data);
        if (!empty($data_Output['error'])) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $data_Output['error']
            );
        } elseif (!empty($data_Output['status']) && $data_Output['status'] != 200) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $data_Output['status']
            );
        }
        $data_Output = json_decode($data_Output['body'], true);
        if (isset($data_Output['message']) && $data_Output['message']) {
            $Output['status'] = 'Unsuccessful';
            $Output['msg'] = $data_Output['message'];
        } else {
            $Output['status'] = 'successful';
            $Output['username'] = $usernameC;
            $Output['subscription_url'] = "{$Get_Data_Panel['linksubx']}/{$data_Output['uuid']}/";
            $Output['configs'] = [];
            if ($inoice != false) {
                $Output['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
            }
        }
    } elseif ($Get_Data_Panel['type'] == "Manualsale") {
        $statement = $pdo->prepare("SELECT * FROM manualsell WHERE codepanel = :code_panel AND status = 'active' AND codeproduct = '$code_product' ORDER BY RAND() LIMIT 1");
        $statement->execute(array(':code_panel' => $Get_Data_Panel['code_panel']));
        $configman = $statement->fetch(PDO::FETCH_ASSOC);
        $Output['status'] = 'successful';
        $Output['username'] = $usernameC;
        $Output['subscription_url'] = $configman['contentrecord'];
        $Output['configs'] = "";
        update("manualsell", "status", "selled", "id", $configman['id']);
        update("manualsell", "username", $usernameC, "id", $configman['id']);
    } elseif ($Get_Data_Panel['type'] == "WGDashboard") {
        $data_limit = round($data_limit / (1024 * 1024 * 1024), 2);
        $data_Output = addpear($Get_Data_Panel['name_panel'], $usernameC);
        if (isset($data_Output['status']) && $data_Output['status'] === false) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => isset($data_Output['msg']) ? $data_Output['msg'] : ''
            );
        }
        if (!empty($data_Output['status']) && $data_Output['status'] != 200) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $data_Output['status']
            );
        }
        if (!empty($data_Output['error'])) {
            return array(
                'status' => 'Unsuccessful',
                'msg' => $data_Output['error']
            );
        }
        $data_Output = $data_Output['body'];
        $response = json_decode($data_Output['response'], true);
        if ($data_limit != 0) {
            $jobResponse = setjob($Get_Data_Panel['name_panel'], "total_data", $data_limit, $data_Output['public_key']);
            if (isset($jobResponse['status']) && $jobResponse['status'] === false) {
                return array(
                    'status' => 'Unsuccessful',
                    'msg' => isset($jobResponse['msg']) ? $jobResponse['msg'] : ''
                );
            }
        }
        if ($expire != 0) {
            $jobResponse = setjob($Get_Data_Panel['name_panel'], "date", date('Y-m-d H:i:s', $expire), $data_Output['public_key']);
            if (isset($jobResponse['status']) && $jobResponse['status'] === false) {
                return array(
                    'status' => 'Unsuccessful',
                    'msg' => isset($jobResponse['msg']) ? $jobResponse['msg'] : ''
                );
            }
        }
        update("invoice", "user_info", json_encode($data_Output), "username", $usernameC);
        if (!$response['status']) {
            $Output['status'] = 'Unsuccessful';
            $Output['msg'] = $data_Output['msg'];
        } else {
            $download_config = downloadconfig($Get_Data_Panel['name_panel'], $data_Output['public_key']);
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
            $Output['status'] = 'successful';
            $Output['username'] = $usernameC;
            $Output['subscription_url'] = strval($download_config['file']);
            $Output['configs'] = [];
        }
    } elseif ($Get_Data_Panel['type'] == "s_ui") {
        if ($Get_Data_Product['inbounds'] != null) {
            $Get_Data_Panel['inbounds'] = $Get_Data_Product['inbounds'];
        }
        $data_Output = addClientS_ui($Get_Data_Panel['name_panel'], $usernameC, $expire, $data_limit, json_decode($Get_Data_Panel['inbounds']), $note);
        if (!$data_Output['success']) {
            $Output['status'] = 'Unsuccessful';
            $Output['msg'] = $data_Output['msg'];
        } else {
            $setting_app = get_settig($Get_Data_Panel['name_panel']);
            $url = explode(":", $Get_Data_Panel['url_panel']);
            $url_sub = $url[0] . ":" . $url[1] . ":" . $setting_app['subPort'] . $setting_app['subPath'] . $usernameC;
            $Output['status'] = 'successful';
            $Output['username'] = $usernameC;
            $Output['subscription_url'] = $url_sub;
            $Output['configs'] = [outputlunk($url_sub)];
        }
    } elseif ($Get_Data_Panel['type'] == "ibsng") {
        $password = bin2hex(random_bytes(6));
        $name_group = $Get_Data_Panel['proxies'];
        if ($Get_Data_Product['inbounds'] != null) {
            $name_group = $Get_Data_Panel['inbounds'];
        } elseif ($code_product == "usertest") {
            $name_group = "usertest";
        }
        $data_Output = addUserIBsng($Get_Data_Panel['name_panel'], $usernameC, $password, $name_group);
        if (!$data_Output) {
            $Output['status'] = 'Unsuccessful';
            $Output['msg'] = $data_Output['msg'];
        } else {
            $Output['status'] = 'successful';
            $Output['username'] = $usernameC;
            $Output['subscription_url'] = $password;
            $Output['configs'] = [];
        }
    } elseif ($Get_Data_Panel['type'] == "mikrotik") {
        $password = bin2hex(random_bytes(6));
        $name_group = $Get_Data_Panel['proxies'];
        if ($Get_Data_Product['inbounds'] != null) {
            $name_group = $Get_Data_Product['inbounds'];
        } elseif ($code_product == "usertest") {
            $name_group = "usertest";
        }
        $data_Output = addUser_mikrotik($Get_Data_Panel['name_panel'], $usernameC, $password, $name_group);
        if (isset($data_Output['error'])) {
            $Output['status'] = 'Unsuccessful';
            $Output['msg'] = $data_Output['msg'];
        } else {
            $Output['status'] = 'successful';
            $Output['username'] = $usernameC;
            $Output['subscription_url'] = $password;
            $Output['configs'] = [];
        }
    } else {
        $Output['status'] = 'Unsuccessful';
        $Output['msg'] = 'Panel Not Found';
    }
    if (function_exists('normalizeServiceConfigs')) {
        if (isset($Output['status']) && $Output['status'] === 'successful') {
            $Output['configs'] = normalizeServiceConfigs($Output['configs'] ?? null, $Output['subscription_url'] ?? null);
        } else {
            $Output['configs'] = normalizeServiceConfigs($Output['configs'] ?? null);
        }
    } else {
        if (!isset($Output['configs'])) {
            $Output['configs'] = [];
        } elseif (!is_array($Output['configs'])) {
            $value = trim((string) $Output['configs']);
            $Output['configs'] = $value === '' ? [] : [$value];
        }
    }
    return $Output;
}
}
