<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/12/13
 * Time: 18:24
 * 嘉年华奖励发放接口
 */
require CURR_PLATFORM_DIR.'SYSDK.class.php';
$p = API::param()->getParams();
if(SYSDK::DEBUG) API::log($p, 'carnival_share', 'request');

//参数验证
if(empty($p['sign']) || empty($p['role_id']) || empty($p['platform']) || empty($p['zone_id']) || empty($p['ts'])) {
    API::log(array('msg' => '参数缺少'), 'carnival_share', 'request');
    API::out(-3, '参数错误');
}
//sign校验
if ($p['sign'] !== SYSDK::createWeeklySign($p, SYSDK::CARNIVAL_KEY)) {
    API::log(array('msg' => '验证sign错误'), 'carnival_share', 'request');
    API::out(-2, 'sign验证错误');
}

//获取角色周报信息
$res = GameApi::call('GM')->carnivalShare($p['role_id'], $p['platform'], $p['zone_id']);
API::log($res, 'carnival_share', 'ret');
if ($res['success']) {
    API::out(666, $res['message']);
} else {
    API::out(-1, $res['message']);
}