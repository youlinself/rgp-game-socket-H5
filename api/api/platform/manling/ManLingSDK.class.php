<?php
/**
 * User: weiming Email: 329403630@qq.com
 * Date: 2017/7/29
 * Time: 15:00
 * 漫灵游戏 辅助类
 */

class ManLingSDK {
    const APP_KEY = 'c8002c44bfb273a09f83b4fd964a943a';
    const APP_SECRET = '999f60c70f3ab3a19188b1f05727f0fc';
    const RSA_PUBLIC_KEY = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCppgXxBVEmn+gmz+udBnqj6vy81ddV6vo48BD05eyD53+968IL1NPjS2RIIfWK1H0aXzKtRDat1Qk5bQRTyIsSZdGUWC4oMMKjwZZ5nPvNB9zUWkKlIOR4uKNHGJz45lS2DHPl3QFAZ7WJcouWsrgX2a/8Q8t3Mi5hD8tiG22fywIDAQAB'; //RSA 公钥
    //日志记录开启
    const DEBUG = true;

    /**
     * 签名验证 充值回调
     * @param $data
     * @param $sign
     * @return int
     */
    public static function verify($data, $sign)
    {
        $pem = chunk_split(self::RSA_PUBLIC_KEY,64,"\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n".$pem."-----END PUBLIC KEY-----\n";
        $public_key_id = openssl_pkey_get_public($pem);
        $signature = base64_decode($sign);
        $result = openssl_verify($data, $signature, $public_key_id, OPENSSL_ALGO_MD5);//成功返回1,0失败，-1错误,其他看手册
        openssl_free_key($public_key_id);
        return $result;
    }

    public static function createSign($params, $secret = self::APP_SECRET)
    {
        unset($params['sign']);
        ksort($params, SORT_REGULAR );

        $query_string = [$secret];
        foreach ($params as $key => $val) {
            if(empty($val)) continue;
            array_push($query_string, $key . '=' . $val);
        }

        $string = implode('&', $query_string);

        return md5($string);
    }

}