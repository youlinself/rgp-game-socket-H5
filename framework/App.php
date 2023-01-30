<?php
/*-----------------------------------------------------+
 * PHP简易框架
 * 目的是提供一种简单灵活易扩展的PHP使用方式
 * @author yeahoo2000@gmail.com
 *
 * 基本使用流程:
 * 1.梆定自定义函数和类到App，构建适合业务逻辑的运行环境
 * 2.映射url到对应的处理函数或方法
 * 3.运行App::start()处理url路由
 * 4.由router调用相应的函数或方法
 *
 * 文件和目录约定:
 * ROOT/init.php 自定义系统初始化
 * ROOT/env.php 配置文件
 * ROOT/lib/ 自定义类库存储目录
 * ROOT/mod/ 业务逻辑模块目录
 * ROOT/tpl/ 模板文件目录
 * ROOT/var/ 动态数据临时存储目录
 * ROOT/var/sess php session存储目录
 * ROOT/var/error.log 系统日志，包括错误日志
 *
 * 系统内置基础组件:
 * App::cfg() 访问配置文件
 * App::map() 梆定自定义函数
 * App::register() 注册自定义类库
 * App::route() 映射url到指定的callback
 * App::request() 请求处理
 * App::response() 响应处理
 * App::url() 生成一个url路径
 * App::redirect() 重定向
 * App::debug() 输出一条调试信息到日志文件
 * App::start() 开始处理url路由
 * Util.php 工具包
 * OS.php 操作系统相关类库
 * View.php 视图模板处理类库
 +-----------------------------------------------------*/
define('FRAMEWORK_ROOT', __DIR__);
App::bootup();

class App{
    private static $kernel = null;

    private $baseUrl = '';
    private $funcs = [];
    private $classes = [];
    private $instances = [];
    private $routes = [];

    public static function _classLoader($class){
        require "$class.php";
        // 载入脚本时自动调用__awake()方法
        if(method_exists($class, '__awake')){
            $class::__awake();
        }
    }

    private function __construct(){
        // 设置autoload，如果有新的目录想要加入autoload搜索路径中,
        // 可以自行调用set_include_path()指定
        set_include_path(
            ROOT.'/mod'.PATH_SEPARATOR.
            ROOT.'/lib'.PATH_SEPARATOR.
            dirname(__FILE__).PATH_SEPARATOR.
            get_include_path()
        );
        spl_autoload_register([__CLASS__, '_classLoader']);
    }

    public static function bootup(){
        if(!defined('ROOT')) exit("没有定义ROOT常量，系统无法工作");
        require 'errorhandler.php';

        if(is_null(self::$kernel)){
            self::$kernel = new self();
        }

        // 注册默认的cfg, request, response
        self::register('cfg', 'Cfg');
        self::register('request', 'Request');
        self::register('response', 'Response');
        self::register('sess', 'Session');
        if(self::cfg()->ini_set->session->auto_start){
            self::sess()->start();
        }
    }

    // TODO:目前只支持简单匹配
    public static function start(){
        $targetUrl = self::$kernel->request()->url;
        $params = self::$kernel->request()->params;
        $matched = false;
        foreach(self::$kernel->routes as $url => $callback){
            if($url == $targetUrl || $url == '*'){
                // 返回true则继续下一个匹配
                if(true == call_user_func_array($callback, $params)){
                    $matched = true;
                    continue;
                }else{
                    return;
                }
            }
        }
        if(!$matched){
            self::$kernel->response(false)
                ->status(404)
                ->write('404 找不到此页面')
                ->send();
        }
    }

    public static function route($pattern, $callback){
        self::$kernel->routes[$pattern] = $callback;
    }

    public static function map($name, $callback){
        if(method_exists(__CLASS__, $name)) throw new ErrorException("不能覆盖内部方法: ${name}");
        self::$kernel->funcs[$name] = $callback;
    }

