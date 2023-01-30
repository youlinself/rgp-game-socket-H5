<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2020/3/2
 * Time: 14:21
 * funplus 游戏 充值回调
 * 充值回调地址:http://s1-jp-sszg.shiyuegame.com/api.php/pf/funplus/callback
 */
require CURR_PLATFORM_DIR. './FunplusSDK.class.php';

//去掉自动转义(json格式)
$p = stripQuotes(API::param()->getParams());
if(Funplus::DEBUG) API::log($p, 'funplus_callback', 'callback_request');
$data = json_decode($p['app_data'], true);
//参数验证
if(empty($data['through_cargo'])) {
    API::log(array('msg' => '缺少extra_info参数内容'), 'funplus_callback', 'request');
    Funplus::payOut('ERROR', 'SPECIFIC REASON');
}
//透传数据解析
$info = API::getExt($data['through_cargo']);
//组织回调域名
$host = API::getUrlHost($info['zone_id'], $info['platform']);
try {
    $ret = API::callRemoteApi("http://".$host."/api.php/pf/funplus/pay/", $p, 2);
    API::log($ret, 'funplus_callback', 'call_remote_api');
    echo $ret['msg'];exit;
} catch (Exception $e) {
    API::log($e->getMessage(), 'funplus_callback', 'call_error');
    Funplus::payOut('ERROR', 'SPECIFIC REASON');
}