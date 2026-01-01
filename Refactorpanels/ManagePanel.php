<?php
// Refactored ManagePanel class (Consolidated version to fix 500 error)

class ManagePanel
{
    public $pdo, $domainhosts, $name_panel, $new_marzban;

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
            $ConnectToPanel = adduser($Get_Data_Panel['name_panel'], $data_limit, $usernameC, $expire, $note, $Get_Data_Product['data_limit_reset'], $Get_Data_Product['name_product']);
            if (!empty($ConnectToPanel['status']) && $ConnectToPanel['status'] == 500) {
                return array('status' => 'Unsuccessful', 'msg' => $ConnectToPanel['status']);
            }
            if (!empty($ConnectToPanel['error'])) {
                return array('status' => 'Unsuccessful', 'msg' => $ConnectToPanel['error']);
            }
            $data_Output = json_decode($ConnectToPanel['body'], true);
            if (!empty($data_Output['detail']) && $data_Output['detail']) {
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['detail'];
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
            $ConnectToPanel = adduserm($Get_Data_Panel['name_panel'], $data_limit, $usernameC, $expire, $Get_Data_Product['name_product'], $note, $Get_Data_Product['data_limit_reset']);
            if (!empty($ConnectToPanel['status']) && $ConnectToPanel['status'] == 500) {
                return array('status' => 'Unsuccessful', 'msg' => $ConnectToPanel['status']);
            }
            if (!empty($ConnectToPanel['error'])) {
                return array('status' => 'Unsuccessful', 'msg' => $ConnectToPanel['error']);
            }
            $data_Output = json_decode($ConnectToPanel['body'], true);
            if (isset($data_Output['detail']) && $data_Output['detail']) {
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['detail'];
            } else {
                if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $data_Output['subscription_url'])) {
                    $data_Output['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($data_Output['subscription_url'], "/");
                }
                $links_raw = outputlunk($data_Output['subscription_url']);
                if (isBase64($links_raw)) {
                    $links_raw = base64_decode($links_raw);
                }
                $links_user = explode("\n", trim($links_raw));
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
            $inbounds = $Get_Data_Product['inbounds'] ?? $Get_Data_Panel['inboundid'];
            $data_Output = addClient($Get_Data_Panel['name_panel'], $usernameC, $expire, $data_limit, generateUUID(), "", $subId, $inbounds, $Get_Data_Product['name_product'], $note);
            if (!empty($data_Output['error'])) {
                return array('status' => 'Unsuccessful', 'msg' => $data_Output['error']);
            } elseif (!empty($data_Output['status']) && $data_Output['status'] != 200) {
                return array('status' => 'Unsuccessful', 'msg' => $data_Output['status']);
            } else {
                $data_Output = json_decode($data_Output['body'], true);
                if (!$data_Output['success']) {
                    $Output['status'] = 'Unsuccessful';
                    $Output['msg'] = $data_Output['msg'];
                } else {
                    $subscriptionUrl = rtrim($Get_Data_Panel['linksubx'], '/') . "/{$subId}";
                    $singleLink = get_single_link_after_create($Get_Data_Panel['url_panel'], $inbounds, $subscriptionUrl, $usernameC, $Get_Data_Panel['name_panel'], $Get_Data_Panel['code_panel'] ?? null);
                    $links_user = [];
                    if ($singleLink) $links_user[] = $singleLink;
                    $subscriptionLinks = get_subscription_links_with_retry($subscriptionUrl);
                    if (is_array($subscriptionLinks)) {
                        foreach ($subscriptionLinks as $linkItem) {
                            if (!in_array($linkItem, $links_user, true)) $links_user[] = $linkItem;
                        }
                    }
                    if (empty($links_user)) $links_user[] = 'در دسترس نیست';
                    $Output['status'] = 'successful';
                    $Output['username'] = $usernameC;
                    $Output['subscription_url'] = $subscriptionUrl;
                    $Output['configs'] = $links_user;
                    if ($inoice != false) $Output['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
                }
            }
        } elseif ($Get_Data_Panel['type'] == "alireza_single") {
            $subId = bin2hex(random_bytes(8));
            $Expireac = $expire * 1000;
            $inbounds = $Get_Data_Product['inbounds'] ?? $Get_Data_Panel['inboundid'];
            $data_Output = addClientalireza_singel($Get_Data_Panel['name_panel'], $usernameC, $Expireac, $data_limit, generateUUID(), "", $subId, $inbounds);
            if (!empty($data_Output['error'])) {
                return array('status' => 'Unsuccessful', 'msg' => $data_Output['error']);
            } elseif (!empty($data_Output['status']) && $data_Output['status'] != 200) {
                return array('status' => 'Unsuccessful', 'msg' => $data_Output['status']);
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
                    if ($inoice != false) $Output['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
                }
            }
        } elseif ($Get_Data_Panel['type'] == "hiddify") {
            $diff_days = ($expire != 0) ? ceil(($expire - time()) / 86400) : 111111;
            $uuid = generateUUID();
            $data = array("uuid" => $uuid, "name" => $usernameC, "added_by_uuid" => $Get_Data_Panel['secret_code'], "current_usage_GB" => "0", "usage_limit_GB" => $data_limit / pow(1024, 3), "package_days" => $diff_days, "comment" => $note);
            $data_Output = adduserhi($Get_Data_Panel['name_panel'], $data);
            if (!empty($data_Output['error'])) {
                return array('status' => 'Unsuccessful', 'msg' => $data_Output['error']);
            } elseif (!empty($data_Output['status']) && $data_Output['status'] != 200) {
                return array('status' => 'Unsuccessful', 'msg' => $data_Output['status']);
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
                if ($inoice != false) $Output['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
            }
        } elseif ($Get_Data_Panel['type'] == "Manualsale") {
            $statement = $pdo->prepare("SELECT * FROM manualsell WHERE codepanel = :code_panel AND status = 'active' AND codeproduct = :code_product ORDER BY RAND() LIMIT 1");
            $statement->execute(array(':code_panel' => $Get_Data_Panel['code_panel'], ':code_product' => $code_product));
            $configman = $statement->fetch(PDO::FETCH_ASSOC);
            if (!$configman) {
                return array('status' => 'Unsuccessful', 'msg' => 'No active manual sale found for this product.');
            }
            $Output['status'] = 'successful';
            $Output['username'] = $usernameC;
            $Output['subscription_url'] = $configman['contentrecord'];
            $Output['configs'] = "";
            update("manualsell", "status", "selled", "id", $configman['id']);
            update("manualsell", "username", $usernameC, "id", $configman['id']);
        } elseif ($Get_Data_Panel['type'] == "WGDashboard") {
            $data_limit_gb = round($data_limit / (1024 * 1024 * 1024), 2);
            $data_Output = addpear($Get_Data_Panel['name_panel'], $usernameC);
            if (isset($data_Output['status']) && $data_Output['status'] === false) return array('status' => 'Unsuccessful', 'msg' => $data_Output['msg'] ?? '');
            if (!empty($data_Output['status']) && $data_Output['status'] != 200) return array('status' => 'Unsuccessful', 'msg' => $data_Output['status']);
            if (!empty($data_Output['error'])) return array('status' => 'Unsuccessful', 'msg' => $data_Output['error']);
            $data_Output = $data_Output['body'];
            $response = json_decode($data_Output['response'], true);
            if ($data_limit_gb != 0) setjob($Get_Data_Panel['name_panel'], "total_data", $data_limit_gb, $data_Output['public_key']);
            if ($expire != 0) setjob($Get_Data_Panel['name_panel'], "date", date('Y-m-d H:i:s', $expire), $data_Output['public_key']);
            update("invoice", "user_info", json_encode($data_Output), "username", $usernameC);
            if (!$response['status']) {
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['msg'] ?? '';
            } else {
                $download_config = downloadconfig($Get_Data_Panel['name_panel'], $data_Output['public_key']);
                $download_config = json_decode($download_config['body'], true)['data'];
                $Output['status'] = 'successful';
                $Output['username'] = $usernameC;
                $Output['subscription_url'] = strval($download_config['file']);
                $Output['configs'] = [];
            }
        } elseif ($Get_Data_Panel['type'] == "s_ui") {
            if ($Get_Data_Product['inbounds'] != null) $Get_Data_Panel['inbounds'] = $Get_Data_Product['inbounds'];
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
            $name_group = $Get_Data_Product['inbounds'] ?? ($code_product == "usertest" ? "usertest" : $Get_Data_Panel['proxies']);
            $data_Output = addUserIBsng($Get_Data_Panel['name_panel'], $usernameC, $password, $name_group);
            if (!$data_Output) {
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['msg'] ?? 'Error';
            } else {
                $Output['status'] = 'successful';
                $Output['username'] = $usernameC;
                $Output['subscription_url'] = $password;
                $Output['configs'] = [];
            }
        } elseif ($Get_Data_Panel['type'] == "mikrotik") {
            $password = bin2hex(random_bytes(6));
            $name_group = $Get_Data_Product['inbounds'] ?? ($code_product == "usertest" ? "usertest" : $Get_Data_Panel['proxies']);
            $data_Output = addUser_mikrotik($Get_Data_Panel['name_panel'], $usernameC, $password, $name_group);
            if (isset($data_Output['error'])) {
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['msg'] ?? 'Error';
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
            $Output['configs'] = normalizeServiceConfigs($Output['configs'] ?? null, $Output['subscription_url'] ?? null);
        } else {
            if (!isset($Output['configs'])) $Output['configs'] = [];
            elseif (!is_array($Output['configs'])) {
                $val = trim((string)$Output['configs']);
                $Output['configs'] = ($val === '') ? [] : [$val];
            }
        }
        return $Output;
    }

    function DataUser($name_panel, $username)
    {
        $Output = array();
        global $pdo, $domainhosts, $new_marzban;
        $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel, "select");
        if (!$Get_Data_Panel || !is_array($Get_Data_Panel)) {
            return array('status' => 'Unsuccessful', 'msg' => 'Panel Not Found');
        }
        $inoice = (isset($Get_Data_Panel['subvip']) && $Get_Data_Panel['subvip'] == "onsubvip") ? select("invoice", "*", "username", $username, "select") : false;

        if ($Get_Data_Panel['type'] == "marzban") {
            $UsernameData = getuser($username, $Get_Data_Panel['name_panel']);
            if (!empty($UsernameData['error'])) return array('status' => 'Unsuccessful', 'msg' => $UsernameData['error']);
            if (!empty($UsernameData['status']) && $UsernameData['status'] == 500) return array('status' => 'Unsuccessful', 'msg' => $UsernameData['status']);
            $UsernameData = json_decode($UsernameData['body'], true);
            if (!empty($UsernameData['detail'])) return array('status' => 'Unsuccessful', 'msg' => $UsernameData['detail']);
            if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $UsernameData['subscription_url'])) {
                $UsernameData['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($UsernameData['subscription_url'], "/");
            }
            if ($new_marzban) {
                $UsernameData['expire'] = strtotime($UsernameData['expire']);
                $UsernameData['links'] = explode("\n", base64_decode(outputlunk($UsernameData['subscription_url'])));
                $sublist_update = get_list_update($name_panel, $username);
                $sublist_update = json_decode($sublist_update['body'], true);
                $UsernameData['sub_updated_at'] = $sublist_update['updates'][0]['created_at'] ?? null;
                $UsernameData['sub_last_user_agent'] = $sublist_update['updates'][0]['user_agent'] ?? null;
            }
            if ($inoice != false) $UsernameData['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
            $Output = array(
                'status' => $UsernameData['status'], 'username' => $UsernameData['username'], 'data_limit' => $UsernameData['data_limit'], 'expire' => $UsernameData['expire'],
                'online_at' => $UsernameData['online_at'], 'used_traffic' => $UsernameData['used_traffic'], 'links' => $UsernameData['links'], 'subscription_url' => $UsernameData['subscription_url'],
                'sub_updated_at' => $UsernameData['sub_updated_at'] ?? null, 'sub_last_user_agent' => $UsernameData['sub_last_user_agent'] ?? null, 'uuid' => $UsernameData['proxy_settings'] ?? null,
                'data_limit_reset' => $UsernameData['data_limit_reset_strategy']
            );
        } elseif ($Get_Data_Panel['type'] == "marzneshin") {
            $UsernameData = getuserm($username, $Get_Data_Panel['name_panel']);
            $UsernameData = json_decode($UsernameData['body'], true);
            if (isset($UsernameData['detail']) || !isset($UsernameData['username'])) return array('status' => 'Unsuccessful', 'msg' => $UsernameData['detail'] ?? "Unsuccessful");
            if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $UsernameData['subscription_url'])) {
                $UsernameData['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($UsernameData['subscription_url'], "/");
            }
            $status = "active";
            if (!$UsernameData['enabled']) $status = "disabled";
            if ($UsernameData['expire_strategy'] == "start_on_first_use") $status = "on_hold";
            if ($UsernameData['expired']) $status = "expired";
            if ($UsernameData['data_limit'] != null && ($UsernameData['data_limit'] - $UsernameData['used_traffic'] <= 0)) $status = "limtied";
            $links_raw = outputlunk($UsernameData['subscription_url']);
            $links_user = explode("\n", trim(isBase64($links_raw) ? base64_decode($links_raw) : $links_raw));
            if ($inoice != false) $UsernameData['subscription_url'] = "https://$domainhosts/sub/" . $inoice['id_invoice'];
            $Output = array(
                'status' => $status, 'username' => $UsernameData['username'], 'data_limit' => $UsernameData['data_limit'] ?? 0, 'expire' => isset($UsernameData['expire_date']) ? strtotime($UsernameData['expire_date']) : 0,
                'online_at' => $UsernameData['online_at'], 'used_traffic' => $UsernameData['used_traffic'], 'links' => $links_user, 'subscription_url' => $UsernameData['subscription_url'],
                'sub_updated_at' => $UsernameData['sub_updated_at'] ?? null, 'sub_last_user_agent' => $UsernameData['sub_last_user_agent'] ?? null, 'uuid' => null
            );
        } elseif ($Get_Data_Panel['type'] == "x-ui_single") {
            $user_data = get_clinets($username, $Get_Data_Panel['name_panel']);
            $user_data = json_decode($user_data['body'], true);
            if (empty($user_data['obj'])) return array('status' => 'Unsuccessful', 'msg' => "User not found");
            $user_data = $user_data['obj'];
            $expire = $user_data['expiryTime'] / 1000;
            $status = $user_data['enable'] ? "active" : "disabled";
            if ($user_data['total'] != 0 && ($user_data['total'] - ($user_data['up'] + $user_data['down']) <= 0)) $status = "limited";
            if ($user_data['expiryTime'] != 0 && ($expire - time() <= 0)) $status = "expired";
            if ($user_data['expiryTime'] < -10000) { $status = "on_hold"; $expire = 0; }
            $subUrl = rtrim($Get_Data_Panel['linksubx'], '/') . "/{$user_data['subId']}";
            $links_raw = outputlunk($subUrl);
            $links_user = array_values(array_filter(array_map('trim', explode("\n", isBase64($links_raw) ? base64_decode($links_raw) : $links_raw))));
            if ($inoice != false) $subUrl = "https://$domainhosts/sub/" . $inoice['id_invoice'];
            $Output = array(
                'status' => $status, 'username' => $user_data['email'], 'data_limit' => $user_data['total'], 'expire' => $expire, 'online_at' => $user_data['lastOnline'] == 0 ? "offline" : date('Y-m-d H:i:s', $user_data['lastOnline'] / 1000),
                'used_traffic' => $user_data['up'] + $user_data['down'], 'links' => $links_user, 'subscription_url' => $subUrl, 'sub_updated_at' => null, 'sub_last_user_agent' => null
            );
        } elseif ($Get_Data_Panel['type'] == "alireza_single") {
            $user_data = get_clinetsalireza($username, $Get_Data_Panel['name_panel'])[0] ?? null;
            if (!$user_data) return array('status' => 'Unsuccessful', 'msg' => "User not found");
            $expire = ($user_data['expiryTime'] ?? 0) / 1000;
            $status = ($user_data['enable'] ?? false) ? "active" : "disabled";
            $up = $user_data['up'] ?? 0;
            $down = $user_data['down'] ?? 0;
            $total = $user_data['total'] ?? 0;
            if ($total != 0 && ($total - ($up + $down) <= 0)) $status = "limited";
            if ($expire != 0 && ($expire - time() <= 0)) $status = "expired";
            $subUrl = $Get_Data_Panel['linksubx'] . "/" . ($user_data['subId'] ?? '');
            $links_raw = outputlunk($subUrl);
            $links_user = array_values(array_filter(array_map('trim', explode("\n", isBase64($links_raw) ? base64_decode($links_raw) : $links_raw))));
            if ($inoice != false) $subUrl = "https://$domainhosts/sub/" . $inoice['id_invoice'];
            $Output = array(
                'status' => $status, 'username' => $user_data['email'] ?? $username, 'data_limit' => $total, 'expire' => $expire, 'online_at' => get_onlineclialireza($Get_Data_Panel['name_panel'], $username),
                'used_traffic' => $up + $down, 'links' => $links_user, 'subscription_url' => $subUrl, 'sub_updated_at' => null, 'sub_last_user_agent' => null
            );
        } elseif ($Get_Data_Panel['type'] == "hiddify") {
            $UsernameData = getdatauser($username, $Get_Data_Panel['name_panel']);
            if (!isset($UsernameData)) return array('status' => 'Unsuccessful', 'msg' => "Not Connected");
            if (isset($UsernameData['message'])) return array('status' => 'Unsuccessful', 'msg' => $UsernameData['message']);
            $startDate = $UsernameData['start_date'] ?? null;
            $expire = ($startDate === null) ? 0 : strtotime($startDate) + ($UsernameData['package_days'] * 86400);
            $usageLimit = ($UsernameData['usage_limit_GB'] ?? 0) * pow(1024,3);
            $currentUsage = ($UsernameData['current_usage_GB'] ?? 0) * pow(1024,3);
            $status = ($usageLimit > 0 && ($usageLimit - $currentUsage <= 0)) ? "limited" : (($expire != 0 && $expire - time() <= 0) ? "expired" : (($startDate === null) ? "on_hold" : "active"));
            $linkUrl = ($UsernameData['uuid'] ?? false) ? "{$Get_Data_Panel['linksubx']}/{$UsernameData['uuid']}/" : $Get_Data_Panel['linksubx'];
            if ($inoice != false) $linkUrl = "https://$domainhosts/sub/" . $inoice['id_invoice'];
            $Output = array(
                'status' => $status, 'username' => $UsernameData['name'] ?? $username, 'data_limit' => $usageLimit, 'expire' => $expire, 'online_at' => ($UsernameData['last_online'] == "1-01-01 00:00:00") ? null : $UsernameData['last_online'],
                'used_traffic' => $currentUsage, 'links' => [], 'subscription_url' => $linkUrl, 'sub_updated_at' => null, 'sub_last_user_agent' => null
            );
        } elseif ($Get_Data_Panel['type'] == "WGDashboard") {
            $UsernameData = get_userwg($username, $Get_Data_Panel['name_panel']);
            if (empty($UsernameData['id'])) return array('status' => 'Unsuccessful', 'msg' => $UsernameData['msg'] ?? '');
            $invoiceinfo = select("invoice", "*", "username", $username, "select");
            $jobv = 0; $expire = 0;
            foreach($UsernameData['jobs'] as $j) {
                if ($j['Field'] == "total_data") $jobv = $j['Value'];
                if ($j['Field'] == "date") $expire = strtotime($j['Value']);
            }
            $status = $UsernameData['configuration']['Status'] ? "active" : "disabled";
            if ($expire != 0 && $expire - time() < 0) $status = "expired";
            $usage = ($UsernameData['total_data'] + $UsernameData['cumu_data']) * pow(1024,3);
            if (($jobv * pow(1024,3)) < $usage) $status = "limited";
            $dl = downloadconfig($Get_Data_Panel['name_panel'], $UsernameData['id']);
            $dl = json_decode($dl['body'], true)['data'];
            $Output = array(
                'status' => $status, 'username' => $UsernameData['name'], 'data_limit' => $jobv * pow(1024,3), 'expire' => $expire, 'online_at' => null, 'used_traffic' => $usage,
                'links' => [], 'subscription_url' => strval($dl['file']), 'sub_updated_at' => null, 'sub_last_user_agent' => null
            );
        } elseif ($Get_Data_Panel['type'] == "s_ui") {
            $UsernameData = GetClientsS_UI($username, $Get_Data_Panel['name_panel']);
            if (!isset($UsernameData['id'])) return array('status' => 'Unsuccessful', 'msg' => $UsernameData['msg']);
            $links = [];
            if (is_array($UsernameData['links'])) foreach($UsernameData['links'] as $c) $links[] = $c['uri'];
            $usage = $UsernameData['up'] + $UsernameData['down'];
            $status = $UsernameData['enable'] ? "active" : (($UsernameData['volume'] != 0 && $UsernameData['volume'] - $usage < 0) ? "limited" : (($UsernameData['expiry'] != 0 && $UsernameData['expiry'] - time() < 0) ? "expired" : "disabled"));
            $urlParts = explode(":", $Get_Data_Panel['url_panel']);
            $setting_app = get_settig($Get_Data_Panel['name_panel']);
            $url_sub = $urlParts[0] . ":" . $urlParts[1] . ":" . $setting_app['subPort'] . $setting_app['subPath'] . $username;
            $Output = array(
                'status' => $status, 'username' => $UsernameData['name'], 'data_limit' => $UsernameData['volume'], 'expire' => $UsernameData['expiry'], 'online_at' => get_onlineclients_ui($Get_Data_Panel['name_panel'], $username),
                'used_traffic' => $usage, 'links' => $links, 'subscription_url' => $url_sub, 'sub_updated_at' => null, 'sub_last_user_agent' => null
            );
        } elseif ($Get_Data_Panel['type'] == "ibsng") {
            $UsernameData = GetUserIBsng($Get_Data_Panel['name_panel'], $username);
            if (!$UsernameData['status']) return array('status' => 'Unsuccessful', 'msg' => $UsernameData['msg']);
            $d = $UsernameData['data'];
            $Output = array(
                'status' => "active", 'username' => $d['username'], 'data_limit' => $d['data_limit'], 'expire' => strtotime($d['absolute_expire_date']), 'online_at' => strtolower($d['status']),
                'used_traffic' => $d['used_traffic'], 'links' => [], 'subscription_url' => $d['password'], 'sub_updated_at' => null, 'sub_last_user_agent' => null
            );
        } elseif ($Get_Data_Panel['type'] == "mikrotik") {
            $UsernameData = GetUsermikrotik($Get_Data_Panel['name_panel'], $username)[0] ?? null;
            if (!$UsernameData || isset($UsernameData['error'])) return array('status' => 'Unsuccessful', 'msg' => "User not found");
            $inv = select("invoice", "*", "username", $username, "select");
            $traffic = GetUsermikrotik_volume($Get_Data_Panel['name_panel'], $UsernameData['.id']);
            $Output = array(
                'status' => "active", 'username' => $username, 'data_limit' => $inv['Volume'] * pow(1024,3), 'expire' => $inv['time_sell'] + ($inv['Service_time'] * 86400), 'online_at' => null,
                'used_traffic' => $traffic['total-upload'] + $traffic['total-download'], 'links' => [], 'subscription_url' => $UsernameData['password'], 'sub_updated_at' => null, 'sub_last_user_agent' => null
            );
        } else {
            $Output = array('status' => 'Unsuccessful', 'msg' => 'Panel Not Found');
        }
        return $Output;
    }

