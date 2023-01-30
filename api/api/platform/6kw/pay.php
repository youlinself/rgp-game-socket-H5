<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/6/4
 * Time: 14:34
 * 6kw 游戏 充值接口
 */

require CURR_PLATFORM_DIR. './SDK6KW.class.php';
//获取请求参数和访问日志记录
$p = API::param()->getParams();
if (SDK6KW::DEBUG) API::log($p, '6kw_pay', 'request');
//参数验证
if(empty($p['extension'])) {
    API::log(array('msg' => '缺少extra_info参数内容'), '6kw_pay', 'request');
    exit('FAIL');
}
//透传数据解析
$info = API::getExt($p['extension']);
//充值额外参数
$ext  = API::setPayExt($info);
//sign校验
if ($p['sign'] !== SDK6KW::createPaySign($p, $info['cps'])) {
    API::log(array('msg' => $info['cps'] . '标签验证sign错误'), '6kw_pay', 'request');
    exit('FAIL');
}
//组织充值数据
$amount    = (float)$p['total']/100; //成功充值金额
$order_no  = trim($p['orderID']);
$acc = '6kw_'.$p['uid'];
//充值账号验证
if(!API::checkPayAccount($info['rid'], $info['platform'], $info['zone_id'], $acc)) {
    API::log('充值账号跟角色注册帐号不匹配', '6kw_pay', 'error');
    exit('FAIL');
}

$api = new PayApi($info['rid'], $info['platform'], $info['zone_id']);
$ret = $api->pay($order_no, $amount, $ext);
if(SDK6KW::DEBUG) API::log($ret, '6kw_pay', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
    case 'ORDER_EXISTS':
        exit('SUCCESS');
        break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        exit('FAIL');
        break;
}