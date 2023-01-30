<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/7/25
 * Time: 11:01
 * R2games 游戏 充值回调接口
 * 统一回调地址:http://s1-mix-sszg.shiyuegame.com/api.php/pf/r2/callback/
 */
require CURR_PLATFORM_DIR. './R2GameSDK.class.php';
//获取请求数据和访问日志记录
$p = API::param()->getParams();
if (R2Game::DEBUG) API::log($p, 'r2game_callback', 'callback_request');
//解析数据
if(empty($p['item'])) {
    API::log(['msg' => '缺少item参数'], 'r2game_callback', 'callback_request');
    R2Game::payOut(1, '缺少必要参数');
}
//透传数据解析
$info = API::getExt($p['item']);
//数据转发
$host = API::getUrlHost($info['zone_id'], $info['platform']);

try {
    $ret = API::callRemoteApi("http://".$host."/api.php/pf/r2/pay/", $p);
    API::log($ret, 'r2game_callback', 'call_remote_api');
    echo $ret['msg'];exit;
} catch (Exception $e) {
    API::log($e->getMessage(), 'r2game_callback', 'call_error');
    TanWanSDK::payOut(5, '请求异常');
}