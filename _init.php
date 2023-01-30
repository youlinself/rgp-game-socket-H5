<?php
/**
 *  初始化
 */

//初始化当前平台环境
require getenv('ROOT').'/env.php';

// 注册类自动加载方法
set_include_path(get_include_path().PATH_SEPARATOR.LIB_DIR.'/api/api/common/');
spl_autoload_register('class_loader');

define('TIME', time());

session_start();

/**
 * 类自动加载函数
 * @param string $class 类名
 */
function class_loader($class){
    if (class_exists($class) || interface_exists($class)) {
        return;
    }
    include $class.'.class.php';
}
