<?php
/**
 * User: weiming Email: 329403630@qq.com
 * Date: 2016/9/12
 * Time: 20:56
 * 我方 SDK 充值接口
 */

require CURR_PLATFORM_DIR. './SYGAMESDK.class.php';
require CURR_PLATFORM_DIR. '../reyun/ReYunSDK.class.php';
$p = API::param()->getParams();
if (SYGAMESDK::DEBUG) API::log($p, 'sygame_pay', 'request');
//解析数据
$info      = explode('$$', $p['exdata']);//附加参数格式：rid$$srv_id|xxx
$cps       = trim($info[6]); //游戏别名
//sign校验
if ($p['sign'] !== SYGAMESDK::createPaySign($p, $cps)) exit('error sign');
//参数组织
$rid      = (int)$info[0];
$platform =  $info[1];
$zone_id  = (int)$info[2];
$amount    = (float)$p['amount']; //成功充值金额
$gold      = (int)($amount*10); //元宝最终获得由服务端发货
$order_no  = trim($p['order_id']);
$payType   = empty($p['paywith']) ? '未知渠道' : $p['paywith'];

//充值额外参数
$ext  = [
    'pay_channel'  => $payType,
    'channel'      => $info[3],
    'package_id'   => (int)$info[4],
    'package_name' => $info[5],
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

//发货接口
$api = new PayApi($rid, $platform, $zone_id);
$ret = $api->pay($order_no, $amount, $ext, $category);
if(SYGAMESDK::DEBUG) API::log($ret, 'sygame_pay', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
    case 'ORDER_EXISTS':
        if($ret['error'] != 'ORDER_EXISTS' && ReYunSDK::checkCPS($cps)) ReYunSDK::reYunReport($rid, $platform.'_'.$zone_id, $order_no, $amount, ReYunSDK::getReYunAppKey($cps));
        exit('success');break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        exit('fail'); break;
}
