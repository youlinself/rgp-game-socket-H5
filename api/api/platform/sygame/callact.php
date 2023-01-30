<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/5/21
 * Time: 16:27
 * 诗悦官网平台 统一回调接口
 * 文档地址:http://developer.shiyuegame.com/showdoc/web/#/6?page_id=117
 * web网页充值统一回调接口:http://s1-symlf-sszg.shiyuegame.com/api.php/pf/sygame/callact?call_act=pay_web
 * 角色信息查询统一回调接口:http://s1-symlf-sszg.shiyuegame.com/api.php/pf/sygame/callact?call_act=role_info
 *
 */
require CURR_PLATFORM_DIR. './SYGAMESDK.class.php';
//请求参数获取
$p = stripQuotes(API::param()->getParams());
if (SYGAMESDK::DEBUG) API::log($p, 'sy_callact', 'callback_request');
if(!$p['call_act']) SYGAMESDK::outSy(-1, '缺少call_act参数');
//获取服务器id
$act = $p['call_act'];
switch($act)
{
    case 'pay_web':
    case 'role_info':
        $srv_id = empty($p['server_id']) ? $p['srv_id'] : $p['server_id'];
        $exp = explode('_', $srv_id);
        $zone_id = (int)$exp[1];
        $platform = $exp[0];
        break;

    default: SYGAMESDK::outSy(-2, '错误act');exit;break;
}

$host = API::getUrlHost($zone_id, $platform);
if(isset($p['call_act'])) unset($p['call_act']);
try {
    $ret = API::callRemoteApi("http://".$host."/api.php/pf/sygame/{$act}", $p);
    if(!$ret['result']) API::log($ret['msg'], 'sy_callact', 'call_remote_api');
    echo $ret['msg'];exit;
} catch (Exception $e) {
    API::log($e->getMessage(), 'sy_callact', 'call_remote_api');
    SYGAMESDK::outSy(-3, $e->getMessage());
}