<?php
// 出于安全性考虑，运营中的系统不会在页面上报告任何详细的错误信息
// 只有在DEBUG模式下时，才会显示错误到页面上
// 建议设置为E_ALL，这样才能把所有可能的不稳定因素记录到日志中
error_reporting(E_ALL);

// 错误日志路径
define('_LOG_FILE', ROOT."/var/error.log");

register_shutdown_function('__fatal_error_handler');
set_error_handler('__error_handler');
set_exception_handler('__exception_handler');

// 注意不要在这里通过throw new ErrorException()的方式处理错误
// 因为这种方式不可靠，有时会throw失败，导致记录不到错误信息
function __error_handler($errno, $msg, $file, $line){
    if(($errno & error_reporting()) != $errno) return;
    ob_start();
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $trace = ob_get_clean();
    __error_log("PHP Error(".__error_code_to_string($errno)."): $msg", $file, $line, $trace);
}

function __exception_handler($e){
    __error_log("PHP Exception({$e->getCode()}): {$e->getMessage()}", $e->getFile(), $e->getLine(), $e->getTraceAsString()."\n");
}

function __fatal_error_handler() {
    $e = error_get_last();
    if($e !== NULL) {
        ob_start();
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $trace = ob_get_clean();
        __error_log('PHP Fatal error('.__error_code_to_string($e['type'])."): {$e['message']}", $e['file'], $e['line'], $trace);
    }
}

function __error_log($msg, $file, $line, $trace){
    $msg = sprintf("[ERROR][%s %s:%d] %s\n%s", date('y/m/d H:i:s'), basename($file), $line, $msg, $trace);
    error_log($msg, 3, _LOG_FILE);
    if(defined('DEBUG') && DEBUG == true){
        header('Content-Type: text/plain; charset=utf-8');
        exit($msg);
    }else{
        exit("系统内部错误");
    }
}

function __error_code_to_string($code){
    switch($code){ 
    case E_ERROR: return 'E_ERROR'; 
    case E_WARNING: return 'E_WARNING'; 
    case E_PARSE: return 'E_PARSE'; 
    case E_NOTICE: return 'E_NOTICE'; 
    case E_CORE_ERROR: return 'E_CORE_ERROR'; 
    case E_CORE_WARNING: return 'E_CORE_WARNING'; 
    case E_COMPILE_ERROR: return 'E_COMPILE_ERROR'; 
    case E_COMPILE_WARNING: return 'E_COMPILE_WARNING'; 
    case E_USER_ERROR: return 'E_USER_ERROR'; 
    case E_USER_WARNING: return 'E_USER_WARNING'; 
    case E_USER_NOTICE: return 'E_USER_NOTICE'; 
    case E_STRICT: return 'E_STRICT'; 
    case E_RECOVERABLE_ERROR: return 'E_RECOVERABLE_ERROR'; 
    case E_DEPRECATED: return 'E_DEPRECATED'; 
    case E_USER_DEPRECATED: return 'E_USER_DEPRECATED'; 
    default: return $code;
    } 
}
