<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/7/24
 * Time: 19:39
 * 诗悦 融合SDK 充值回调 充值 接口
 * 接口地址: api.php/pf/sy/pay
 * 文档地址:http://developer.shiyuegame.com/showdoc/web/#/1?page_id=4
 */
require CURR_PLATFORM_DIR.'SYSDK.class.php';
//获取请求数据和日志记录
$p = file_get_contents("php://input");
$p = json_decode($p, true);
if(SYSDK::DEBUG) API::log($p, 'sy_pay', 'request');
if(empty($p['state']) || empty($p['data'])) {
    API::log(['msg' => 'state,data数据为空'], 'sy_pay', 'request');
    exit('FAIL');
}
//订单支付状态 1成功 其它 失败
if((int)$p['state'] !== 1) {
    API::log(['msg' => '订单状态不为1'], 'sy_pay', 'request');
    exit('FAIL');
}
//充值sign判断
if(!SYSDK::verifyPaySign($p['data'])) {
    API::log(['msg' => '充值验证sign失败'], 'sy_pay', 'request');
    exit('FAIL');
}
//透传数据解析
$info = API::getExt($p['data']['extension']);
//充值额外参数
$ext  = API::setPayExt($info);
//货币类型
$currency = $p['data']['currency'];
$category  = PayApi::NORMAL;
//成功充值金额
$amount   = (float)$p['data']['money']/100;
$order_no = trim($p['data']['orderID']);
$channelID = trim($p['data']['channelID']);

//充值账号验证
if(!API::checkPayAccount($info['rid'], $info['platform'], $info['zone_id'], $p['data']['userID'])
    && !in_array($channelID, [16, 47, 72])) {
    API::log('充值账号跟角色注册帐号不匹配', 'sy_pay', 'error');
    exit('FAIL角色账号认证错误');
}

//充值发货
$api = new PayApi($info['rid'], $info['platform'], $info['zone_id']);
$ret = $api->pay($order_no, $amount, $ext, $category, $currency);
if(SYSDK::DEBUG) API::log($ret, 'sy_pay', 'pay_ret');
switch ($ret['error']) {
    case 'ORDER_EXISTS':
    case 'SUCCESS':
        exit('SUCCESS');
        break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        exit('FAIL');
        break;
}