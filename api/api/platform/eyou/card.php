<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/2/1
 * Time: 17:13
 * eYou 游戏 兑换码查询和发放接口
 */
require CURR_PLATFORM_DIR.'EYouSDK.class.php';
//获取请求参数和日志记录
$p = API::param()->getParams();
$sdk = new EYouSDK();
if(EYouSDK::DEBUG) API::log($p, 'eyou_card', 'request');
//sign验证
if($p['sign'] !== $sdk->createSign($p, $p['gameid'])) out(0, 'sign验证失败');
$role_id   = (int)$p['roleid'];
$card_no   = $p['cardno'];
$zone_id   = (int)$p['serverid'];
//通知服务端发送礼包
$ret = GameApi::call('GM')->cards($role_id, PLATFORM, $zone_id, $card_no);
if(EYouSDK::DEBUG) API::log($ret, 'eyou_card', 'gift_ret');
if ($ret['success']) {
    out(1, $ret['message']);
} else {
    out(0, '返回不成功:'. var_export($ret['message'], 1));
}
//兑换码信息格式返回
function out($code, $msg)
{
    global $sdk;
    $sdk->out(array('code' => $code,  'msg' => $msg));
}