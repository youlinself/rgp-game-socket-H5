<?php
/**
 * User: weiming Email: 329403630@qq.com
 * Date: 2016/9/12
 * Time: 20:56
 * 诗悦游戏 海外 SDK 辅助类
 */
class SYHWSDK {
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
            case "shzghw":
                return "6daZVrDxGsics6CjDdW0AIqP";
                break;
            case "pay_web":
                return "6i08a3njKJeBvnZ8WjDA7F1b3wW6WyTxnYK";
                break;
            default:
                return "6daZVrDxGsics6CjDdW0AIqP";
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
     * 设置充值账户
     * @param $platform
     * @param $acc
     * @return string
     */
    public static function setPayAcc($platform, $acc)
    {
        switch ($platform) {
            case 'syen':
                return 'sygameen_'. $acc;
                break;

            default:
                return 'xinma_' . $acc;
        }
    }

}
