<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/7/25
 * Time: 11:18
 * r2game 游戏 登录验证地址
 * 统一登录回调地址:http://s1-h5mlf-h5sszg.shiyuegame.com/api.php/pf/r2/login/
 */
require CURR_PLATFORM_DIR. './R2GameSDK.class.php';
//数据请求获取和记录
$p = API::param()->getParams();
if (R2Game::DEBUG) API::log($p, 'r2game_login', 'request');
//重新组织请求数据
$cps = $p['cps'];//游戏透传别名，用来区分不同游戏参数
if(empty($cps)) {
    API::log(['msg' => '缺少cps参数'], 'r2game_login', 'request');
    API::out(-1, 'Missing parameters');
}

//sign校验
if ($p['sign'] !== R2Game::createLoginSign($p, $cps)) {
    API::log(array('msg' => $cps . '标签验证sign错误'), 'r2game_login', 'request');
    API::out(-1, '验证失败');
} else {
    API::out(666, '验证成功');//返回json数据
}
