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

    //金额对应商品ID
    public static $money_to_pack_id = [
        //RMB
        '6' => 3,
        '30' => 4,
        '98' => 5,
        '198'  => 6,
        '328'  => 7,
        '648'  => 8,
        '68'  => 20,
        '128'  => 21,
    ];

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

    /**
     * 接口信息返回
     * @param $code
     * @param $msg
     * @param array $data
     */
    public static function outSy($code, $msg, $data = []) {
        echo json_encode(array('code' => $code, 'message' => $msg, 'data' => $data));
        exit;
    }

    /**
     * 金额
     * @param $money
     * @return int|mixed
     */
    public static function getPackageId($money)
    {
        $money = (int)$money; //强制转成int

        return !in_array($money, array_keys(self::$money_to_pack_id)) ? 0 : self::$money_to_pack_id[$money];
    }

    /**
     *
     * @param $params
     * @return string
     */
    public static function createBondSign($params)
    {
        $signStr = $params['platform'] . $params['zone_id']. $params['type'] . $params['time'];
        return md5($signStr. 'ZMIUClkQieIL7tElz7VSjVrT');
    }

}
