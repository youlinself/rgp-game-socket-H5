<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2020/3/2
 * Time: 14:21
 */
require CURR_PLATFORM_DIR. './FunplusSDK.class.php';
//获取请求参数和访问日志记录
$p = stripQuotes(API::param()->getParams());
if (Funplus::DEBUG) API::log($p, 'funplus_pay', 'request');

$data = json_decode($p['app_data'], true);
//参数验证
if(empty($data['through_cargo'])) {
    API::log(array('msg' => '缺少through_cargo参数内容'), 'funplus_pay', 'request');
    Funplus::payOut('ERROR', 'SPECIFIC REASON');
}

if((int)$p['status'] !== 1) {
    API::log(array('msg' => '订单状态不为1'), 'funplus_pay', 'request');
    Funplus::payOut('ERROR', 'SPECIFIC REASON');
}

//透传数据解析
$info = API::getExt($data['through_cargo']);
//充值额外参数
$ext  = API::setPayExt($info);
//sign校验
if ($p['token_all'] !== Funplus::createPaySign($p, $info['cps'])) {
    API::log(array('msg' => $info['cps'] . '标签验证token_all错误'), 'funplus_pay', 'request');
    Funplus::payOut('ERROR', 'SPECIFIC REASON');
}
//组织充值数据
$amount    = (float)$p['rmoney']; //成功充值金额
$order_no  = trim($p['tid']);
$acc = 'jp_'.$p['uid'];
//充值账号验证
if(!API::checkPayAccount($info['rid'], $info['platform'], $info['zone_id'], $acc)) {
    API::log('充值账号跟角色注册帐号不匹配', 'funplus_pay', 'error');
    Funplus::payOut('ERROR', 'SPECIFIC REASON');
}
$currency = $p['currency'];

$api = new PayApi($info['rid'], $info['platform'], $info['zone_id']);
$ret = $api->pay($order_no, $amount, $ext, PayApi::NORMAL, $currency);
if(Funplus::DEBUG) API::log($ret, 'funplus_pay', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
        Funplus::payOut('OK', 'no reason');
        break;
    case 'ORDER_EXISTS':
        Funplus::payOut('OK', 'repeat');
        break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        Funplus::payOut('ERROR', 'SPECIFIC REASON');
        break;
}