    function Revoke_sub($name_panel, $username)
    {
        $Output = array();
        $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel, "select");
        if ($Get_Data_Panel['type'] == "marzban") {
            $revoke = revoke_sub($username, $name_panel);
            if (isset($revoke['detail'])) return array('status' => 'Unsuccessful', 'msg' => $revoke['detail']);
            $du = $this->DataUser($name_panel, $username);
            if (!preg_match('/^(https?:\/\/)?/', $du['subscription_url'])) $du['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($du['subscription_url'], "/");
            $Output = array('status' => 'successful', 'configs' => $du['links'], 'subscription_url' => $du['subscription_url']);
        } elseif ($Get_Data_Panel['type'] == "marzneshin") {
            $revoke = revoke_subm($username, $name_panel);
            if (isset($revoke['detail'])) return array('status' => 'Unsuccessful', 'msg' => $revoke['detail']);
            $du = $this->DataUser($name_panel, $username);
            $du['links'] = [base64_decode(outputlunk($du['subscription_url']))];
            $Output = array('status' => 'successful', 'configs' => $du['links'], 'subscription_url' => $du['subscription_url']);
        } elseif (in_array($Get_Data_Panel['type'], ["x-ui_single", "alireza_single"])) {
            $subId = bin2hex(random_bytes(8));
            $this->Modifyuser($username, $name_panel, array('settings' => json_encode(array('clients' => array(array("id" => generateUUID(), "enable" => true, "subId" => $subId))))));
            $url = $Get_Data_Panel['linksubx'] . "/{$subId}";
            $Output = array('status' => 'successful', 'configs' => [outputlunk($url)], 'subscription_url' => $url);
        } elseif ($Get_Data_Panel['type'] == "s_ui") {
            $clients = GetClientsS_UI($username, $name_panel);
            $usernameac = $username;
            $configpanel = array(
                "object" => 'clients', 'action' => "edit",
                "data" => json_encode(array(
                    "id" => $clients['id'], "enable" => $clients['enable'], "name" => $usernameac,
                    "config" => array(
                        "mixed" => array("username" => $usernameac, "password" => generateAuthStr()),
                        "socks" => array("username" => $usernameac, "password" => generateAuthStr()),
                        "http" => array("username" => $usernameac, "password" => generateAuthStr()),
                        "shadowsocks" => array("name" => $usernameac, "password" => bin2hex(random_bytes(16))),
                        "vmess" => array("name" => $usernameac, "uuid" => generateUUID(), "alterId" => 0),
                        "vless" => array("name" => $usernameac, "uuid" => generateUUID(), "flow" => ""),
                        "trojan" => array("name" => $usernameac, "password" => generateAuthStr()),
                    ),
                    "inbounds" => $clients['inbounds'], "links" => [], "volume" => $clients['volume'], "expiry" => $clients['expiry'], "desc" => $clients['desc']
                )),
            );
            if (!updateClientS_ui($name_panel, $configpanel)['success']) return array('status' => 'Unsuccessful', 'msg' => 'Unsuccessful');
            $setting_app = get_settig($name_panel);
            $urlParts = explode(":", $Get_Data_Panel['url_panel']);
            $url_sub = $urlParts[0] . ":" . $urlParts[1] . ":" . $setting_app['subPort'] . $setting_app['subPath'] . $username;
            $Output = array('status' => 'successful', 'configs' => [outputlunk($url_sub)], 'subscription_url' => $url_sub);
        } else {
            $Output = array('status' => 'Unsuccessful', 'msg' => 'Panel Not Supported');
        }
        return $Output;
    }

