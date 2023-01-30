<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/3/8
 * Time: 14:58
 * eYou 游戏 玩家等级充值累积获取接口
 */
require CURR_PLATFORM_DIR.'EYouSDK.class.php';
//获取请求参数和日志记录
$p = API::param()->getParams();
$sdk = new EYouSDK();
if(EYouSDK::DEBUG) API::log($p, 'eyou_online', 'request');
//sign验证
if($p['sign'] !== $sdk->createSign($p, $p['gameid'])) out(0, 'sign验证失败', 0, 0, '');
$game_id  = (int)$p['gameid'];
$role_id  = (int)$p['roleid'];
$uid      = $p['uid'];
list($platform, $zone_id) = explode('_', $p['serverid']);
$srv_id = $p['serverid'];
$api = GameApi::call('Role');
$api->queryField = 'lev, account';
$ret = $api->getRoleByRid($role_id, $platform, (int)$zone_id);

if(EYouSDK::DEBUG) API::log($ret['data'], 'eyou_online', 'ret');

if(empty($ret['data'][0])) out(0, '没有该角色信息', 0, 0, '');

$acc = $sdk->setPayAcc($game_id, $uid, $platform);

$role_uid =  $ret['data'][0]['account'];
if($acc != $role_uid) out(0, '帐号验证失败', 0, 0, '');

$charge = Db::getInstance()->getRow("select sum(money) money,currency_type from mod_charge where rid={$role_id} and srv_id ='{$srv_id}'  GROUP by currency_type");

$money = $charge['money'];
$currency_type =  $charge['currency_type'];

if (empty($money)) $money = 0;
if (empty($currency_type)) $currency_type = '';

out(1, '获取成功', $ret['data'][0]['lev'], $money, $currency_type);

//信息格式返回
function out($code, $msg, $lev, $money, $currency)
{
    global $sdk;
    $sdk->role_out($code, $msg, $lev, $money, $currency);
}