<?php
/*-----------------------------------------------------+
 * 请求处理类
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Request extends ObjectBaseArray{
    private $_post = null;
    private $_get = null;
    private $_files = null;
    private $_cookie = null;
    private $body = null;

    public $ip;
    public $base;
    public $url;
    public $method;
    public $params = [];

    public function __construct(){
        parent::__construct($_SERVER);
        // 获取客户端ip
        $this->ip = $this->clientIp();
        // 获取请求方式
        // HTTP/1.1中定义了8种请求方式
        // * OPTIONS
        // * GET
        // * HEAD
        // * POST
        // * PUT
        // * DELETE
        // * TRACE
        // * CONNECT
        if(isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])){
            $this->method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        }else{
            $this->method = $_SERVER['REQUEST_METHOD'];
        }
        // 获取baseUrl
        $this->base = str_replace([$_SERVER['DOCUMENT_ROOT'], '\\', ''], ['', '/', '%20'], $_SERVER['SCRIPT_FILENAME']);
        App::setBaseUrl($this->base);
		// 忽略掉url中没有带入口php文件名的情况，这时应该访问的是index页
        if(stristr($_SERVER['REQUEST_URI'], $this->base) === FALSE) {
            $this->url = '';
        }else{
            // 获取真正的url路径
            $this->url = str_replace('?'.$_SERVER["QUERY_STRING"], '', preg_replace('!^'.$this->base.'!', '', $_SERVER['REQUEST_URI']));
        }
        if(empty($this->url)) $this->url = '/';
    }

    public function getBody(){
        if(!is_null($this->body)){
            return $this->body;
        }
        if($this->method == 'POST' || $this->method == 'PUT'){
            $this->body = file_get_contents('php://input');
        }
        return $this->body;
    }

    public function post(){
        if(!is_null($this->_post)){
            return $this->_post;
        }
        return $this->_post = new ObjectBaseArray($_POST);
    }

    public function get(){
        if(!is_null($this->_get)){
            return $this->_get;
        }
        return $this->_get = new ObjectBaseArray($_GET);
    }

    public function cookies(){
        if(!is_null($this->_cookies)){
            return $this->_cookies;
        }
        return $this->_cookies = new ObjectBaseArray($_COOKIES);
    }

    public function files(){
        if(!is_null($this->_files)){
            return $this->_files;
        }
        return $this->_files = new ObjectBaseArray($_FILES);
    }

    public function clientIp(){
        return getenv('HTTP_CLIENT_IP')?:
            getenv('HTTP_X_FORWARDED_FOR')?:
            getenv('HTTP_X_FORWARDED')?:
            getenv('HTTP_FORWARDED_FOR')?:
            getenv('HTTP_FORWARDED')?:
            getenv('REMOTE_ADDR');
    }

    // 判断是否ajax请求
    public function isAjax(){
        return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    // 检查当前脚本是否在命令行环境中执行
    public function inCLI(){
        if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])){
            return true;
        } else {
            return false;
        }
    }
}
