<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/4/18
 * Time: 11:27
 * 玩家角色等级获取
 * 渠道商将通过调用接口取得玩家等级等信息
 * 文档地址：http://api.eyougame.com/html/userinfo.html
 */
require CURR_PLATFORM_DIR.'EYouSDK.class.php';

$p = API::param()->getParams();
$sdk = new EYouSDK();
API::log($p, 'eyou_role_level', 'request');

if($p['sign'] !== $sdk->createSign($p, $p['gameid'])) out(0, 'sign验证失败');
list($platform, $zone_id) = explode('_', $p['serverid']);
$time    = (int)$p['time'];
$roleId = (int)$p['roleid'];

$api = GameApi::call('Role');
$api->queryField = 'rid user_id, account uid, name user_name, sex, lev';
$ret = $api->getRoleByRid($roleId, $platform, $zone_id);

if ($ret['error'] !== 'OK') {
    out(-1, '错误的查询');
} else {
    API::log($ret, 'eyou_role_level', 'request');
    $money = Db::getInstance()->getRow("SELECT sum(money) money, MAX(currency_type) currency  FROM mod_charge WHERE rid = {$roleId} and srv_id = '{$p['serverid']}'");
    API::log($money, 'eyou_role_level', 'request');
    out(1, ' 获取成功', $ret['data'][0]['lev'], $money);
}



function out($code, $msg , $level = 0, $totalAmount = 0, $currency = 'TWD') {
    echo json_encode(array('Code' => $code, 'Reason' => $msg, 'level' => $level, 'total_amount' => intval($totalAmount), 'currency' => $currency, 'onlinetime' => 0));
    exit;
}