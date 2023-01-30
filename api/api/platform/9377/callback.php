<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/8/16
 * Time: 16:27
 * 93877 游戏 充值回调接口
 * 统一回调地址:http://s1-9377-sszg.shiyuegame.com/api.php/pf/9377/callback/
 */
require CURR_PLATFORM_DIR. './SDK9377.class.php';
//获取请求数据和访问日志记录
$p = API::param()->getParams();
if (SDK9377::DEBUG) API::log($p, '9377_callback', 'callback_request');
//解析数据
if(empty($p['extra_info'])) {
    API::log(['msg' => '缺少extra_info参数'], '9377_callback', 'callback_request');
    SDK9377::out(-1, '参数校验失败');
}
//透传数据解析
$info = API::getExt($p['extra_info']);
//数据转发
$host = API::getUrlHost($info['zone_id'], $info['platform']);

try {
    //额外特殊判断充值账号
    $accFile = VAR_DIR.'/acc/acc_9377_mapping.php';
    if(is_file($accFile)) {
        $accInfo = require $accFile;
        if(!empty($accInfo) && isset($accInfo[$p['username']])) $p['change_uid'] = $accInfo[$p['username']];
    }

    $ret = API::callRemoteApi("http://".$host."/api.php/pf/9377/pay/", $p);
    API::log($ret, '9377_callback', 'call_remote_api');
    echo $ret['msg'];exit;
} catch (Exception $e) {
    API::log($e->getMessage(), '9377_callback', 'call_error');
    SDK9377::out(-2, '请求异常');
}