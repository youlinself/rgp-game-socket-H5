<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/7/24
 * Time: 19:39
 * 诗悦 融合SDK 辅助类
 */
class SYSDK {
    //融合SKD密钥
    const SECRET_SDK_KEY = 'd1bc09cd7424e811c176c78726de4702';
    const APP_PUBLIC_KEY = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3DfKtuCg7/8IW73/8QdOIdI7MM8i4kbtmG80QxuddxilwAJaO4j7HKGizyArZCUq+T7iFnJxGXXBu27MhdO01k5thVjABS/6F+2c4iEtUuoGhM48WXB/a7rpS7RVlsoWAgiMXOFikpmBmf2Zs2mtjXsTPm65WHRPOPCMjmY2BB9ZBoQg9HJoTJdoaQUaMxQzAxL+yhpHwK17upwCePD8+3Ij7md5fnmVD41U0cDnIEPAhk3HEjmjJM1kFueklQnjBXX0dzlAMl24fbi3wEORVmt5cA8/0izd0w5z8qIwppAJ9p2KDy7VXR5wDUrF79XWBnbKJgFGHLK7+ah45PaLXQIDAQAB';

    const APP_GAME_KEY = 'cBcc18D0808fEB9739601e893E2D4A07';

    const WEEKLY_KEY  = 'JZ0Psf67jc10U05cykVzpUctYExk';
    const CARNIVAL_KEY  = '43b06rT01AE96FH9f7ee7793eaf6578';
    //日志记录开启
    const DEBUG = true;

    /**
     * 融合SDK充值验证 sign
     * @param $params
     * @return string
     */
    public static function verifyPaySign($params)
    {
        $sign = $params['sign'];
        $signType = $params['signType'];
        unset($params['sign'], $params['signType']);

        Ksort($params);
        $arr = [];
        foreach ($params as $k => $v) {
            $arr[] = $k .'='. $v;
        }
        $signStr = implode('&', $arr) .'&'. self::SECRET_SDK_KEY ;

        if ($signType == 'md5') {
            return md5($signStr) == $sign;
        }
        return self::signVerify($signStr, $sign, self::formatPublicKey(self::APP_PUBLIC_KEY));

    }

    /**
     * 游戏验证 sign
     * @param $params
     * @return string
     */
    public static function createGameSign($params)
    {
        $signStr = $params['role_id'] . $params['platform'] . $params['zone_id'] . $params['time'] . self::APP_GAME_KEY;

        return md5($signStr);
    }

    /**
     * 使用RSA验证签名
     * @param string $data 待签名数据
     * @param string $sign 等验证签名字串
     * @param string $pubKey 公钥(必须经过格式化)
     * @return bool
     */
    public static function signVerify($data, $sign, $pubKey)
    {
        $res = openssl_pkey_get_public($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }

    /**
     * 去除私钥或公钥的头部和尾部，转换成不带格式的单行字符串
     * @param string //私钥或公钥
     * @return string
     */
    public static function stripKey($key)
    {
        $key = str_replace('-----BEGIN PUBLIC KEY-----', '', $key);
        $key = str_replace('-----END PUBLIC KEY-----', '', $key);
        $key = str_replace('-----BEGIN PRIVATE KEY-----', '', $key);
        $key = str_replace('-----END PRIVATE KEY-----', '', $key);
        $key = str_replace('-----BEGIN RSA PRIVATE KEY-----', '', $key);
        $key = str_replace('-----END RSA PRIVATE KEY-----', '', $key);
        $key = str_replace("\n", '', $key);
        return $key;
    }

    /**
     * 格式化公钥(将单行的密钥转换成多行带格式的密钥)
     * @param string $pubKey 公钥
     * @return string
     */
    public static function formatPublicKey($pubKey)
    {
        $pubKey = self::stripKey($pubKey);
        $pubKey = '-----BEGIN PUBLIC KEY-----' . PHP_EOL . chunk_split($pubKey, 64, PHP_EOL) . '-----END PUBLIC KEY-----' . PHP_EOL;
        return $pubKey;
    }

    /**
     * @param $params
     * @return string
     */
    public static function createWeeklySign($params, $appKey = self::WEEKLY_KEY)
    {
        unset($params['sign']);
        // 排序参数
        ksort($params);
        reset($params);

        // 构建QueryString
        $p = [];
        foreach ($params as $k => $v) {
            $p[] = rawurlencode($k) . "=" . rawurlencode($v);
        }
        //java和php url编码对*处理不同，java保留，php处理
        $encoded = strtr(implode($p, "&"), ['%2A' => '*']);
        //Logger::info("签名串：".$encoded);
        return sha1($encoded . $appKey);
    }
}