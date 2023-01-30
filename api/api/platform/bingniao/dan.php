<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/6/26
 * Time: 17:33
 * 冰鸟游戏 ios 充值接口
 */
require CURR_PLATFORM_DIR. './BingNiaoSDK.class.php';
//获取请求参数和访问日志记录
$p = API::param()->getParams();
if (BingNiaoSDK::DEBUG) API::log($p, 'bingniao_dan', 'request');
//参数验证
if(empty($p['extinfo'])) {
    API::log(array('msg' => '缺少extinfo参数内容'), 'bingniao_dan', 'request');
    BingNiaoSDK::out(1);
}
//透传数据解析
$info = API::getExt($p['extinfo']);
//充值额外参数
$ext  = API::setPayExt($info);
//支付状态
$status   = trim($p['orderStatus']);
if((int)$status !== 1) {
    API::log(array('msg' => $info['cps'] . '标签验证status状态不为1'), 'bingniao_dan', 'request');
    BingNiaoSDK::out(1);
}
//sign验证
if($p['sign'] !== BingNiaoSDK::createPaySign($p, $info['cps'])) {
    API::log(array('msg' => $info['cps'] . '标签验证sign错误'), 'bingniao_dan', 'request');
    BingNiaoSDK::out(1);
}

//组织充值数据
$amount   = (float)$p['money']/100; //成功充值金额，单位(分)
$order_no = trim($p['bfOrderId']);

//充值账号验证
//账户特殊处理，需要拼接
$acc = 'bingniao_' . $p['userId'];
if(!API::checkPayAccount($info['rid'], $info['platform'], $info['zone_id'], $acc)) {
    API::log('充值账号跟角色注册帐号不匹配', 'bingniao_dan', 'error');
    BingNiaoSDK::out(1);
}

$api = new PayApi($info['rid'], $info['platform'], $info['zone_id']);
$ret = $api->pay($order_no, $amount, $ext);
if(BingNiaoSDK::DEBUG) API::log($ret, 'bingniao_dan', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
    case 'ORDER_EXISTS':
        BingNiaoSDK::out(0);
        break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        BingNiaoSDK::out(1);
        break;
}