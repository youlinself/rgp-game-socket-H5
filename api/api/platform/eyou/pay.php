<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/1/15
 * Time: 17:01
 * eyou 游戏 充值推送接口一
 * 文档地址:http://developer.eyougame.com/php/sdk.html#pay1
 */
require CURR_PLATFORM_DIR.'EYouSDK.class.php';
//获取请求数据和日志记录
$req = API::param()->getParams();
if(EYouSDK::DEBUG) API::log($req, 'eyou_pay', 'request');
$sdk = new EYouSDK();
$p = $sdk->getPayParams($req['Orderinfo']);
if(EYouSDK::DEBUG) API::log($p, 'eyou_pay', 'request');
//成功充值金额
$amount   = (float)$p['amount'];
$order_no = trim($p['orderId']);
$status   = (int)$p['success']; //订单支付状态 1成功 0 失败
if($status !== 1) $sdk->out(['Success' => 0,'Reason' => '订单支付状态错误']);
//透传数据解析
$info = API::getExt(base64_decode(urldecode($p['ctext'])));//透传参数
//充值额外参数
$ext = API::setPayExt($info);
//货币类型
$currency = $p['currency'];
$gameId = $req['gameid'];
$tax = $p['tax'];

$acc = $sdk->setPayAcc($gameId, $p['sdkuid'], $info['platform']);
API::log('充值账号:'.$acc, 'eyou_pay', 'request');
//充值账号验证
if(!API::checkPayAccount($info['rid'], $info['platform'], $info['zone_id'], $acc)) {
    API::log('充值账号跟角色注册帐号不匹配', 'eyou_pay', 'error');
    $sdk->out(['Success' => 0, 'Reason' => '充值账号异常']);
}

$check = $sdk->checkTaxGold($gameId, $ext['package_id'], $tax);
$ext['package_id'] = $check['package_id'];

$category = PayApi::NORMAL;
if((int)$p['is_sandbox'] === 1) $category = PayApi::TEST;

//充值发货
$api = new PayApi($info['rid'], $info['platform'], $info['zone_id']);
$ret = $api->pay($order_no, $amount, $ext, $category, $currency);
if(EYouSDK::DEBUG) API::log($ret, 'eyou_pay', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
    case 'ORDER_EXISTS':
        $sdk->out(['Success' => 1, 'Reason' => '']);
        break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        $sdk->out(['Success' => 0, 'Reason' => $ret['msg']]);
        break;
}