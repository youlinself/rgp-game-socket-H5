<?php
/*-----------------------------------------------------+
 * 公共函数
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/

/**
 * 打印指定变量的内容(用于调试)
 * @param mixed $var 变量名
 */
function d($var) {
    $content = '<pre>' . htmlspecialchars(print_r($var, true)) . '</pre>';
    echo $content;
    exit;
}

/**
 * 用addslashes处理变量,可处理多维数组（使用反斜线引用字符串）
 * @param mixed $vars 待处理的数据
 * @return mixed
 */
function addQuotes($vars) {
    return is_array($vars) ? array_map(__FUNCTION__, $vars) : addslashes($vars);
}

/**
 * 对指定变量进行stripslashes处理,可处理多维数组（去掉字符串中的反斜线字符。若是连续二个反斜线，则去掉一个，留下一个。若只有一个反斜线，就直接去掉）
 * @param mixed $vars 待处理的数据
 * @return mixed
 */
function stripQuotes($vars) {
    return is_array($vars) ? array_map(__FUNCTION__, $vars) : stripslashes($vars);
}

/**
 * 对变量进行 trim 处理,支持多维数组.(截去字符串首尾的空格)
 * @param mixed $vars
 * @return mixed
 */
function trimArr($vars) {
    return is_array($vars) ? array_map(__FUNCTION__, $vars) : trim($vars);
}

/**
 * 得到客户端IP地址
 * @return string
 */
function clientIp() {
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else {
            if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
                $ip = getenv("REMOTE_ADDR");
            } else {
                if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                } else {
                    $ip = "0.0.0.0";
                }
            }
        }
    }
    if (!preg_match('#^\d+\.\d+\.\d+\.\d+$#', $ip)) {
        return '0.0.0.0';
    }
    return $ip;
}

/**
 * 加密一些隐私数据
 * @param mixed $data 要加密的数据
 * @return int 索引ID
 */
function encrypt($data) {
    if (!isset($_SESSION['game_accounts_encrypt'])) {
        $_SESSION['game_accounts_encrypt'] = array();
    }
    //已经存在时，直接返回对应的key
    $old = array_search($data, $_SESSION['game_accounts_encrypt']);
    if ($old !== false) {
        return $old;
    }
    $id = count($_SESSION['game_accounts_encrypt']) + 1;
    $_SESSION['game_accounts_encrypt'][$id] = $data;
    return $id;
}

/**
 * 解密隐私数据
 * @param int $id 索引ID
 * @return mixed | bool
 */
function decrypt($id) {
    if (isset($_SESSION['game_accounts_encrypt'][$id])) {
        return $_SESSION['game_accounts_encrypt'][$id];
    }

    return false;
}

/**
 * 获取服务器ID前缀
 * @param string $server_id
 * @return int
 */
function get_server_prefix($server_id) {
    preg_match('#(.+?)\d+$#', $server_id, $match);

    return isset($match[1]) && $match[1] ? $match[1] : $server_id;
}

/**
 * 获取指定参数的服数
 * @param string $server_id
 * @return int 服数
 */
function get_server_num($server_id) {
    preg_match('#.*?(\d+)$#', $server_id, $match);

    return isset($match[1]) && $match[1] != '' ? (int)$match[1] : (int)$server_id;
}

/**
 * 写文件系统
 * @param string $file 文件名
 * @param string $content 内容
 * @param string $mode
 */
function writeFile($file, $content, $mode='wb'){
    $oldMask= umask(0);
    $fp= @fopen($file, $mode);
    if (!is_writable($file)) {
        exit('文件不可写');
    }
    fwrite($fp, $content);
    fclose($fp);
    umask($oldMask);
}


/**
 * 错开时间
 * 防止计划任务在同一时间并发执行，造成服务器资源紧张
 */
function staggerTime(){
    if(!extension_loaded('posix')) {
        exit('Error: posix extension not loaded !');
    } else {
        mt_srand(posix_getpid());
        sleep(rand(0, 180));
    }
}

/**
 * 获取帐号key
 */
