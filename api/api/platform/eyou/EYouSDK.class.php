<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2018/1/15
 * Time: 17:00
 */
class EYouSDK {
    private $hex_iv = '00000000000000000000000000000000';
    //AES密钥
    private $key = 'LeNiu8810x.Qz';
    //服务器host
    private $server_host = '';
    //韩版
    const KR_SECRET_KEY = 'sf78qBoaAjzrhfc6SPUbTQ==';
    //英文版
    const EN_SECRET_KEY = '1KQcuCHZuHpklMYqRUGCwQ==';
    //日志记录开启
    const DEBUG  = true;
    //是否测试
    const IS_TEST = false;

    private $tw_tax_pid = [
        '3'=> 10003,
        '4'=> 10004,
        '5'=> 10005,
        '6'=> 10006,
        '7'=> 10007,
        '8'=> 10008,
        '20'=> 10020,
        '21'=> 10021,
        '22'=> 10022,
        '23'=> 10023,
        '24'=> 10024,
        '25'=> 10025,
        '26'=> 10026,
        '27'=> 10027,
        '28'=> 10028,
        '29'=> 10029,
        '30'=> 10030,
        '51'=> 10051,
        '101'=> 10101,
        '102'=> 10102,
        '103'=> 10103,
        '201'=> 10201,
        '202'=> 10202,
        '203'=> 10203,
        '204'=> 10204,
        '205'=> 10205,
        '206'=> 10206,
        '207'=> 10207,
        '211'=> 10211,
        '212'=> 10212,
        '213'=> 10213,
        '214'=> 10214,
        '215'=> 10215,
        '216'=> 10216,
        '217'=> 10217,
        '218'=> 10218,
        '219'=> 10219,
        '220'=> 10220,
        '301'=> 10301,
        '302'=> 10302,
        '303'=> 10303,
        '304'=> 10304,
        '305'=> 10305,
        '306'=> 10306,
        '307'=> 10307,
        '308'=> 10308,
        '309'=> 10309,
        '401'=> 10401,
        '402'=> 10402,
        '403'=> 10403,
        '404'=> 10404,
        '405'=> 10405,
        '406'=> 10406,
        '411'=> 10411,
        '412'=> 10412,
        '413'=> 10413,
        '414'=> 10414,
        '415'=> 10415,
        '416'=> 10416,
        '417'=> 10417,
        '501'=> 10501,
        '502'=> 10502,
        '503'=> 10503,
        '601'=> 10601,
        '602'=> 10602,
        '603'=> 10603,
        '604'=> 10604,
        '605'=> 10605,
        '606'=> 10606,
        '607'=> 10607,
        '701'=> 10701,
        '702'=> 10702,
        '703'=> 10703,
        '704'=> 10704,
        '705'=> 10705,
        '706'=> 10706,
        '707'=> 10707,
        '708'=> 10708,
        '709'=> 10709,
        '710'=> 10710,
        '711'=> 10711,
        '712'=> 10712,
        '713'=> 10713,
        '714'=> 10714,
        '721'=> 10721,
        '722'=> 10722,
        '725'=> 10725,
        '726'=> 10726,
        '727'=> 10727,
        '728'=> 10728,
        '801'=> 10801,
        '802'=> 10802,
        '803'=> 10803,
        '804'=> 10804,
        '805'=> 10805,
        '806'=> 10806,
        '807'=> 10807,
        '808'=> 10808,
        '901'=> 10901,
        '902'=> 10902,
        '1001'=> 11001,
        '1002'=> 11002,
        '1003'=> 11003,
        '1004'=> 11004,
        //非台湾地区
        '10003'=> 3,
        '10004'=> 4,
        '10005'=> 5,
        '10006'=> 6,
        '10007'=> 7,
        '10008'=> 8,
        '10020'=> 20,
        '10021'=> 21,
        '10022'=> 22,
        '10023'=> 23,
        '10024'=> 24,
        '10025'=> 25,
        '10026'=> 26,
        '10027'=> 27,
        '10028'=> 28,
        '10029'=> 29,
        '10030'=> 30,
        '10051'=> 51,
        '10101'=> 101,
        '10102'=> 102,
        '10103'=> 103,
        '10201'=> 201,
        '10202'=> 202,
        '10203'=> 203,
        '10204'=> 204,
        '10205'=> 205,
        '10206'=> 206,
        '10207'=> 207,
        '10211'=> 211,
        '10212'=> 212,
        '10213'=> 213,
        '10214'=> 214,
        '10215'=> 215,
        '10216'=> 216,
        '10217'=> 217,
        '10218'=> 218,
        '10219'=> 219,
        '10220'=> 220,
        '10301'=> 301,
        '10302'=> 302,
        '10303'=> 303,
        '10304'=> 304,
        '10305'=> 305,
        '10306'=> 306,
        '10307'=> 307,
        '10308'=> 308,
        '10309'=> 309,
        '10401'=> 401,
        '10402'=> 402,
        '10403'=> 403,
        '10404'=> 404,
        '10405'=> 405,
        '10406'=> 406,
        '10411'=> 411,
        '10412'=> 412,
        '10413'=> 413,
        '10414'=> 414,
        '10415'=> 415,
        '10416'=> 416,
        '10417'=> 417,
        '10501'=> 501,
        '10502'=> 502,
        '10503'=> 503,
        '10601'=> 601,
        '10602'=> 602,
        '10603'=> 603,
        '10604'=> 604,
        '10605'=> 605,
        '10606'=> 606,
        '10607'=> 607,
        '10701'=> 701,
        '10702'=> 702,
        '10703'=> 703,
        '10704'=> 704,
        '10705'=> 705,
        '10706'=> 706,
        '10707'=> 707,
        '10708'=> 708,
        '10709'=> 709,
        '10710'=> 710,
        '10711'=> 711,
        '10712'=> 712,
        '10713'=> 713,
        '10714'=> 714,
        '10721'=> 721,
        '10722'=> 722,
        '10725'=> 725,
        '10726'=> 726,
        '10727'=> 727,
        '10728'=> 728,
        '10801'=> 801,
        '10802'=> 802,
        '10803'=> 803,
        '10804'=> 804,
        '10805'=> 805,
        '10806'=> 806,
        '10807'=> 807,
        '10808'=> 808,
        '10901'=> 901,
        '10902'=> 902,
        '11001'=> 1001,
        '11002'=> 1002,
        '11003'=> 1003,
        '11004'=> 1004,
    ];

