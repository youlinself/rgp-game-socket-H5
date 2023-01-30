<?php
/**
 * User: weiming Email: 329403630@qq.com
 * Date: 2017/7/29
 * Time: 15:00
 * 漫灵统一回调地址
 */
require CURR_PLATFORM_DIR. './ManLingSDK.class.php';

//去掉自动转义(json格式)
$p = stripQuotes(API::param()->getParams());
if(ManLingSDK::DEBUG) API::log($p, 'manling_pay', 'callback_request');

//解析data参数
$data = json_decode($p['data'], true);
$exp = explode('$$', $data['extension']); //附加参数格式：rid$$srv_id
$platform = $exp[1];
$zone_id = $exp[2];

//组织回调域名
$host = API::getCrossHost($zone_id, $platform);
try {
    $ret = API::callRemoteApi("http://".$host."/api.php/pf/manling/pay", $p);
    echo $ret['msg'];exit;
} catch (Exception $e) {
    exit('callback error：'.$e->getMessage());
}