function getAccKey(){
    if(isset($_REQUEST['acck'])){
        $acc = trim($_REQUEST['acck']);
        return $acc;
    }

    $acc = isset($_SERVER['QUERY_STRING']) ? trim($_SERVER['QUERY_STRING']) : '';
    if(substr($acc, 0, 6) == 'reconn'){
        $acc = substr($acc, 6);
    }
    return $acc;
}

/**
 * 判定是否重连
 */
function isReconn(){
    if(isset($_REQUEST['reconn'])){
        return true;
    }
    return false;
}

/**
 * 终止脚本执行，输出信息到头部
 */
function halt($errno, $statusCode = 500) {
    header('E: '.$errno, true, $statusCode);
    exit;
}

/**
 * 加载define.cfg.php到 $GLOBALS
 */
function loadDefines() {
    if (!isset($GLOBALS['def'])) {
        $GLOBALS['def'] = include LIB_DIR.'/define.cfg.php';
    }
    return $GLOBALS['def'];
}

/**
 * 调用 erlang 接口
 * @param string $module 接口模块名
 * @param string $method 接口名称
 * @param string $format 参数数据格式
 * @param array $args 参数
 *
 * @return array    array(0 || 1, error, origin_data)
 */
function erl($module, $method, $format = '', $args = array()) {
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (!$socket) return array('success' => false, 'message' => 'create_conn_failed');
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>2, "usec"=>0));
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>2, "usec"=>0));

    $conn = socket_connect($socket, '127.0.0.1', $GLOBALS['cfg']['port']);
    if (!$conn) return array('success' => false, 'message' => 'connect_failed');
    socket_write($socket, "web_conn---------------");
    $time = TIME;
    if (trim($format) === '') { 
        $data = array(FROM_PLATFORM, $time, md5(FROM_PLATFORM.$time.SERVER_KEY), $format, $module, $method);
    } else {
        $data = array(FROM_PLATFORM, $time, md5(FROM_PLATFORM.$time.SERVER_KEY), $format, $module, $method, $args);
    }
    $data = json_encode($data);
    socket_write($socket, pack('n', strlen($data)));
    socket_write($socket, $data);

    $recvData = '';
    while ($r = socket_recv($socket, $bufs, 1024, 0)) {
        $recvData .= $bufs;
    }
    socket_close($socket);
    if (json_last_error() !== JSON_ERROR_NONE) return array('success' => false, 'message' => '返回数据不是json格式');

    return json_decode($recvData, true);
}

/*function returnMsg($success, $msg, $module, $method, $format = '', $args = array()) {

    if(in_array($msg, array('create_conn_failed', 'connect_failed', '返回数据不是json格式')))
    {
        $data = [];

    }

}*/

/**
 * 记录debug信息
 *
 * @param  mixed    $message
 * @param  boolean  $flag    输出分隔符分开，表示一个完整的记录
 * @return boolean
 */
function recoder($message, $flag = true) {
    if (!DEBUG) return false;
    $f = VAR_DIR.'/debug.log';
    $fh = fopen($f, 'a');
    if (!$fh) return false;
    $date = date('Y/m/d H:i:s', time());
    if (is_array($message) || is_object($message)) {
        $message = var_export($message, true);
    }
    $message = "[{$date}] {$message}\n";
    if ($flag) $message .= str_repeat('=', 60)."\n\n";
    $ret = fwrite($fh, $message);
    fclose($fh);
    return $ret === false ? false : true;
}

/**
 * 数组转换成lua_table
 */
function luatable_encode($arr){
    $str = "{";
    foreach($arr as $k => $v){
        if(is_int($k)){
            $k = $k + 1;  // lua的下标是从1开始的
            $str .= "[$k] = ";
        }else{
            $str .= "['$k'] = ";
        }
        if(is_int($v)){
            $str .= $v;
        }elseif(is_array($v)){
            $str .= luatable_encode($v);
        }else{
            $str .= "[[$v]]";
        }
        $str .= ",\n";
    }
    $str .= "}";
    return $str;
}
