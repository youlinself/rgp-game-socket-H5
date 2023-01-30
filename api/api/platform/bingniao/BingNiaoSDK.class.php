<?php
/**
 * User: weiming Email: 329403630@qq.com
 * Date: 2016/10/31
 * Time: 14:20
 * 冰鸟游戏 辅助类
 */
class BingNiaoSDK {
    const APP_SECRET  = "5687d666f2eb1c22aac9c5e07cbaf173";
    //日志记录开启
    const DEBUG       = true;

    /**
     * 获取游戏参数
     * @param $cps //游戏透传别名
     * @return string
     */
    public static function getAppKey($cps)
    {
        $cps = empty($cps) ?  '' : $cps;
        switch ($cps) {
            case 'sszg_bingniao':
                return self::APP_SECRET;
                break;
            default:
                return self::APP_SECRET;
        }
    }

    /**
     * 充值验证sign
     * @param $params
     * @param $cps
     * @return string
     */
    public static function createPaySign($params, $cps)
    {
        $appKey  = self::getAppKey($cps);
        unset($params['sign'], $params['extinfo']);
        ksort($params);

        $arr = [];
        foreach ($params as $k => $v) {
            $arr[] = $k .'=' .$v;
        }

        $strSign = implode('', $arr);
        return md5($strSign . $appKey);
    }

    /**
     * 数据返回
     * @param $code
     * @param string $msg
     */
    public static function out($code)
    {
        echo json_encode(array('ret' => $code));
        exit;
    }
}