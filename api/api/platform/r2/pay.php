<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/7/25
 * Time: 11:01
 * r2games 游戏 充值接口
 */
require CURR_PLATFORM_DIR. './R2GameSDK.class.php';
//获取请求参数和访问日志记录
$p = API::param()->getParams();
if (R2Game::DEBUG) API::log($p, 'r2game_pay', 'request');
//参数验证
if(empty($p['item'])) {
    API::log(array('msg' => '缺少ext参数内容'), 'r2game_pay', 'request');
    R2Game::payOut(1, '缺少必要参数');
}
//透传数据解析
$info = API::getExt($p['item']);
//充值额外参数
$ext  = API::setPayExt($info);
//sign校验
if ($p['sign'] !== R2Game::createPaySign($p, $info['cps'])) {
    API::log(array('msg' => $info['cps'] . '标签验证sign错误'), 'r2game_pay', 'request');
    R2Game::payOut(6, '错误的sign');
}
//组织充值数据
$amount    = (float)$p['money']; //成功充值金额
$order_no  = trim($p['orderid']);
//账户特殊处理，需要拼接
$acc = 'syen_' . $p['username'];
//充值账号验证
if(!API::checkPayAccount($info['rid'], $info['platform'], $info['zone_id'], $acc)) {
    API::log('充值账号跟角色注册帐号不匹配', 'r2game_pay', 'error');
    R2Game::payOut(2, '用户不存在');
}
//货币类型
$currency = $p['currency'];
$api = new PayApi($info['rid'], $info['platform'], $info['zone_id']);
$ret = $api->pay($order_no, $amount, $ext, 1, $currency);
if(R2Game::DEBUG) API::log($ret, 'r2game_pay', 'pay_ret');
switch ($ret['error']) {
    case 'SUCCESS':
        R2Game::payOut(0, '成功');
        break;
    case 'ORDER_EXISTS':
        R2Game::payOut(5, '订单号已存在');
        break;
    case 'ORDER_HANDLE_FAILURE':
    default:
        R2Game::payOut(8, '发放物品失败');
        break;
}