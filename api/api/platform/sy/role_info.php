<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/9/10
 * Time: 16:04
 * 诗悦 游戏 获取角色信息接口
 */
require CURR_PLATFORM_DIR.'SYSDK.class.php';
//获取请求参数和日志记录
$p = API::param()->getParams();
if (SYSDK::DEBUG) API::log($p, 'sy_role_info', 'request');
//参数验证
$field = ['role_id', 'platform', 'zone_id', 'time', 'sign'];
foreach ($field as $k => $v) {
    if(empty($p[$v])) API::out(-1, '参数错误');
}
//sign校验
if ($p['sign'] !== SYSDK::createGameSign($p)) {
    API::log('签名验证错误', 'sy_role_info', 'request');
    API::out(-2, '签名错误');
}
//判断玩家是否已充值过
$srvId = $p['platform'] .'_'. $p['zone_id'];
$money = Db::getInstance()->getOne("select sum(money) money from mod_charge where category = 1 and rid = {$p['role_id']} and srv_id = '{$srvId}' ");
if($money) {
    API::log('玩家充值金额:'. $money, 'sy_role_info', 'request');
    API::out(1, $money);
} else {
    API::log('玩家未充值:'. $money, 'sy_role_info', 'request');
    API::out(1, -1);
}