    public static function register($name, $class, $params = [], $callback = null){
        // if(!class_exists($class)) throw new ErrorException("无法注册不存在的类: ${class}");
        if(method_exists(__CLASS__, $name)) throw new ErrorException("不能覆盖内部方法: ${name}");
        self::$kernel->classes[$name] = [$class, $params, $callback];
    }

    public static function unregister($name, $class, $params = [], $callback = null){
        unset(self::$kernel->classes[$name]);
    }

    public static function setBaseUrl($baseUrl){
        self::$kernel->baseUrl = $baseUrl;
    }

    public static function getBaseUrl(){
        return self::$kernel->baseUrl;
    }

    public static function url($path, $params = []){
        if(empty($params)){
            return self::$kernel->baseUrl.$path;
        }else{
            $p = '?';
            foreach($params as $k => $v){
                $p .= "$k=$v";
            }
            return self::$kernel->baseUrl.$path.urlencode($p);
        }
    }

    public static function redirect($url){
        self::$kernel->response()
            ->status(303)
            ->header('Location', $url)
            ->send();
    }

    public static function error($msg){
        self::log('ERROR', $msg);
        exit('发生了系统内部错误'); // 不要显示详细错误到页面上
    }

    public static function debug($msg){
        self::log('DEBUG', $msg);
    }

    public static function info($msg){
        self::log('INFO', $msg);
    }

    private static function log($tag, $msg){
        $caller = debug_backtrace()[1]; // 第1层的栈包含了调用者信息
        $msg = sprintf("[%s][%s %s:%d] %s\n", $tag, date('y/m/d H:i:s'), basename($caller['file']), $caller['line'], $msg);
        error_log($msg, 3, ROOT."/var/error.log");
    }

    public static function __callStatic($name, $params){
        return self::$kernel->invoke(self::$kernel, $name, $params);
    }

    public function __call($name, $params){
        return $this->invoke($this, $name, $params);
    }

    private function invoke($kernel, $name, $params){
        if(isset($kernel->funcs[$name])){
            return call_user_func_array($kernel->funcs[$name], $params);
        }
        if(isset($kernel->instances[$name]) && empty($params)){
            return $kernel->instances[$name];
        }
        if(isset($kernel->classes[$name])){
            list($class, $initParams, $callback) = $kernel->classes[$name];
            if(!empty($params)){
                // 如果带有参数则说明是需要创建新的对象
                // 并且把第一个参数丢弃进行实例化新对象
                array_shift($params);
                $ref = new ReflectionClass($class);
                $obj = $ref->newInstanceArgs($params);
                return $obj;
            }
            $ref = new ReflectionClass($class);
            $obj = $ref->newInstanceArgs($initParams);
            $kernel->instances[$name] = $obj;
            return $obj;
        }
        throw new ErrorException("无法调用未梆定的函数或方法: App::${name}()");
    }
}

class ObjectBaseArray{
    protected $__vars;

    public function __construct($vars = [], $allToObject = false){
        $this->setArray($vars, $allToObject);
    }

    public function setArray($vars, $allToObject = false){
        if($allToObject){
            $this->__vars = $this->allToObject($vars);
        }else{
            $this->__vars = &$vars;
        }
    }

    public function getArray(){
        return $this->__vars;
    }

    public function __set($k, $v){
        $this->__vars[$k] = $v;
    }

    public function __get($k){
        if(isset($this->__vars[$k])) return $this->__vars[$k];
        return false; // 注意:访问不存在的变量时返回的默认值是false，如果想知道是不是存在该变量请使用isset()
    }

    public function __isset($k){
        return isset($this->__vars[$k]);
    }

    public function __unset($k){
        unset($this->__vars[$k]);
    }

    public function __call($name, $params){
        throw new ErrorException("无法调用不存在的方法: $name()");
    }

    // 将数组中所有子项类型为array的数据转为ObjectBaseArray
    private function allToObject($arr){
        foreach($arr as $k => $v){
            if(is_array($v)){
                $arr[$k] = new self($v, true);
            }else{
                $arr[$k] = $v;
            }
        }
        return $arr;
    }
}
