<?php
/**
 * 游戏接口调用基类，接口调用在继承本类后进行一次封装
 * 统一输入参数以及统一返回信息给调用者
 * @author: heshitan
 */

//定义游戏接口模块目录
define('GAME_API_DIR', LIB_DIR.'/api/game/');

class GameApi {
    private static $apiCache = array();

    /**
     * 统一接口返回信息
     * @param $error string 接口执行正常返回正确信息，统一填写大写 OK，否则填写错误信息
     * @param $data  mixed  接口正常执行后返回的额外数据，如果没有默认为 NULL
     *
     * @return array
     */
    public function ret($error = 'OK', $data = NULL) {
        return array('error' => $error, 'data' => $data);
    }

    /**
     * 调用游戏接口模块
     *
     * @param $module string 模块名称，与 api/game/ 下文件命名一致
     *
     * @return GameApi Module
     * @throws Exception
     */
    public static function call($module) {
        if (!isset(self::$apiCache[$module])) {
            $init = false;
            if (file_exists(GAME_API_DIR.$module.'.class.php')) {
                require GAME_API_DIR.$module.'.class.php';
                $class = "{$module}_GameApi";
                if (class_exists($class)) {
                    self::$apiCache[$module] = new $class();
                    $init = true;
                }
            }
            if (!$init) throw new Exception("Module {$module} Not Found.");
        }
        return self::$apiCache[$module];
    }
}