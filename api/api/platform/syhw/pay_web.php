<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/8/12
 * Time: 15:22
 */
require CURR_PLATFORM_DIR. './SYHWSDK.class.php';

$p = stripQuotes(API::param()->getParams());
if (SYHWSDK::DEBUG) API::log($p, 'syhw_pay_web', 'request');
//参数验证
if(empty($p['srv_id'])) {
    API::log(array('msg' => '缺少srv_id参数内容'), 'syhw_pay_web', 'request');
    exit('fail');
}

//解析数据
$exp = explode('_', $p['srv_id']);
//sign校验
if ($p['sign'] !== SYHWSDK::createPaySign($p, 'pay_web')) {
    API::log(array('msg' => '订单签名错误'), 'syhw_pay_web', 'request');
    exit('error sign');
}
//参数组织
$rid      = (int)$p['role_id'];
$zone_id = (int)$exp[1];
$platform = $exp[0];
$amount    = (float)$p['money']; //成功充值金额
$order_no  = trim($p['order_id']);
//货币类型
$currency = $p['data']['currency'];
$category  = PayApi::TEST;
$package_id = $p['product_id'];
$payType   = empty($p['paywith']) ? '未知渠道' : $p['paywith'];
//充值额外参数
$ext  = [
    'pay_channel'  => $payType,
    'package_id'   => $package_id,
    'package_name' => $p['product_name'],
    'channel'      => 'shiyue|webhw',
    'charge_type'  => (int)0,
];

//判断是否有角色
$roleInfo = GameApi::call('Role')->getRoleByRid($rid, $platform, $zone_id);
API::log($roleInfo, 'syhw_pay_web', 'request');
if ($roleInfo['error'] != 'OK') {
    API::log(array('msg' => '无角色信息'), 'syhw_pay_web', 'request');
    exit('fail');
}

$payAccount = 'xinma_'. $p['account_id'];
$roleAccount = $roleInfo['data'][0]['account'];

//充值账号判断
if($payAccount != $roleAccount) {
    API::log('充值账号跟角色注册帐号不匹配,角色账号:'. $roleAccount .' 充值账号:'. $payAccount, 'syhw_pay_web', 'error');
    exit('fail');
}

//货币类型
$currency = $p['currency'];
//发货接口
$api = new PayApi($rid, $platform, $zone_id);
$ret = $api->pay($order_no, $amount, $ext, $category, $currency);
if(SYHWSDK::DEBUG) API::log($ret, 'syhw_pay_web', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
    case 'ORDER_EXISTS':
        exit('success');break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        exit('fail'); break;
}
