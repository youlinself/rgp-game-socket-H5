<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/8/12
 * Time: 15:23
 * 文档地址:http://market-doc.shiyuegame.com/web/?#/10?page_id=110 shichang
 * web网页充值统一回调接口:http://s1-xinma-sszg.shiyuegame.com/api.php/pf/sy/callback?call_act=pay_web
 */

require CURR_PLATFORM_DIR. './SYHWSDK.class.php';
//请求参数获取
$p = stripQuotes(API::param()->getParams());
if (SYHWSDK::DEBUG) API::log($p, 'syhw_callback', 'callback_request');
if(!$p['call_act']) exit('fail');
//获取服务器id
$act = $p['call_act'];
switch($act)
{
    case 'pay_web':
        $exp = explode('_', $p['srv_id']);
        $zone_id = (int)$exp[1];
        $platform = $exp[0];
        break;

    default: exit('fail'); break;
}

$host = API::getUrlHost($zone_id, $platform);
if(isset($p['call_act'])) unset($p['call_act']);
try {
    $ret = API::callRemoteApi("http://".$host."/api.php/pf/syhw/{$act}", $p);
    if(!$ret['result']) API::log($ret['msg'], 'syhw_callback', 'call_remote_api');
    echo $ret['msg'];exit;
} catch (Exception $e) {
    API::log($e->getMessage(), 'syhw_callback', 'call_remote_api');
    exit('fail');
}