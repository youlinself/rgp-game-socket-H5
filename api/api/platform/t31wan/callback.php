<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/8/16
 * Time: 16:27
 * 贪玩 游戏 充值回调接口
 * 统一回调地址:http://s1-mix-sszg.shiyuegame.com/api.php/pf/t31wan/callback/
 */
require CURR_PLATFORM_DIR. './TanWanSDK.class.php';
//获取请求数据和访问日志记录
$p = API::param()->getParams();
if (TanWanSDK::DEBUG) API::log($p, 'tanwan_callback', 'callback_request');
//解析数据
if(empty($p['ext'])) {
    API::log(['msg' => '缺少ext参数'], 'tanwan_callback', 'callback_request');
    TanWanSDK::payOut(2, '缺少必要参数');
}
//透传数据解析
$info = API::getExt($p['ext']);
//数据转发
$host = API::getUrlHost($info['zone_id'], $info['platform']);

try {
    $ret = API::callRemoteApi("http://".$host."/api.php/pf/t31wan/pay/", $p);
    API::log($ret, 'tanwan_callback', 'call_remote_api');
    echo $ret['msg'];exit;
} catch (Exception $e) {
    API::log($e->getMessage(), 'tanwan_callback', 'call_error');
    TanWanSDK::payOut(5, '请求异常');
}