<?php
/**----------------------------------------------------+
 * PHP环境配置文件
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/

// 环境初始化
define('DEBUG', false);
define('PASSWD', 'l1bc2vz93jf91gs1');

// 错误报告设置
if(DEBUG){ 
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}else{
    error_reporting(0);
    ini_set('display_errors', 0);
}

//系统目录结构
define('ZONE_ROOT',             getenv('ROOT'));
define('VAR_DIR',               ZONE_ROOT.'/var');
define('WEB_DIR',               ZONE_ROOT.'/www');
define('LIB_DIR',               "{{code_path}}/{{ver}}/web");

require LIB_DIR.'/global.php';

// 平台标识
define('PLATFORM',              "{{platform}}");
// 游戏区号
define('ZONE_ID',               {{zone_id}});
// 语言设置
define('LANG',                  "{{lang}}");
// 主密钥
define('SERVER_KEY',            "{{srv_key}}");

// --------------------------------------------------------
// 配置
// --------------------------------------------------------

$GLOBALS['cfg'] = array(
    'game_name' => '{{game_name}}', // 该平台上使用的游戏名字，不同平台可能会使用不同的游戏名
    'zone_name' => '{{zone_name}}', // 游戏分区名称，有时也作为页面title
    'ver' => '{{ver}}', // 当前版本号
    'host' => '{{host}}', // 游戏节点服务器地址，此处不带'http://'前缀
    'ip' => '{{ip}}', // 游戏节点服务器IP地址
    'port' => {{port}}, // 游戏节点服务器端口
    'url_main' => 'http://{{host}}', // 服务器URL
    'time_open' => '{{open_time}}', // 开服时间
    'merge_time' => '{{merge_time}}', // 合服时间
    'combine' => '{{combine}}', // 合服列表

    'server_key' => '{{srv_key}}', // 服务器主密钥

    'ticket_lifetime' => 300, // ticket通用有效时长，单位：秒

    // erlang节点信息
    'erl' => array(
        'cookie' => '{{cookie}}',
        'nodename' => '{{nodename}}',
    ),

    // 充值接口相关配置
    'pay' => array(
        'key' => '{{pay_key}}', // 充值请求加密密钥
        'url' => '{{pay_link}}', // 充值地址
        'ticket_lifetime' => 60, // ticket失效时长，单位:秒
        'allow_ips' => array({{pay_allow_ips}}), // 充值请求IP白名单，留空表示不启用
    ),

    // 数据库服务器信息
    'database' => array(
        'driver' => 'mysql',
        'encode' => 'utf8',
        'host' => '{{db_host}}',
        'user' => '{{db_user}}',
        'pass' => '{{db_pass}}',
        'dbname' => '{{db_name}}'
    ),

    // 用于验证的一些正则表达式
    're' => array(
        'name' => '/^[a-z0-9_]{3,20}$/' // 角色名规则
    )
);

// --------------------------------------------------------
// 常规初始化
// --------------------------------------------------------

// 时区设置，不建议使用php.ini中的统一设置，失去了灵活性
date_default_timezone_set("{{timezone}}");

// 设置session文件的失效时间，默认为6小时，必要时可在相应模块重设此值
ini_set("session.gc_maxlifetime", 21600);
// session文件清除机率，默认为20%，访问量大的网站可以设小一些
ini_set('session.gc_probability', 20);
// session保存到指定目录，最好使用内存虚拟的目录，以保证在大量访问时的效率
session_save_path(VAR_DIR.'/sess');

// 支持页面回跳
header('Cache-control: private, must-revalidate');
// 解决IE中iframe跨域访问cookie/session的问题
header('P3P: CP=CAO PSA OUR');
// 防止cookie被js获取
@ini_set("session.cookie_httponly", 1);

// 使用gz_handler压缩输出页面
// ob_start('ob_gzhandler'); //不能启用，在IE5,IE6中有时会白屏

// 如果PHP没有自动转义Request数据则在这里进行转义处理
if (!get_magic_quotes_gpc()) {
    $_GET = addQuotes($_GET);
    $_POST = addQuotes($_POST);
    $_FILES= addQuotes($_FILES);
    $_COOKIE= addQuotes($_COOKIE);
}
