<?php
/**
 *  平台接口入口文件
 */

//初始化当前平台环境
require '_init.php';

DEBUG && recoder(API::param()->getParams());
/**
 * 映射到对应的接口文件
 * api.php/pf/4399/pay/?a=1&b=3     => api/api/platform/4399/pay.php?a=1&b=3  对应平台接口
 * api.php/local/local/pay/?&z=3 => api/api/local/pay.php?z=3 游戏本地接口， z 区服
 */
if (!isset($_SERVER['PATH_INFO'])) halt(-10, 500);
$pathInfo = explode('/', trim(str_replace('index.php', '', $_SERVER['PATH_INFO']), '/'));
if (count($pathInfo) != 3) halt(-20);

$path = '';
if ($pathInfo[0] == 'pf') {
    $path = "platform/{$pathInfo[1]}/";
} else if ($pathInfo[0] == 'local') {
    $path = 'local/';
} else {
    halt(-30);
}

//在混服情况下，合服情况下，平台和区服信息应该从请求参数中获取
//定义来自哪个平台
if (DEBUG && PLATFORM == 'dev') {
    //用于在开发服测试平台接口
    define('FROM_PLATFORM', 'dev');
} else {
    define('FROM_PLATFORM', $pathInfo[1]);
}
//define('FROM_ZONE');

//当前访问平台接口目录
define('CURR_PLATFORM_DIR', LIB_DIR.'/api/api/'.$path);

$file = CURR_PLATFORM_DIR.$pathInfo[2].'.php';

if (!file_exists($file)) halt(-40, 404);

define('PAY_KEY', $GLOBALS['cfg']['pay']['key']);
include $file;
