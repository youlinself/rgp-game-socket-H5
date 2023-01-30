<?php
/**
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/6/6
 * Time: 15:00
 * oppo游戏 辅助类
 */

class OPPOSDK
{
    const APP_SECRET = '0d5785b55e98481a98a621fde9bae042';
    //礼包码密钥
    const PUBLIC_KEY = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCmreYIkPwVovKR8rLHWlFVw7YDfm9uQOJKL89Smt6ypXGVdrAKKl0wNYc3/jecAoPi2ylChfa2iRu5gunJyNmpWZzlCNRIau55fxGW0XEu553IiprOZcaw5OuYGlf60ga8QT6qToP0/dpiL/ZbmNUO9kUhosIjEu22uFgR+5cYyQIDAQAB";

    //礼包码接口
    const GIFT_URL = 'http://oss.api.shiyuegame.com/index.php/card/send';//正式
    //礼包码密钥
    const APP_GIFT_KEY = 'cmRcnnv1ky43qwc2379m28VKpcjc58cycYb2myXz';
    //项目名称
    const PRODUCT_NAME = 'sszg';

    //礼包码密钥
    const APP_WEB_GIFT_KEY = 'cmRcnnv1ky43qwc2379m28VKpcjc58cycYb2myXz';
    //礼包卡地址
    const GIFT_URL_TEST = 'http://local.oss.api.shiyuegame.com:8300/index.php/card/send'; //测试
    const REG_KEY =  'e4aaea56874e41be74f22ed2332a875c';


    //日志记录开启
    const DEBUG = true;


    public static function pubKey(){
        $pubKey = '-----BEGIN PUBLIC KEY-----' . PHP_EOL . chunk_split(self::PUBLIC_KEY, 64, PHP_EOL) . '-----END PUBLIC KEY-----' . PHP_EOL;
        return $pubKey;
    }

   public static function verify($data, $sign){
       $sign = base64_decode($sign);
       $res = openssl_pkey_get_public(self::pubKey());
       $result = (bool)openssl_verify($data, $sign, $res, OPENSSL_ALGO_DSS1);
       openssl_free_key($res);
       return $result;
    }

    public static function decryptData($data)
    {
        $key = mb_substr(self::APP_SECRET, 0, 16);
        $iv = $key;
        $deData = openssl_decrypt(base64_decode($data), 'aes-128-cbc', $key, OPENSSL_NO_PADDING, $iv);
        $deData = preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', trim($deData));
        return json_decode($deData, true);
    }

    /**
     * 卡号礼包信息 返回格式
     * @param $code
     * @param $msg
     */
    public static function giftOut($code, $msg)
    {
        echo json_encode(array('code' => $code, 'msg' => $msg));
        exit;
    }

    public static function getConf()
    {
        return array(
            "7439e580e5e34e9c" => [
                "card_id" => 6038,
                "items" => [[1,1,1], [22,1,1]]
            ],
        );
    }
}