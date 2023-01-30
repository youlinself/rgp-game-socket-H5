<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/6/4
 * Time: 14:11
 * 9377 游戏 辅助类
 */

class SDK9377 {
    //9377支付验证KEY
    const SSZG_APP_KEY = "8651cf548ba895bb896d01e106c718ca";
    const SSZG_IOS_APP_KEY = '6a9a7b3ce5f1e592242f813cb530770c';
    const SYNN_IOS_APP_KEY = '616473d3a59c2c0fd91ebbe33c0cb096';
    const APP_IOS = '539e788a7754c4af95d489183ef1739d';

    //日志记录开启
    const DEBUG = true;


    /**
     * 获取游戏参数
     * @param $cps //游戏透传别名
     * @return string
     */
    public static function getAppKey($cps)
    {
        $cps = empty($cps) ?  '' : $cps;
        switch ($cps) {
            case '9377sszg':
                return self::SSZG_APP_KEY;
                break;
            case 'syios_9377':
                return self::SSZG_IOS_APP_KEY;
                break;
            case '9377_synn':
                return self::SYNN_IOS_APP_KEY;
                break;
            case '9377_ios':
                return self::APP_IOS;
                break;

            default:
                return self::SSZG_APP_KEY;
        }
    }

    /**
     * 充值验证 sign
     * @param $params
     * @param $cps
     * @return string
     */
    public static function createPaySign($params, $cps)
    {
        $appKey = self::getAppKey($cps);
        $money  = empty($params['money'])? $params['game_coin'] : $params['money'];

        $signStr = $params['order_id'].$params['username'].$params['server_id'].$money.$params['extra_info'].$params['time'].$appKey;
        return md5($signStr);
    }

    /**
     * 输出返回格式信息
     * @param $code
     * @param string $msg
     */
    public static function out($code, $msg = '')
    {
        echo json_encode(array('state'=>$code, 'msg'=>$msg));
        exit;
    }
}