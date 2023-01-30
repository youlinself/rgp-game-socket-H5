<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/1/15
 * Time: 17:00
 * eyou 游戏 充值回调接口
 * 充值接口一统一回调地址:https://s1-kokr-sszg.shiyuegame.com/api.php/pf/eyou/callback/?call_act=pay
 * 充值接口二回调地址:http://s1-kokr-sszg.shiyuegame.com/api.php/pf/eyou/callback/?call_act=pay_web
 * 礼包卡号回调地址:https://s1-kokr-sszg.shiyuegame.com/api.php/pf/eyou/callback/?call_act=gift
 * 卡号兑换回调地址:https://s1-kokr-sszg.shiyuegame.com/api.php/pf/eyou/callback/?call_act=card
 * 玩家时长回调地址:https://s1-kokr-sszg.shiyuegame.com/api.php/pf/eyou/callback/?call_act=online
 * 获取角色信息回调地址:https://s1-kokr-sszg.shiyuegame.com/api.php/pf/eyou/callback/?call_act=role_info
 * 获取角色等级回调地址:https://s1-kokr-sszg.shiyuegame.com/api.php/pf/eyou/callback/?call_act=role_level
 */
require CURR_PLATFORM_DIR.'EYouSDK.class.php';
//获取请求参数和日志记录
$p = API::param()->getParams();
if(EYouSDK::DEBUG) API::log($p, 'eyou_pay', 'callback_request');
if(!$p['call_act']) API::out(-1, '缺少call_act参数');
//获取服务器id
$act = $p['call_act'];
$zone_id = $platform = '';
switch($act)
{
    case 'pay':
        $sdk = new EYouSDK();
        if(!$p['Orderinfo']) $sdk->out(['Success'=>0,'Reason'=>'缺少参数']);
        $params = $sdk->getPayParams($p['Orderinfo']);
        if(EYouSDK::DEBUG) API::log($params, 'eyou_pay', 'request');
        $info = API::getExt(base64_decode(urldecode($params['ctext'])));//透传参数
        $zone_id = (int)$info['zone_id'];
        $game_id = (int)$params['gameid'];
        $platform = $info['platform'];
        break;
    case 'pay_web':
        $game_id = (int)$p['game_id'];
        list($platform, $zone_id) = explode('_', $p['game_server_id']);
        break;
    case 'gift':
    case 'role_info':
        list($platform, $zone_id) = explode('_', $p['s_id']);
        $game_id = (int)$p['gameid'];
        break;
    case 'card':
    case 'online':
    case 'role_level':
        list($platform, $zone_id) = explode('_', $p['serverid']);
        $game_id = (int)$p['gameid'];
        break;
    default: API::out(-1, '错误act');exit;break;
}
$isExtHost = false;

if(in_array($game_id, EYouSDK::$game_id['kokr'])) {
    switch ($zone_id)
    {
        case ($zone_id >= 10001 && $zone_id <= 19999):
            $platform = 'verifyios';
            break;
        case ($zone_id >= 20001 && $zone_id <= 29999):
            $platform = 'kokrtest';
            break;
        default:
            $isExtHost = true;
            $platform = 'kokr';
            break;
    }
} elseif(in_array($game_id, EYouSDK::$game_id['eyouen'])) {
    switch ($zone_id)
    {
        case ($zone_id >= 10001 && $zone_id <= 19999):
            $platform = 'verifyios';
            break;
        case ($zone_id >= 20001 && $zone_id <= 29999):
            $platform = 'eyouentest';
            break;
        default:
            if(!in_array($platform, ['eyouentest'])) {
                $isExtHost = true;
                if(empty($platform)) $platform = 'eyouen';
                break;
            }
    }
}

elseif(in_array($game_id, EYouSDK::$game_id['tw'])) {

    switch ($zone_id)
    {
        case ($zone_id >= 10001 && $zone_id <= 19999):
            $platform = 'verifyios';
            break;
        case ($zone_id >= 20001 && $zone_id <= 29999):
            $platform = 'twtest';
            break;
        default:
            if(!in_array($platform, ['twtest'])) {
                $isExtHost = true;
                $platform = 'tw';
                break;
            }
    }
}

elseif(in_array($game_id, EYouSDK::$game_id['taiguo'])) {
    switch ($zone_id)
    {
        case ($zone_id >= 10001 && $zone_id <= 19999):
            $platform = 'verifyios';
            break;
        case ($zone_id >= 20001 && $zone_id <= 29999):
            $platform = 'taiguotest';
            break;
        default:
            $isExtHost = true;
            $platform = 'taiguo';
            break;
    }
}

elseif(in_array($game_id, EYouSDK::$game_id['idn'])) {

    if(in_array($platform, ['idn'])) {
        $isExtHost = true;
    }
}


else {
    API::out(-1, '错误game_id');
}

if($isExtHost) {
    $sdk = new EYouSDK();
    $host = $sdk->getUrlHost($zone_id, $platform);
} else {
    $host = API::getUrlHost($zone_id, $platform);
}

if(isset($p['call_act'])) unset($p['call_act']);
try {
    $ret = API::callRemoteApi("http://".$host."/api.php/pf/eyou/{$act}/", $p);
    API::log($ret, 'eyou_pay', 'call_remote_api');
    echo $ret['msg'];exit;
} catch (Exception $e) {
    API::log($e->getMessage(), 'eyou_pay', 'callback_request');
    exit('callback error：'.$e->getMessage());
}