<?php
/**
 * User: weiming Email: 329403630@qq.com
 * Date: 2016/9/12
 * Time: 20:56
 * 我方专服 SDK 充值回调地址
 * 统一回调地址:
 */

require CURR_PLATFORM_DIR. './SYGAMESDK.class.php';

$p = API::param()->getParams();
if (SYGAMESDK::DEBUG) API::log($p, 'sygame_pay', 'callback_request');
//解析数据
$exp       = explode('$$', $p['exdata']); //附加参数格式：rid$$srv_id
$platform  = $exp[1];
$zone_id   = $exp[2];
//构造访问域名
$host = API::getCrossHost($zone_id, $platform);
try {
    $ret = API::callRemoteApi("http://".$host."/api.php/pf/sygame/pay", $p);
    API::log($ret, 'sygame_pay', 'call_remote_api');
    echo $ret['msg'];exit;
} catch (Exception $e) {
    API::log($e->getMessage(), 'sygame_pay', 'call_error');
    exit('callback error：'.$e->getMessage());
}