<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/6/4
 * Time: 14:34
 */

class SDK6KW {
    //神明物语-安卓
    const SMWY_AZ_APP_KEY  = "aa3705a98c249839c7e359869b22f5aa";
    //神明物语-应用宝
    const SMWY_YYB_APP_KEY = "579032c8198f2b082a7bcf79ff62cdce";
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
            case 'az':
                return self::SMWY_AZ_APP_KEY;
                break;
            case 'yyb':
                return self::SMWY_YYB_APP_KEY;
                break;
            default:
                return '';
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
        unset($params['sign']);
        ksort($params);

        $arr = [];
        foreach ($params as $k => $v) {
            $arr[] = $k .'='. $v;
        }

        $signStr = implode('&', $arr);
        return md5($signStr . $appKey);
    }
}