<?php
/**
 * 接口常用方法类
 */

class API {
    private static $_apiParam;

    public static function param() {
        if (!self::$_apiParam) self::$_apiParam = new APIParams();
        return self::$_apiParam;
    }

    /**
     * 检查提供给平台的接口的公共参数
     *
     * @param array     $checkParams    检查附带在请求中参数，指定参数 account, z, ts, ticket
     * @param bool      $checkIP        是否检查来源IP
     * @param string    $encryptKey     加密密钥，默认使用登录接口使用的密钥，支付接口密钥和其他接口密钥不一样
     *
     * 检查不通过直接输出错误并中断脚本
     * @return array 检查通过返回帐号和区号参数
     */
    public static function checkComParam($checkParams = array('account', 'z', 'ts', 'ticket'), $checkIP = TRUE, $encryptKey = SERVER_KEY) {
        if ($checkIP) {
            if (!self::isTrustIP()) self::out('IP_NOT_ALLOW', '拒绝该IP请求');
        }

        $ret = array();
        $p = self::param()->getParams();

        if (in_array('account', $checkParams)) {
            if (!isset($p['account']) || trim($p['account']) === '') self::out('INCORRECT_ACCOUNT', '帐号参数错误');
            $ret['account'] = trim($p['account']);
        }
        if (in_array('z', $checkParams)) {
            if (!isset($p['z']) || !is_numeric($p['z']) || (int)$p['z'] < 0) self::out('INCORRECT_ZONEID', '区号参数错误');
            $ret['z'] = (int)$p['z'];
        }
        if (in_array('ts', $checkParams)) {
            if (!isset($p['ts']) || self::isExpire((int)$p['ts'])) self::out('REQUEST_EXPIRE', '请求已过期，请重新发起请求');
            $ret['ts'] = (int)$p['ts'];
        }
        if (in_array('ticket', $checkParams)) {
            if (!isset($p['ticket']) || $p['ticket'] !== self::encryptTicket($encryptKey)) self::out('INCORRECT_TICKET', '签名验证失败');
            $ret['ticket'] = $p['ticket'];
        }

        return $ret;
    }

    /**
     * 检查来源IP是否可信任
     * 注：当IP白名单为空时，默认所有来源可信
     *
     * @param string $serverIP 来源服务器IP
     *
     * @return boolean
     */
    public static function isTrustIP($serverIP = NULL) {
        $ip = $serverIP ? $serverIP : clientIp();
        $ipWhiteList = &$GLOBALS['cfg']['pay']['allow_ips'];
        if ($ipWhiteList) {
            return in_array($ip, $ipWhiteList);
        } else {
            return TRUE;
        }
    }


    //用于判断请求是否过期
    public static function isExpire($ts) {
        return TIME - (int)$ts > $GLOBALS['cfg']['ticket_lifetime'];
    }

    /**
     * 生成 ticket
     *
     * @param string $encryptKey 密钥
     * @param array  $params     参与加密的参数，不传默认请求中所有出 ticket 参数都参与hash计算
     *
     * @return string
     */
    public static function encryptTicket($encryptKey, $params = array()) {
        $params = $params ? $params : self::param()->getParams();
        unset($params['ticket']);
        ksort($params);

        $queryParams = array();
        foreach ($params as $_k => $_v) {
            array_push($queryParams, $_k . '=' . $_v);
        }

        $queryString = rawurlencode(implode('&', $queryParams));
        DEBUG && recoder('加密前：'.$queryString.$encryptKey);
        $ret = md5($queryString.$encryptKey);
        DEBUG && recoder('加密后：'.$ret);
        return $ret;
    }

    /**
     * 统一输出格式
     *
     * @param string $error  错误代码，一般大写，正确信息为 OK
     * @param string $msg    错误信息，具体错误信息，正确信息为空
     * @param mixed  $data   额外数据
     */
    public static function out($error = 'OK', $msg = '', $data = NULL) {
        echo json_encode(self::returnMsg($error, $msg, $data));
        exit;
    }

    /**
     * 统一输出lua数组格式
     *
     * @param string $error  错误代码，一般大写，正确信息为 OK
     * @param string $msg    错误信息，具体错误信息，正确信息为空
     * @param mixed  $data   额外数据
     */
    public static function outlua($error = 'OK', $msg = '', $data = NULL){
        echo luatable_encode(self::returnMsg($error, $msg, $data));
        exit;
    }

