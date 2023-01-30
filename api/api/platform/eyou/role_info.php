<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/1/31
 * Time: 17:56
 * 玩家角色id获取
 * 渠道商将通过调用接口取得玩家角色等信息
 * 文档地址：http://api.eyougame.com/html/userinfo.html
 */

require CURR_PLATFORM_DIR.'EYouSDK.class.php';
//获取请求数据和日志记录
$p = API::param()->getParams();
$sdk = new EYouSDK();
if(EYouSDK::DEBUG) API::log($p, 'eyou_role_info', 'request');
//参数验证
if($p['sign'] !== $sdk->createSign($p, $p['gameid'])) out(0, 'sign验证失败');

list($platform, $zone_id) = explode('_', $p['s_id']);
$game_id = (int)$p['gameid'];
$account = $sdk->setPayAcc($game_id, $p['uid'], $platform);

API::log('查询账号:'.$account, 'eyou_role_info', 'error');
$api = GameApi::call('Role');
$api->queryField = 'rid user_id, account uid, name user_name';
$ret = $api->getRoleByAccount($account, $platform, (int)$zone_id);

if ($ret['error'] !== 'OK') {
    out(-1, '错误的查询');
} else {
    if(EYouSDK::DEBUG) API::log($ret['data'], 'eyou_role_info', 'ret');
    out(1, ['user_list' => $ret['data']]);
}
//返回格式
function out($code, $msg) {
    global $sdk;
    $sdk->out(array('code' => $code, 'str'=>'获取玩家列表', 'msg' => $msg));
}
