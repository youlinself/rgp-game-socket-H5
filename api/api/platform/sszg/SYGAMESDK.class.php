<?php
/**
 * User: weiming Email: 329403630@qq.com
 * Date: 2016/9/12
 * Time: 20:56
 * 我方 SDK 辅助类
 */
class SYGAMESDK {
    //日志记录打开
    const DEBUG     = true;

    /**
     * 游戏参数获取
     * @param $cps //游戏别名
     * @return string
     */
    public static function getAppKey($cps)
    {
        $cps = empty($cps) ?  '' : $cps;
        //IOS
        switch ($cps) {
            case "sszgandroid":
                return "ZMIUClkQieIL7tElz7VSjVrT";
                break;
            // case "syios_smzhs":
            //     return "ozT9fSTmCfqwom4WA0q9gsyT";
            //     break;
            default:
                // return "lkUOZqKb7kQJ6nOWMtl6tEK0";
                return "ZMIUClkQieIL7tElz7VSjVrT";
        }
    }
    /**
     * @param $params
     * @param string $cps
     * @return string
     */
    public static function createPaySign($params, $cps)
    {
        unset($params['sign']);
        ksort($params);

        $secret = self::getAppKey($cps);
        $str_arr = array();
        foreach ($params as $key=>$val) {
            $str_arr[] = rawurlencode($key) . "=" . rawurlencode($val);
        }

        $signStr = implode('&', $str_arr);
        return sha1($signStr . $secret);
    }
}