    function RemoveUser($name_panel, $username)
    {
        $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel, "select");
        if (!$Get_Data_Panel) return array('status' => 'Unsuccessful', 'msg' => 'Panel Not Found');
        $type = $Get_Data_Panel['type'];
        if ($type == "marzban") $res = removeuser($name_panel, $username);
        elseif ($type == "marzneshin") $res = removeuserm($name_panel, $username);
        elseif ($type == "x-ui_single") $res = removeClient($name_panel, $username);
        elseif ($type == "alireza_single") $res = removeClientalireza_single($name_panel, $username);
        elseif ($type == "hiddify") { $du = getdatauser($username, $name_panel); removeuserhi($name_panel, $du['uuid']); return array('status' => 'successful'); }
        elseif ($type == "Manualsale") { update("manualsell", "status", "delete", "username", $username); return array('status' => 'successful'); }
        elseif ($type == "WGDashboard") $res = remove_userwg($name_panel, $username);
        elseif ($type == "s_ui") $res = removeClientS_ui($name_panel, $username);
        elseif ($type == "ibsng") $res = deleteUserIBSng($name_panel, $username);
        elseif ($type == "mikrotik") { 
            $du = GetUsermikrotik($name_panel, $username)[0] ?? null;
            if ($du) deleteUser_mikrotik($name_panel, $du['.id']);
            return array('status' => 'successful');
        }
        else return array('status' => 'Unsuccessful', 'msg' => 'Panel Not Found');

