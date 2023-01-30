<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/7/25
 * Time: 11:00
 * r2game 游戏 辅助类
 */

class R2Game {
    //R2game游戏充值key
    const PAY_KEY = 'c4066357e0efe2f3';
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
            case 'r2':
                return self::PAY_KEY;
                break;

            default:
                return self::PAY_KEY;
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

        $signStr = $params['username'] . $params['time'] . $params['game'] . $params['serverid'] .
            $params['money'] . $params['orderid'] . $params['game_coin'] . $params['item'] . md5($appKey . 'R2Games');

        return md5($signStr);
    }

    /**
     * 登录验证 sign
     * @param $params
     * @param $cps
     * @return string
     */
    public static function createLoginSign($params, $cps)
    {
        $appKey = self::getAppKey($cps);
        $signStr = $params['uid'] . $params['time'] . $appKey;

        return md5(md5($signStr). $appKey);
    }

    /**
     * @param $ret
     * @param $msg
     */
    public static function payOut($ret, $msg)
    {
        echo json_encode(array('status' => $ret, 'message' => $msg));
        exit;
    }
}