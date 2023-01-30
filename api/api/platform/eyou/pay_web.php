<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/1/15
 * Time: 17:01
 * 充值结果推送接口二
 * 渠道商将通过 WEB/PC 充值结果反馈给游戏开发商请求发放虚拟物品(道具)
 * 文档地址：http://api.eyougame.com/html/webpay.html
 */
require CURR_PLATFORM_DIR.'EYouSDK.class.php';
//获取请求参数和
$p = API::param()->getParams();
$sdk = new EYouSDK();
if(EYouSDK::DEBUG) API::log($p, 'eyou_pay_web', 'request');
if($p['sign'] !== $sdk->createSign($p, $p['game_id'])) out(0, 'sign验证失败');
//组织充值数据
$currency = $p['currency'];//货币类型
$rid      = (int)$p['game_role_id'];
list($platform, $zone_id) = explode('_', $p['game_server_id']);
$amount   = (float)$p['total_fee']; //成功充值金额
$order_no = trim($p['order_id']);
$status   = (int)$p['pay_result']; //订单支付状态 1成功 0 失败
$order_type = $p['order_type'];
if($status !== 1) $sdk->out(['Success' => 0, 'Reason' => '订单购买结果错误']);
$package_id = 0;
$game_coin = (int)$p['game_coin'];
if($order_type > 0) {
    $package_id = $order_type;
}

//充值额外参数
$ext  = [
    'pay_channel'  => 'eyou自定义充值',
    'channel'      => $platform .'_web',
    'package_id'   => (int)$package_id,
    'package_name' => '',
    'gold' => $game_coin,
];

$acc = $sdk->setPayAcc($p['game_id'], $p['user_id'], $platform);
API::log('充值账号:'.$acc, 'eyou_pay_web', 'request');
//充值账号验证
if(!API::checkPayAccount($rid, $platform, (int)$zone_id, $acc)) {
    API::log('充值账号跟角色注册帐号不匹配', 'eyou_pay_web', 'error');
    $sdk->out(['Success' => 0, 'Reason' => '充值账号异常']);
}

$category = PayApi::NORMAL;
if((int)$p['is_sandbox'] === 1) $category = PayApi::TEST;
//充值发货
$api = new PayApi($rid, $platform, (int)$zone_id);
$ret = $api->pay($order_no, $amount, $ext, $category, $currency);
if(EYouSDK::DEBUG) API::log($ret, 'eyou_pay_web', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
    case 'ORDER_EXISTS':
         out(1, '');break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        out(0, $ret['msg']);break;
}

//充值信息返回
function out($code, $msg)
{
    global $sdk,$p;
    $sdk->out(['Success' => $code, 'Orderid' => $p['order_id'], 'Reason' => $msg]);
}