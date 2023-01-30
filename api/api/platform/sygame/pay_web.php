<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/5/21
 * Time: 16:06
 * 平台官网充值地址
 */
require CURR_PLATFORM_DIR. './SYGAMESDK.class.php';

$p = stripQuotes(API::param()->getParams());
if (SYGAMESDK::DEBUG) API::log($p, 'sygame_pay_web', 'request');
//参数验证
if(empty($p['srv_id'])) {
    API::log(array('msg' => '缺少srv_id参数内容'), 'sygame_pay_web', 'request');
    exit('fail');
}

if((int)$p['status'] !== 1) {
    API::log(array('msg' => '订单status不为1'), 'sygame_pay_web', 'request');
    exit('fail');
}

//解析数据
$exp = explode('_', $p['srv_id']);
//sign校验
if ($p['sign'] !== SYGAMESDK::createPaySign($p, '')) {
    API::log(array('msg' => '订单签名错误'), 'sygame_pay_web', 'request');
    exit('error sign');
}
//参数组织
$rid      = (int)$p['role_id'];
$zone_id = (int)$exp[1];
$platform = $exp[0];
$amount    = (float)$p['amount']; //成功充值金额
$order_no  = trim($p['order_id']);
$package_id = (int)SYGAMESDK::getPackageId($amount); //根据映射表来
/*$subject_id = $p['subject_id'];
if(!is_null($subject_id) && $package_id !== (int)$subject_id)  {
    API::log(array('msg' => '商品ID跟充值金额匹配的ID不一致,充值金额:'. $amount.'的商品ID:'.$package_id.'充值携带商品ID:'. $subject_id), 'sygame_pay_web', 'request');
    exit('fail');
}*/
//充值额外参数
$ext  = [
    'pay_channel'  => '官网充值',
    'package_id'   => $package_id,
    'package_name' => '',
    'channel'      => 'shiyue|web',
    'charge_type'  => (int)0,
];

//判断是否有角色
$roleInfo = GameApi::call('Role')->getRoleByRid($rid, $platform, $zone_id);
API::log($roleInfo, 'sygame_pay_web', 'request');
if ($roleInfo['error'] != 'OK') {
    API::log(array('msg' => '无角色信息'), 'sygame_pay_web', 'request');
    exit('fail');
}

$payAccount = $p['name'];
$roleAccount = $roleInfo['data'][0]['account'];

//充值账号判断
if($payAccount != $roleAccount) {
    API::log('充值账号跟角色注册帐号不匹配,角色账号:'. $roleAccount .' 充值账号:'. $payAccount, 'sygame_pay_web', 'error');
    exit('fail');
}

//发货接口
$api = new PayApi($rid, $platform, $zone_id);
$ret = $api->pay($order_no, $amount, $ext);
if(SYGAMESDK::DEBUG) API::log($ret, 'sygame_pay_web', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
    case 'ORDER_EXISTS':
        exit('success');break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        exit('fail'); break;
}
