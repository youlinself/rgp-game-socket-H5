<?php
/**
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/6/6
 * Time: 15:00
 * oppo游戏 礼包接口
 */

require CURR_PLATFORM_DIR.'OPPOSDK.class.php';
//获取请求才和日志记录
$p = API::param()->getParams();
if(OPPOSDK::DEBUG) API::log($p, 'oppo_gift', 'request');

if (!OPPOSDK::verify($p['data'], $p['sign'])) {
    API::log('sign验证错误', 'oppo_gift', 'request');
    OPPOSDK::giftOut(40003,"sign error");
}

$p = OPPOSDK::decryptData($p['data']);
API::log($p, 'oppo_gift', 'request');
if (empty($p)) {
    API::log('data数据解密失败', 'oppo_gift', 'request');
    OPPOSDK::giftOut(40002, "params error");
}

$list = explode('_', $p['realmId']);
$zone_id = (int)$list[1];
$platform = $list[0];
$account_id = $p['accountId'];
$card_no = $p['giftId'];
$role_id = $p['roleId'];
//角色信息查询
$rows = GameApi::call('Role')->getRoleByRid($role_id, $platform, $zone_id);
API::log($rows, 'oppo_gift', 'request');
$role = $rows['data'][0];
if(empty($role)) {
    API::log('角色数据为空', 'oppo_gift', 'ret');
    OPPOSDK::giftOut(50000, 'server error');
}

//充值账号验证
if(!API::checkPayAccount($role['rid'], $role['platform'], $role['zone_id'], $account_id)) {
    API::log('充值账号跟角色注册帐号不匹配', '9377_pay', 'error');
    SDK9377::out(-1, '参数校验失败');
}

$giftConf = OPPOSDK::getConf();
if (empty($giftConf[$card_no])){
    API::log($giftConf, 'oppo_gift', 'ret');
    OPPOSDK::giftOut(50000, "server error");
}
$giftConf = $giftConf[$card_no];

if(OPPOSDK::DEBUG) API::log($giftConf, 'oppo_gift', 'report_ret');

$roles = [(int)$role['rid'], $platform, (int)$zone_id];
$res = GameApi::call('GM')->gifts($roles, $giftConf['card_id'], $card_no, "礼包奖励", "尊敬的冒险者大人，以下是您的礼包奖励，请查收，祝您游戏愉快！", $giftConf['items']);

if ($res['error'] !== 'OK') {
    API::log($res, 'oppo_gift', 'ret');

    switch ($res['message']) {
        case '不能重复领取奖励':
            OPPOSDK::giftOut(20001, "server error");
            break;
        default:
            OPPOSDK::giftOut(50000, "server error");
    }

} else {
    API::log($res, 'oppo_gift', 'ret');
    OPPOSDK::giftOut(20000, "OK");
}





