<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/6/4
 * Time: 14:11
 * 9377 游戏 充值接口
 */
require CURR_PLATFORM_DIR. './SDK9377.class.php';
//获取请求参数和访问日志记录
$p = API::param()->getParams();
if (SDK9377::DEBUG) API::log($p, '9377_pay', 'request');
//参数验证
if(empty($p['extra_info'])) {
    API::log(array('msg' => '缺少extra_info参数内容'), '9377_pay', 'request');
    SDK9377::out(-1, '参数校验失败');
}
//透传数据解析
$info = API::getExt($p['extra_info']);
//充值额外参数
$ext  = API::setPayExt($info);
//sign校验
if ($p['sign'] !== SDK9377::createPaySign($p, $info['cps']) && $p['sign'] !== SDK9377::createPaySign($p, '9377_ios')) {
    API::log(array('msg' => $info['cps'] . '标签验证sign错误'), '9377_pay', 'request');
    SDK9377::out(-1, 'sign校验失败');
}

//订单沙箱状态判断
$sandbox = (int)$p['sandbox'];
if(!isset($p['sandbox'])) {
    API::log(array('msg' => '缺少必要参数sandbox'), '9377_pay', 'request');
    SDK9377::out(-1, '缺少参数sandbox');
}
if($sandbox === 1 && !in_array($info['platform'], ['verifyios'])) {
    API::log(array('msg' => '沙箱订单状态为1,指定限制充值平台verifyios'), '9377_pay', 'request');
    SDK9377::out(-1, '充值失败');
}

//组织充值数据
$amount  = (float)$p['money'];
$order_no  = trim($p['order_id']);
//充值额外账号处理
if(!empty($p['change_uid'])) $p['username'] = $p['change_uid'];
$acc = '9377_' . $p['username'];


//充值账号验证
if(!API::checkPayAccount($info['rid'], $info['platform'], $info['zone_id'], $acc)) {
    API::log('充值账号跟角色注册帐号不匹配', '9377_pay', 'error');
    SDK9377::out(-1, '参数校验失败');
}

$api = new PayApi($info['rid'], $info['platform'], $info['zone_id']);
$ret = $api->pay($order_no, $amount, $ext);
if(SDK9377::DEBUG) API::log($ret, '9377_pay', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
    case 'ORDER_EXISTS':
        SDK9377::out(1, '发货成功');
        break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        SDK9377::out(-1, '发货失败');
        break;
}