<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2017/8/14
 * Time: 10:22
 * 热云上报 辅助类
 */

class ReYunSDK {
    /**
     * 检验标签是否需要上报热云
     * @param $cps //游戏透传标签
     * @return bool
     */
    public static function checkCPS($cps){
        //需要上报的游戏标签，统一在这边添加描述
        $data = [
            //我方SDK标签，具体标签名称，请查看对应接口
            'gmry_10001',
        ];

        if (in_array($cps, $data)){
            return true;
        } else {
            return false;
        }
    }

    /**
     * 充值统计上报热云
     * @param int $rid 角色ID
     * @param string $srv_id 服务器ID
     * @param string $order_no 订单号
     * @param float $amount 金额
     * @param string $device_id 用户设备的身份信息
     * @param string $app_key 游戏应用ID
     * @throws Exception
     */
    public static function reYunReport($rid, $srv_id, $order_no, $amount, $app_key)
    {
        //获取角色信息
        $api = GameApi::call('Role');
        $context = [
            '_currencyAmount' => $amount,
            '_currencyType' => 'CNY',
            '_deviceid' => $api->getIdfa($rid, $srv_id),
            '_paymentType' => 'unknown',
            '_transactionId' => $order_no,
        ];

        $account = Db::getInstance()->getOne("select account from role where rid={$rid} and srv_id='{$srv_id}'");
        $who = explode('_', $account);
        $report = array(
            'appid' => trim($app_key),
            'context' => $context,
            'who' => trim(end($who)),
        );

        //热云上报数据日志记录
        API::log($report, 'reyun_report', 'request');

        //数组转换为json格式
        $report_json = json_encode($report);

        $other_curl_opt = array(
            CURLOPT_HTTPHEADER => array("Content-type:application/json;charset=\"utf-8\""),
        );

        $url = "http://log.reyun.com/receive/tkio/payment";
        $ret = API::callRemoteApi($url, $report_json, 2, $other_curl_opt);
        API::log($ret, 'reyun_report', 'report_result');
    }

    /**
     * 获取热云上报参数
     * @param $cps //透传渠道名称
     * @return string
     */
    public static function getReYunAppKey($cps)
    {
        $cps = empty($cps) ? '' : $cps;
        switch ($cps) {
            //我方自己，具体标签名称，请查看对应接口
            case 'gmry_10001':
                return "a0beefefc5a940513b7c3159aac47209";
                break;

            default: //默认没有返回空
                return '';
        }
    }


}
