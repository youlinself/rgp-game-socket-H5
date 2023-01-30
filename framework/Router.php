<?php
/*-----------------------------------------------------+
 * 路由处理
 * @author yeahoo2000@gmail.com
 +-----------------------------------------------------*/
class Router{
    private $routes = [];

    public function map($pattern, $callback){
        $this->routes[$pattern] = $callback;
    }
}
