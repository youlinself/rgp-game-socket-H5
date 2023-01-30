<?php
/*-----------------------------------------------------+
 * SESSION相关处理
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Session{
    public function __construct(){
    }

    public function start(){
        session_start();
    }

    public function close(){
        session_write_close();
    }

    public function __set($k, $v){
        $_SESSION[$k] = $v;
    }

    public function __get($k){
        if(isset($_SESSION[$k])) return $_SESSION[$k];
        return false; // 注意:访问不存在的变量时返回的默认值是false，如果想知道是不是存在该变量请使用isset()
    }

    public function __isset($k){
        return isset($_SESSION[$k]);
    }

    public function __unset($k){
        unset($_SESSION[$k]);
    }

    public function __call($name, $params){
        throw new ErrorException("无法调用不存在的方法: $name()");
    }
}
