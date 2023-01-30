<?php
/**
 * User: xiaoqing Email: liuxiaoqing437@gmail.com
 * Date: 2016/2/23
 * Time: 20:13
 * 漫维 充值回调接口
 */
require CURR_PLATFORM_DIR. './ManLingSDK.class.php';

//去掉自动转义(json格式)获取回调数据和日志记录
$p = stripQuotes(API::param()->getParams());
if(ManLingSDK::DEBUG) API::log($p, 'manling_pay', 'request');

//充值加密验证
if(!$p['data'] || !$p['sign']) exit('FAIL');
if(ManLingSDK::verify($p['data'], $p['sign']) != 1) exit('FAIL');

//解析data参数
$data     = json_decode($p['data'], true);
$exp      = explode('$$', $data['extension']); //附加参数格式：rid$$srv_id
$rid      = (int)$exp[0];
$platform =  $exp[1];
$zone_id  = (int)$exp[2];
$amount   = (float)$data['money']/100; //支付金额,以分为单位
$order_no = trim($data['orderID']);
$status   = (int)$data['state']; //订单支付状态 1表示支付成功
if($status !== 1) exit('FAIL');

//充值额外参数
$ext  = [
    'pay_channel'  => '未知渠道',
    'channel'      => $exp[3],
    'package_id'   => (int)$exp[4],
    'package_name' => $exp[5],
];

//判断是否为自充值账号
$roleInfo = GameApi::call('Role')->getRoleByRid($rid, $platform, $zone_id);
if ($roleInfo['error'] != 'OK') exit('查询角色失败');
$account = $roleInfo['data'][0]['account'];
if (GameApi::call('Role')->isChargeAccount($account)) {
    $category = PayApi::SELF;
} else {
    $category = PayApi::NORMAL;
}

$api = new PayApi($rid, $platform, $zone_id);
$ret = $api->pay($order_no, $amount, $ext, $category);
if(ManLingSDK::DEBUG) API::log($ret, 'manling_pay', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
    case 'ORDER_EXISTS':
        exit('SUCCESS');break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        exit('FAIL'); break;
}