    //eyou $game_id SDK文档内 ClientId
    public static $game_id = array(
        'kokr'=> array(8003100),
        'eyouen'=> array(1079400, 1079440),
        'tw'=> array(1079100, 1079140),
        'taiguo'=> array(1079700, 1079750),
        'idn'=> array(1079900),
    );

    public function __construct()
    {
        $this->key = hash('sha256', $this->key, TRUE);
        $this->server_host = self::DEBUG ? 'http://1020apitest.eyougame.com/' : 'http://1020api.eyougame.com/';
    }

    /**
     * 获取游戏参数
     * @param $gameId //游戏编号
     * @return string
     */
    public function getAppKey($gameId)
    {
        $gameId = empty($gameId) ?  '' : $gameId;
        switch ($gameId) {
            case '8003100':
                return self::KR_SECRET_KEY;
                break;
            case '1079400':
                return self::EN_SECRET_KEY;
                break;
            default:
                return self::EN_SECRET_KEY;
        }
    }

    /**
     * @param $gold
     * @return array
     */
    public function checkTaxGold($gameId, $packId, $tax)
    {
        $packId = (string)$packId; //强制转成string
        if(in_array($gameId, EYouSDK::$game_id['tw']) && in_array($packId, array_keys($this->tw_tax_pid))) {

            if((int)$tax === 2 && $packId < 10000) {
                $packId = $this->tw_tax_pid[$packId];
            } elseif ((int)$tax === 0 && $packId > 10000) {
                $packId = $this->tw_tax_pid[$packId];
            }

            return ['success' => true, 'package_id' => (int)$packId];
        } else {
            return ['success' => true, 'package_id' => (int)$packId];
        }
    }

    /**
     * @param $game_id
     * @param $account
     * @return string
     */
    public function setPayAcc($game_id, $account, $platform)
    {
        //英文测试
        if(in_array($platform, ['eyouentest'])) {
            return $account = $platform.'_'.$account;
        }

        $acc = $account;
        foreach (EYouSDK::$game_id as $name => $gameIds) {
            if(in_array($game_id, $gameIds)) {
                $acc = $name .'_'. $account;
                continue;
            }
        }

        return $acc;
    }



    /**
     * 签名算法
     * @param array $dataArr 参与签名的数组
     * @param string $gameId 游戏ID
     * @return string 签名结果
     */
    public function createSign($dataArr, $gameId)
    {
        $secretKey = self::getAppKey($gameId);
        $signature = '';
        unset($dataArr['sign']);
        Ksort($dataArr);
        foreach ($dataArr as $value) {
            $signature .= trim($value);
        }
        $signature .= $secretKey;
        $signature = md5($signature);
        return $signature;
    }

    /**
     * 解析eyou  Orderinfo加密信息
     * @param $data
     * @return array
     */
    public function getPayParams($data)
    {
        $str = $this->decrypt($data);
        if(!$str) $this->out(array('Success'=>0,'Reason'=>'解密数据失败'));
        $params = array();
        array_map(function($v)use(&$params){$exp = explode('=', $v);$params[$exp[0]]=$exp[1];}, explode("&", $str));
        return $params;
    }

    /**
     * AES加密
     * @param string $str 加密内容
     * @return string
     */
    public function encrypt($str)
    {
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, $this->key, $this->hexToStr($this->hex_iv));
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($str) % $block);
        $str .= str_repeat(chr($pad), $pad);
        $encrypted = mcrypt_generic($td, $str);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return base64_encode($encrypted);
    }

    /**
     * AES解密
     * @param string $code 内容
     * @return bool|string
     */
    public function decrypt($code)
    {
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, $this->key, $this->hexToStr($this->hex_iv));
        $str = mdecrypt_generic($td, base64_decode($code));
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $this->strippadding($str);
    }

    public function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }

    public function out($msg = '') {
        echo json_encode($msg);
        exit;
    }

    public function role_out($code, $msg, $lev, $money, $currency) {
        echo json_encode(array('Code' => $code, 'Reason' => $msg, 'onlinetime' => 0, 'level' => $lev, 'total_amount' => $money, 'currency' => $currency));
        exit;
    }

    private function strippadding($string)
    {
        $slast  = ord(substr($string, -1));
        $slastc = chr($slast);
        $pcheck = substr($string, -$slast);
        //if (preg_match("/$slastc{".$slast."}/", $string)) {
        if (preg_match('/'.$slastc.'{'.$slast.'}/', $string)) {
            $string = substr($string, 0, strlen($string) - $slast);
            return $string;
        } else {
            return false;
        }
    }

    /**
     * 获取跨服主机地址
     * @param int $zone_id 服务器区号 一般是平台那边传过来的参数
     * @param string $platform 平台
     * @return string
     */
    public function getUrlHost($zone_id, $platform = '')
    {
        $zone_id = (int)$zone_id;
        return "s{$zone_id}-{$platform}-sszgshiyue.eyougame.com";
    }

}