    /**
     * 统一返回格式
     *
     * @param string $error
     * @param string $msg
     * @param mixed  $data
     *
     * @return array
     */
    public static function returnMsg($error = 'OK', $msg = '', $data = NULL) {
        $ret = array('error' => $error, 'msg' => $msg);
        if ($data !== NULL) $ret['data'] = $data;
        return $ret;
    }

    /**
     * @static
     * 获取一个URL地址返回的内容
     * @param string $url
     * @param array $other_curl_opt 设置CURL选项
     * @param int &$http_code 返回http code
     * @param string $error
     * @return mixed 成功则返回string，否则返回false 或者错误信息
     */
    public static function fetch($url, $other_curl_opt = array(), &$http_code = 0, &$error = '')
    {
        $curl_opt = array(
            CURLOPT_URL => $url,
            CURLOPT_AUTOREFERER => true, //自动添加referer链接
            CURLOPT_RETURNTRANSFER => true, //true: curl_exec赋值方式，false：curl_exec直接输出结果
            CURLOPT_FOLLOWLOCATION => false, //自动跟踪301,302跳转
            CURLOPT_SSL_VERIFYPEER => false, //兼容https的服务器
            CURLOPT_SSL_VERIFYHOST  => false,
            //CURLOPT_HTTPGET => TRUE, //默认为GET，无需设置
            //CURLOPT_POST => TRUE,
            //CURLOPT_POSTFIELDS => 'username=abc&passwd=bcd',//也可以为数组array('username'=>'abc','passwd'=>'bcd')
            CURLOPT_CONNECTTIMEOUT => 5, //秒
            CURLOPT_USERAGENT => 'JecSpider Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)',
            //CURLOPT_COOKIE => '',
        );

        if($other_curl_opt)
        foreach ($other_curl_opt as $key => $val)
            $curl_opt[$key] = $val;

        //curl传数组时，组建URL不正确，经常有些奇怪的问题导致无法正常请求
        if(isset($other_curl_opt[CURLOPT_POSTFIELDS]) && is_array($other_curl_opt[CURLOPT_POSTFIELDS]))
            $curl_opt[CURLOPT_POSTFIELDS] = http_build_query($other_curl_opt[CURLOPT_POSTFIELDS]);

        $ch = curl_init();
        curl_setopt_array($ch, $curl_opt);
        $contents = curl_exec($ch);
        if($contents === false) $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $contents;
    }

    /**
     * 获取跨服主机地址
     * @param int $zone_id 服务器区号 一般是平台那边传过来的参数
     * @param string $platform 平台
     * @return string
     */
    public static function getCrossHost($zone_id, $platform = '')
    {
        $zone_id = (int)$zone_id;
        if(!$platform)
        {
            $localhost = $GLOBALS['cfg']['host'];
            //替换第一个点号前的数字
            return preg_replace('/(\d+)\./', "{$zone_id}.", $localhost, 1);
        } elseif ($platform == 'sygame') {
            return "s{$zone_id}.{$platform}.zsry.shiyuegame.com";
        } else {
            return "s{$zone_id}.{$platform}.huanxiang.shiyuegame.com";
        }
    }

    /**
     * 获取跨服主机地址
     * @param int $zone_id 服务器区号 一般是平台那边传过来的参数
     * @param string $platform 平台
     * @return string
     */
    public static function getUrlHost($zone_id, $platform = '')
    {
        $zone_id = (int)$zone_id;
        if(!$platform)
        {
            $localhost = $GLOBALS['cfg']['host'];
            if (strstr($localhost, '-')) {
                return preg_replace('/(\d+)\-/', "{$zone_id}-", $localhost, 1);
            } else {
                //替换第一个点号前的数字
                return preg_replace('/(\d+)\./', "{$zone_id}.", $localhost, 1);
            }
        } else {
            return "s{$zone_id}-{$platform}-sszg.shiyuegame.com";
        }
    }

    /**
     * 调用远程单服接口
     * @param string $url 请求地址
     * @param array|string $params
     * @param int $retry_num 重试次数
     * @param array $other_curl_opt 设置CURL选项
     * @return mixed
     */
    public static function callRemoteApi($url, $params = array(), $retry_num = 1, $other_curl_opt = array()) {
        $query = is_array($params) ? http_build_query($params) : $params;
        $curl_opt = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $query, //注意CURL无法转换数组成为name[]=value&这种格式
            CURLOPT_TIMEOUT => 5,
        );
        if($other_curl_opt) {
            foreach ($other_curl_opt as $key => $val)
            $curl_opt[$key] = $val;
        }

