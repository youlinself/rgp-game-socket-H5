<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2020/3/2
 * Time: 16:13
 * funplus 登录验证接口
 * http://s1-jp-sszg.shiyuegame.com/api.php/pf/funplus/login
 */

require CURR_PLATFORM_DIR. './FunplusSDK.class.php';
//获取请求数据
//去掉自动转义(json格式)
$p = stripQuotes(API::param()->getParams());
//日志记录
if(Funplus::DEBUG) API::log($p, 'funplus_login', 'request');
if(empty($p['cps'])) {
    API::log(['msg' => '缺少cps参数'], 'funplus_login', 'request');
    API::out(-2, '缺少参数');//返回json数据
}
//重新组织请求数据
$cps = $p['cps'];//游戏透传别名，用来区分不同游戏参数
$report = array(
    'method' => 'check_session',
    'session_key' =>  urldecode($p['session_key'])
);
//环境判断
$isTest = isset($p['status']) ? $p['status'] : 1;
if((int)$isTest === 1) {
    $url = Funplus::LOGIN_URL;
}else {
    $url = Funplus::TEST_LOGIN_URL;
}

//登录请求地址
$ret = API::callRemoteApi($url, $report, 2);
if(Funplus::DEBUG) API::log($ret, 'funplus_login', 'report_ret');
//返回数据解析，并且返回给客户端
if(!$ret['result']){
    exit('error:'.$ret['msg']);
} else {
    $code = json_decode($ret['msg'], true);
    if((int)$code['status'] === 1) {
        $time = time();
        $retData = [
            'fpid' => $code['data']['fpid'],
            'ctime' => $time,
            'expire_in' => $code['data']['expire_in'],
            'flag' => md5($code['data']['fpid'] . $time . Funplus::RE_LOGIN_KEY)
        ];
        API::log($retData, 'funplus_login', 'request');
        API::out(666, $retData);//返回json数据
    } else {
        API::log($report, 'funplus_login_error', 'request');
        API::out(-1, $code['error']);//返回json数据
    }
}