        if (is_array($res) && isset($res['status']) && $res['status'] != 200) return array('status' => 'Unsuccessful', 'msg' => $res['status']);
        return array('status' => 'successful', 'username' => $username);
    }

    function Modifyuser($username, $name_panel, $config = array())
    {
        global $new_marzban;
        $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel, "select");
        if (!$Get_Data_Panel) return array('status' => false, 'msg' => 'Panel Not Found');
        
        if ($Get_Data_Panel['type'] == "marzban") {
            if ($new_marzban) {
                $r = json_decode(getuser($username, $name_panel)['body'], true);
                $config['proxy_settings'] = $r['proxy_settings'];
            }
            $res = Modifyuser($name_panel, $username, $config);
            return array('status' => true, 'data' => $res);
        } elseif ($Get_Data_Panel['type'] == "marzneshin") {
            $config['username'] = $username;
            $res = Modifyuserm($name_panel, $username, $config);
            return array('status' => true, 'data' => $res);
        } elseif ($Get_Data_Panel['type'] == "x-ui_single") {
            $c = json_decode(get_clinets($username, $name_panel)['body'], true)['obj'];
            $cfg = array('id' => intval($c['inboundId']), 'settings' => json_encode(array('clients' => array(array("id" => $c['uuid'], "flow" => "", "email" => $c['email'], "totalGB" => $c['total'], "expiryTime" => $c['expiryTime'], "enable" => true, "subId" => $c['subId'])))));
            $cfg['settings'] = json_encode(array_replace_recursive(json_decode($cfg['settings'], true), json_decode($config['settings'] ?? '{}', true)));
            updateClient($name_panel, $c['uuid'], $cfg);
            return array('status' => true);
        } elseif ($Get_Data_Panel['type'] == "alireza_single") {
            $c = get_clinetsalireza($username, $name_panel)[0];
            $cfg = array('id' => intval($Get_Data_Panel['inboundid']), 'settings' => json_encode(array('clients' => array(array("id" => $c['id'], "flow" => $c['flow'], "email" => $c['email'], "totalGB" => $c['totalGB'], "expiryTime" => $c['expiryTime'], "enable" => true, "subId" => $c['subId'])))));
            $cfg['settings'] = json_encode(array_replace_recursive(json_decode($cfg['settings'], true), json_decode($config['settings'] ?? '{}', true)));
            updateClientalireza($name_panel, $username, $cfg);
            return array('status' => true);
        } elseif ($Get_Data_Panel['type'] == "hiddify") {
            updateuserhi($username, $name_panel, $config);
            return array('status' => true);
        } elseif ($Get_Data_Panel['type'] == "WGDashboard") {
            $u = get_userwg($username, $name_panel);
            $cfg = array("DNS" => $u['DNS'], "allowed_ip" => $u['allowed_ip'], "endpoint_allowed_ip" => "0.0.0.0/0", "jobs" => $u['jobs'], "id" => $u['id'], "keepalive" => $u['keepalive'], "mtu" => $u['mtu'], "name" => $u['name'], "preshared_key" => $u['preshared_key'], "private_key" => $u['private_key']);
            updatepear($name_panel, array_merge($cfg, $config));
            return array('status' => true);
        } elseif ($Get_Data_Panel['type'] == "s_ui") {
            $c = GetClientsS_UI($username, $name_panel);
            $cfg = array("object" => 'clients', 'action' => "edit", "data" => json_encode(array_merge(array("id" => $c['id'], "enable" => $c['enable'], "name" => $username, "config" => $c['config'], "inbounds" => $c['inbounds'], "links" => $c['links'], "volume" => $c['volume'], "expiry" => $c['expiry'], "desc" => $c['desc']), $config)));
            updateClientS_ui($name_panel, $cfg);
            return array('status' => true);
        }
        return array('status' => false, 'msg' => 'Not supported');
    }

    function Change_status($username, $name_panel)
    {
        $du = $this->DataUser($name_panel, $username);
        $p = select("marzban_panel", "*", "name_panel", $name_panel, "select");
        if ($du['status'] == "Unsuccessful") return array('status' => 'Unsuccessful');
        $status = ($du['status'] == "active") ? "disabled" : "active";
        
        if ($p['type'] == "marzban") $this->Modifyuser($username, $name_panel, array("status" => $status));
        elseif ($p['type'] == "marzneshin") { if ($status == "disabled") disableduser($name_panel, $username); else enableuser($name_panel, $username); }
        elseif (in_array($p['type'], ["x-ui_single", "alireza_single"])) $this->Modifyuser($username, $name_panel, array('settings' => json_encode(array('clients' => array(array("enable" => ($status == "active")))))));
        elseif ($p['type'] == "s_ui") $this->Modifyuser($username, $name_panel, array("enable" => ($status == "active")));
        
        return array('status' => 'successful');
    }

    function ResetUserDataUsage($username, $name_panel)
    {
        $p = select("marzban_panel", "*", "name_panel", $name_panel, "select");
        if (!$p) return array('status' => false);
        if ($p['type'] == "marzban") ResetUserDataUsage($username, $name_panel);
        elseif ($p['type'] == "marzneshin") ResetUserDataUsagem($username, $name_panel);
        elseif ($p['type'] == 'x-ui_single') ResetUserDataUsagex_uisin($username, $name_panel);
        elseif ($p['type'] == 'alireza_single') ResetUserDataUsagealirezasin($username, $name_panel);
        elseif ($p['type'] == "WGDashboard") {
            allowAccessPeers($name_panel, $username);
            $u = get_userwg($username, $name_panel);
            ResetUserDataUsagewg($u['id'], $name_panel);
        } elseif ($p['type'] == "s_ui") ResetUserDataUsages_ui($username, $name_panel);
        return array('status' => true);
    }

    function extend($Method_extend, $new_limit, $time_day, $username, $code_product, $name_panel)
    {
        $p = select("marzban_panel", "*", "code_panel", $name_panel, "select");
        $du = $this->DataUser($p['name_panel'], $username);
        if ($du['status'] == "Unsuccessful") return array('status' => false, 'msg' => $du['msg']);
        
        $lim_old = $du['data_limit']; $t_old = max(time(), $du['expire']);
        $lim_new = $new_limit * pow(1024,3); $t_new = time() + $time_day * 86400;
        
        if ($Method_extend == "ریست حجم و زمان") $this->ResetUserDataUsage($username, $p['name_panel']);
        elseif ($Method_extend == "اضافه شدن زمان و حجم به ماه بعد") { $lim_new += $lim_old; $t_new = $t_old + ($time_day * 86400); }
        elseif ($Method_extend == "ریست زمان و اضافه کردن حجم قبلی") { $lim_new += $lim_old; }
        elseif ($Method_extend == "ریست شدن حجم و اضافه شدن زمان") { $this->ResetUserDataUsage($username, $p['name_panel']); $t_new = $t_old + ($time_day * 86400); }
        elseif ($Method_extend == "اضافه شدن زمان و تبدیل حجم کل به حجم باقی مانده") {
            $this->ResetUserDataUsage($username, $p['name_panel']);
            $t_new = $t_old + ($time_day * 86400);
            $lim_new += max(0, $du['data_limit'] - $du['used_traffic']);
        }
        
        $data = [];
        if ($p['type'] == "marzban") $data = array('data_limit' => $lim_new, 'expire' => $t_new);
        elseif ($p['type'] == "marzneshin") $data = array('expire_date' => date('c', $t_new), 'expire_strategy' => $t_new == 0 ? "never" : "fixed_date", 'data_limit' => $lim_new);
        elseif (in_array($p['type'], ["x-ui_single", "alireza_single"])) $data = array('settings' => json_encode(array('clients' => array(array("totalGB" => $lim_new, "expiryTime" => $t_new * 1000, "enable" => true)))));
        elseif ($p['type'] == "hiddify") $data = array("package_days" => ($t_new - time()) / 86400, "usage_limit_GB" => $lim_new / pow(1024,3), "start_date" => null);
        elseif ($p['type'] == "s_ui") $data = array("volume" => $lim_new, "expiry" => $t_new);
        
        return $this->Modifyuser($username, $p['name_panel'], $data);
    }

    function extra_volume($username_account, $code_panel, $limit_volume_new)
    {
        $p = select("marzban_panel", "*", "code_panel", $code_panel, "select");
        $du = $this->DataUser($p['name_panel'], $username_account);
        $lim_new = ($limit_volume_new * pow(1024,3)) + $du['data_limit'];
        $data = [];
        if ($p['type'] == "marzban") $data = array('data_limit' => $lim_new);
        elseif ($p['type'] == "marzneshin") $data = array('data_limit' => $lim_new);
        elseif (in_array($p['type'], ["x-ui_single", "alireza_single"])) $data = array('settings' => json_encode(array('clients' => array(array("totalGB" => $lim_new)))));
        elseif ($p['type'] == "hiddify") $data = array("usage_limit_GB" => $lim_new / pow(1024,3));
        elseif ($p['type'] == "s_ui") $data = array("volume" => $lim_new);
        return $this->Modifyuser($username_account, $p['name_panel'], $data);
    }

    function extra_time($username_account, $code_panel, $limit_time_new)
    {
        $p = select("marzban_panel", "*", "code_panel", $code_panel, "select");
        $du = $this->DataUser($p['name_panel'], $username_account);
        $t_new = max(time(), $du['expire']) + ($limit_time_new * 86400);
        $data = [];
        if ($p['type'] == "marzban") $data = array('expire' => $t_new);
        elseif ($p['type'] == "marzneshin") $data = array('expire_date' => $t_new, 'expire_strategy' => "fixed_date");
        elseif (in_array($p['type'], ["x-ui_single", "alireza_single"])) $data = array('settings' => json_encode(array('clients' => array(array("expiryTime" => $t_new * 1000)))));
        elseif ($p['type'] == "hiddify") $data = array("package_days" => ($t_new - time()) / 86400, "start_date" => null);
        elseif ($p['type'] == "s_ui") $data = array("expiry" => $t_new);
        return $this->Modifyuser($username_account, $p['name_panel'], $data);
    }
}
