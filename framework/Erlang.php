<?php
/**----------------------------------------------------+
 * Erlang服务器访问接口
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/

/*
format_string:
    The format string is composed of one or more directives.
        ~a - an atom
        ~s - a string  字符串直接使用这个或 ~b都行
        ~b - a binary (contain 0x0 in string)
        ~i - an integer  使用数字类型记得要(int)$val, intval($val)
        ~l - a long integer
        ~u - an unsigned long integer
        ~f - a float
        ~d - a double float
        ~p - an erlang pid
data:
    The data to send to Erlang node. Initial wrapped with an array, tuple and list data must be wrapped with extra dimension.
 */

class Erlang{
    protected $conn;

    public function __construct($node = '', $cookie = '') {
        if(!function_exists('peb_connect')){
            throw new ErrorException('错误: 系统中未安装PEB(Php-Erlang Bridge)扩展');
        }
        $this->connect($node, $cookie);
    }

    // 连接erlang节点
    public function connect($node, $cookie){
        $this->conn = peb_connect($node, $cookie);
        if(!$this->conn){
            throw new ErrorException("连接erlang节点失败[".peb_errorno()."]: ".peb_error());
        }
    }

    // 执行一个RPC调用
    public function rpc($mod, $fun, $argk = '[]', $argv = array()){
        $arg = peb_encode($argk, array($argv));
        $rtn = peb_rpc($mod, $fun, $arg, $this->conn);
        if(!$rtn) return false;
        $rtn = peb_decode($rtn);
        return isset($rtn[1]) ? $rtn : $rtn[0];
    }

    // 关闭当前连接
    public function close(){
        return peb_close($this->conn);
    }

    // 格式化数据为erlang中的列表
    public function repeatFormat($format, $len) {
        if($len < 1) return '';
        return implode (', ', array_fill(0, $len, $format));
    }
}
