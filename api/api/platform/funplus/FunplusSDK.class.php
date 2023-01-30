<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2020/3/2
 * Time: 14:21
 * funplus 游戏辅助类
 */

class Funplus {
    const APP_KEY = 'w=m2a04o4ew*jy*m%$_q@*hamh0$n2ygxq@a9uk*@)-bd6%l2!';
    //登陆验证地址
    const LOGIN_URL = 'https://passport.funplusgame.com/server_api.php';
    //测试
    const TEST_LOGIN_URL = 'https://passport-dev.funplusgame.com/server_api.php';
    //登陆验证返回加密密钥
    const RE_LOGIN_KEY =  'FdEBE8h746b8B4068EE62A9FA1976g0C';
    //日志记录开启
    const DEBUG  = true;

    /**
     * 获取游戏参数
     * @param $cps //游戏透传别名
     * @return string
     */
    public static function getAppKey($cps)
    {
        $cps = empty($cps) ?  '' : $cps;
        switch ($cps) {
            case 'funplus':
                return self::APP_KEY;
                break;

            default:
                return self::APP_KEY;
        }
    }

    /**
     *
     * @param $params
     * @param $cps
     * @return string
     */
    public static function createPaySign($params, $cps)
    {
        $appKey = self::getAppKey($cps);
        if (array_key_exists('token', $params)){
            unset($params['token']);
        }
        if(array_key_exists('token_all', $params)){
            unset($params['token_all']);
        }

        ksort($params);
        $signStr = implode('', $params);

        return md5($signStr.$appKey);
    }

    /**
     * 充值信息返回
     * @param $status
     * @param $reason
     */
    public static function payOut($status, $reason)
    {
        echo json_encode(array('status' => $status, 'reason' => $reason));
        exit;
    }

}