<?php
/**
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/6/6
 * Time: 15:00
 * oppo游戏 接口
 * 礼包统一回调地址:http://s1-symix-sszg.shiyuegame.com/api.php/pf/oppo/callback
 */
require CURR_PLATFORM_DIR . 'OPPOSDK.class.php';
$p = file_get_contents("php://input");
$p = json_decode($p, true);
if (OPPOSDK::DEBUG) API::log($p, 'oppo_callback', 'callback_request');
$data = OPPOSDK::decryptData($p['data']);

$act = isset($p['call_act']) && !empty($p['call_act']) ? $p['call_act'] : 'gift';
if (OPPOSDK::DEBUG) API::log($data, 'oppo_callback', 'callback_request');
$zone_id = $platform = '';
switch ($act) {
    case 'gift':
        $list = explode('_', $data['realmId']);
        if(empty($list)) OPPOSDK::giftOut(10, '错误的数据');
        $zone_id = (int)$list[1];
        $platform = $list[0];
        break;
        break;
    default:
        OPPOSDK::giftOut(10, '错误的call_act');
        break;
}
//数据转发
$host = API::getUrlHost($zone_id, $platform);
if(isset($p['call_act'])) unset($p['call_act']);
try {
    $ret = API::callRemoteApi("http://" . $host . "/api.php/pf/oppo/{$act}", $p);
    API::log($ret, 'oppo_callback', 'call_remote_api');
    echo $ret['msg'];
    exit;
} catch (Exception $e) {
    if (OPPOSDK::DEBUG) API::log($e->getMessage(), 'oppo_callback', 'callback_exception');
    OPPOSDK::giftOut(10, 'callback error：' . $e->getMessage());
}