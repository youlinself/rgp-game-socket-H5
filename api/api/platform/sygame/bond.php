<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/9/16
 * Time: 20:53
 * 代金券通知接口
 */
require CURR_PLATFORM_DIR. './SYGAMESDK.class.php';
//获取请求才和日志记录
$p = API::param()->getParams();
if(SYGAMESDK::DEBUG) API::log($p, 'sygame_bond', 'request');
if($p['sign'] !== SYGAMESDK::createBondSign($p)) SYGAMESDK::outSy(-1, 'sign验证失败');

$type  = (int)$p['type']; //是否通知全服玩家 1 个人 2 全服
//奖品码，唯一
$roleIds = [];
//全服邮件判断
if($type === 1) {
    $roleId = (int)$p['rid'];
    $platform = $p['platform'];
    $zoneId = $p['zone_id'];
    $roleIds = [(int)$roleId, $platform, (int)$zoneId];
}
//通知服务端
$ret = GameApi::call('GM')->bond($roleIds, $type);
if(SYGAMESDK::DEBUG) API::log($ret, 'sygame_bond', 'gift_ret');
if ($ret['success']) {
    SYGAMESDK::outSy(0, '成功');
} else {
    SYGAMESDK::outSy(-2, '通知失败');
}