        $ret = false;
        $error = '';
        $http_code = 0;

        //重试次数
        for($i = 0; $i < $retry_num; $i++) {
            $ret = self::fetch($url, $curl_opt, $http_code, $error);
            if($http_code == 200) break;
        }

        if(false === $ret || !empty($error) || $http_code != 200)
        {
            return array(
                'result' => false,
                'msg' => sprintf("[HTTP CODE: %s ]远程返回数据异常：%s",$http_code, $error),
            );
        } else {
            return array(
                'result' => true,
                'msg' => $ret,

            );
        }
    }

    /**
     * 接口日志记录
     * @param mixed $msg 信息
     * @param string $file 文件名
     * @param string $type 记录日志类型
     */
    public static function log($msg, $file, $type)
    {
        if (is_array($msg) || is_object($msg)) {
            $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
        $date =  date('Y-m-d H:i:s', time());
        list($y, $m, $d) = explode('-', date('Y-m-d', time()));
        $dir = VAR_DIR."/log/{$y}_{$m}/";
        if (!is_dir($dir)) {
            $old = umask(0);
            mkdir($dir, 0777, true);
            umask($old);
        }

        $log_file = $dir. $d . "_" . $file . ".log";
        $log_line = "[{$date}] [{$type}] {$msg}\n";
        file_put_contents($log_file, $log_line, FILE_APPEND);
    }


    /**
     * 获取扩展透传参数
     * @param $data
     * 扩展透传参数格式：rid$$platform$$$$zone_id$$渠道$$礼包id$$购买描述$$包标签
     * @return mixed
     */
    public static function getExt($data)
    {
        $arr =explode('$$',  $data);

        $ext['rid']          = (int)$arr[0];
        $ext['platform']     = (string)$arr[1];
        $ext['zone_id']      = (int)$arr[2];
        $ext['channel']      = (string)$arr[3];
        $ext['package_id']   = (int)$arr[4];
        $ext['package_name'] = (string)$arr[5];
        $ext['cps']          = (string)$arr[6];

        return $ext;
    }

    /**
     * 充值额外参数
     * @param $info
     * @param string $payChannel
     * @return array
     */
    public static function setPayExt($info, $payChannel = '未知渠道')
    {
        //充值额外参数
        $ext = [
            'pay_channel'  => empty($payChannel) ? $info['pay_channel'] : $payChannel,
            'channel'      => $info['channel'],
            'package_id'   => (int)$info['package_id'],
            'package_name' => $info['package_name'],

        ];

        return $ext;
    }

    /**
     * 充值账号检查
     * @param $rid //角色ID
     * @param $platform //平台标识
     * @param $zoneId //角色区服ID
     * @param $acc //充值账号
     * @return bool
     */
    public static function checkPayAccount($rid, $platform, $zoneId, $acc)
    {
        if(empty($acc)) return false;

        // 充值帐号判断
        $roles = GameApi::call('Role')->getRoleByRid($rid, $platform, $zoneId);
        if ($roles['error'] !== 'OK') {
            API::log($rid.'_'.$platform.'_'.$zoneId.'角色获取错误', 'check_account_pay', 'error');
            return false;
        }
        $role = $roles['data'][0];
        if (empty($role)) {
            API::log($rid.'_'.$platform.'_'.$zoneId.'角色信息为空', 'check_account_pay', 'error');
            return false;
        }
        $account = $role['account'];

        if ($acc != $account) {
            API::log($rid.'_'.$platform.'_'.$zoneId.'帐号ID验证错误,订单帐号ID:'. $acc . ' 角色渠道帐号ID:'. $account, 'check_account_pay', 'error');
            return false;
        }

        return true;
    }
}

class APIParams {
    private $_p;

    public function __construct() {
        foreach (array_merge($_POST, $_GET) as $_k => $_v) {
            $this->_p[$_k] = $_v;
        }
    }

    public function getParams() {
        return $this->_p;
    }

    public function has($key) {
        return isset($this->_p[$key]);
    }

    public function getInt($key, $default = 0) {
        return isset($this->_p[$key]) ? (int)$this->_p[$key] : (int)$default;
    }

    public function getString($key, $default = '') {
        return isset($this->_p[$key]) ? trim($this->_p[$key]) : trim($default);
    }
}
