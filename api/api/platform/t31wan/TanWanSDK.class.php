<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/8/16
 * Time: 16:26
 * 贪玩 游戏 辅助类
 */

class TanWanSDK {
    //风色奇迹-闪烁之光(安卓)
    const FSQJ_PAY_KEY   = '8e952ae2a6ea8cd77410d86dd42d7ce3';
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
            case 'fsqj':
                return self::FSQJ_PAY_KEY;
                break;

            default:
                return self::FSQJ_PAY_KEY;
        }
    }

    /**
     * 登录验证 sign
     * @param $params
     * @param $cps
     * @return string
     */
    public static function createPaySign($params, $cps)
    {
        $appKey = self::getAppKey($cps);

        $signStr = $params['uid'] . $params['money'] . $params['time'] . $params['sid']
            . $params['orderid']  . $params['ext'] . $appKey;

        return md5($signStr);
    }

    /**
     * @param $ret
     * @param $msg
     */
    public static function payOut($ret, $msg)
    {
        echo json_encode(array('ret' => $ret, 'msg' => $msg));
        exit;
    }
}