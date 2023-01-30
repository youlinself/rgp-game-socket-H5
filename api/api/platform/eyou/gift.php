<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/1/23
 * Time: 19:45
 * 礼包接口
 * 渠道商将通过调用接口给游戏内玩家发送相应的活动礼包。
 * 文档地址：http://api.eyougame.com/html/gift.html
 */
require CURR_PLATFORM_DIR.'EYouSDK.class.php';
//获取请求才和日志记录
$p = API::param()->getParams();
if(EYouSDK::DEBUG) API::log($p, 'eyou_gift', 'request');
$sdk = new EYouSDK();
if($p['sign'] !== $sdk->createSign($p, $p['gameid'])) {
    API::log('sign验证失败', 'eyou_gift', 'gift_ret');
    out(0, 'sign验证失败');
}
//角色信息
$role_id = (int)$p['role_id'];
list($platform, $zone_id) = explode('_', $p['s_id']);
$pack_id = (int)$p['pid'];
//奖品码，唯一
$orderId = $zone_id . $role_id . $p['singleno'];
API::log('发送角色信息:'.$role_id.'_'.$platform.'_'.$zone_id.' 发货ID:'. $pack_id . '奖品码:'.$orderId , 'eyou_gift', 'gift_ret');
//通知服务端发送礼包

$ret = GameApi::call('GM')->eYouGift($role_id, $platform, $zone_id, $orderId, $pack_id);
if(EYouSDK::DEBUG) API::log($ret, 'eyou_gift', 'gift_ret');
if ($ret['success']) {
    out(1, $ret['message']);
} else {
    out(0, '返回不成功:'. var_export($ret['message'], 1));
}

//礼包信息格式返回
function out($code, $msg)
{
    global $sdk;
    $sdk->out(array('code' => $code,  'msg' => $msg));
}