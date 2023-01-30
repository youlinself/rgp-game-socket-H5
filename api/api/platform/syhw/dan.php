<?php
/**
 * User: weiming Email: 329403630@qq.com
 * Date: 2016/9/12
 * Time: 20:56
 * 我方 SDK 充值接口
 */

require CURR_PLATFORM_DIR. './SYHWSDK.class.php';
$p = API::param()->getParams();
if (SYHWSDK::DEBUG) API::log($p, 'syhw_pay', 'request');
//解析数据
if(empty($p['exdata'])) {
    API::log(array('msg' => '缺少exdata参数内容'), 'syhw_pay', 'request');
    exit('fail');
}
//透传数据解析
$info = API::getExt($p['exdata']);
//充值额外参数
$ext  = API::setPayExt($info);
//sign校验
if ($p['sign'] !== SYHWSDK::createPaySign($p, $info['cps'])) {
    API::log(array('msg' => $info['cps'] . '标签验证sign错误'), 'syhw_pay', 'request');
    exit('error sign');
}

$amount    = (float)$p['amount']; //成功充值金额
$order_no  = trim($p['order_id']);
$ext['pay_channel'] = empty($p['paywith']) ? '未知渠道' : $p['paywith'];

//判断是否为自充值账号
$roleInfo = GameApi::call('Role')->getRoleByRid($info['rid'], $info['platform'], $info['zone_id']);
if ($roleInfo['error'] != 'OK') exit('查询角色失败');
$account = $roleInfo['data'][0]['account'];
if (GameApi::call('Role')->isChargeAccount($account)) {
    $category = PayApi::SELF;
} else {
    $category = PayApi::NORMAL;
}

$acc = SYHWSDK::setPayAcc($info['platform'], $p['account_id']);
//充值账号验证
if(!API::checkPayAccount($info['rid'], $info['platform'], $info['zone_id'], $acc)) {
    API::log('充值账号跟角色注册帐号不匹配', 'syhw_pay', 'error');
    exit('fail');
}

$currency = $p['currency_type'];
if(in_array($p['paywith'], ['sandbox'])) $category = PayApi::TEST;

//发货接口
$api = new PayApi($info['rid'], $info['platform'], $info['zone_id']);
$ret = $api->pay($order_no, $amount, $ext, $category, $currency);
if(SYHWSDK::DEBUG) API::log($ret, 'syhw_pay', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
    case 'ORDER_EXISTS':
        exit('success');break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        exit('fail'); break;
}
