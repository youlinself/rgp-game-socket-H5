<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/8/16
 * Time: 16:26
 * 贪玩 游戏 充值接口
 */
require CURR_PLATFORM_DIR. './TanWanSDK.class.php';
//获取请求参数和访问日志记录
$p = API::param()->getParams();
if (TanWanSDK::DEBUG) API::log($p, 'tanwan_pay', 'request');
//参数验证
if(empty($p['ext'])) {
    API::log(array('msg' => '缺少ext参数内容'), 'tanwan_pay', 'request');
    TanWanSDK::payOut(2, '缺少必要参数');
}
//透传数据解析
$info = API::getExt($p['ext']);
//充值额外参数
$ext  = API::setPayExt($info);
//sign校验
if ($p['flag'] !== TanWanSDK::createPaySign($p, $info['cps'])) {
    API::log(array('msg' => $info['cps'] . '标签验证sign错误'), 'tanwan_pay', 'request');
    TanWanSDK::payOut(3, 'sign校验失败');
}
//组织充值数据
$amount    = (float)$p['money']; //成功充值金额
$order_no  = trim($p['orderid']);
//账户特殊处理，需要拼接
if(in_array($info['platform'], ['verifyios'])) {
    $acc = 'twgzsj_' . $p['uid'];
} else {
    $acc = 'tanwan_' . $p['uid'];
}
//充值账号验证
if(!API::checkPayAccount($info['rid'], $info['platform'], $info['zone_id'], $acc)) {
    API::log('充值账号跟角色注册帐号不匹配', 'tanwan_pay', 'error');
    TanWanSDK::payOut(4, '发货失败');
}

$api = new PayApi($info['rid'], $info['platform'], $info['zone_id']);
$ret = $api->pay($order_no, $amount, $ext);
if(TanWanSDK::DEBUG) API::log($ret, 'tanwan_pay', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
    case 'ORDER_EXISTS':
        TanWanSDK::payOut(1, '发货成功');
        break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        TanWanSDK::payOut(4, '发货失败');
        break;
}