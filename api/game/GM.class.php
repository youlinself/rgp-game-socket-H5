<?php
phpinfo();
/**
 * GM 相关操作游戏接口
 */
class GM_GameApi
{

    /**
     * 禁言
     *
     * @param $roles        array   玩家信息 array(array(rid|role_name, platform, zoneID), ...)
     * @param $time         int     禁言时长，单位秒
     * @param $reason       string  禁言理由
     * @param $adminName    string  禁言管理员
     * @param $notice       int     是否通知玩家，0不通知，1通知
     *
     * @return array
     */
    public function gag($roles, $time, $reason, $adminName, $notice = 1)
    {
        $type = is_int($roles[0][0]) ? 'i' : 'b';
        $type = implode(', ', array_fill(0, count($roles), "{{$type}, b, i}"));
        return erl('adm', 'silent', "[[{$type}], i, b, b, i]", array($roles, (int)$time, $reason, $adminName, $notice));
    }

    /**
     * 封号
     *
     * @param $roles        array   玩家信息 array(array(rid|role_name, platform, zoneID), ...)
     * @param $time         int     封号时长，单位秒
     * @param $reason       string  封号理由
     * @param $adminName    string  封号管理员
     *
     * @return array
     */
    public function lock($roles, $time, $reason, $adminName)
    {
        $type = is_int($roles[0][0]) ? 'i' : 'b';
        $type = implode(', ', array_fill(0, count($roles), "{{$type}, b, i}"));
        return erl('adm', 'lock', "[[{$type}], i, b, b]", array($roles, (int)$time, $reason, $adminName));
    }

    /**
     * 解除封号
     *
     * @param $roles      array   玩家信息 array(array(rid|role_name, platform, zoneID), ...)
     * @param $adminName  string  管理员名字
     *
     * @return array
     */
    public function unlock($roles, $adminName)
    {
        $type = is_int($roles[0][0]) ? 'i' : 'b';
        $type = implode(', ', array_fill(0, count($roles), "{{$type}, b, i}"));
        return erl('adm', 'unlock', "[[{$type}], b]", array($roles, $adminName));
    }

    /**
     * 解除禁言
     *
     * @param $roles      array   玩家信息 array(array(rid|role_name, platform, zoneID), ...)
     * @param $adminName  string  管理员名字
     *
     * @return array
     */
    public function ungag($roles, $adminName)
    {
        $type = is_int($roles[0][0]) ? 'i' : 'b';
        $type = implode(', ', array_fill(0, count($roles), "{{$type}, b, i}"));
        return erl('adm', 'unsilent', "[[{$type}], b]", array($roles, $adminName));
    }

    /**
     *  获取角色详情
     * @param $role_id
     * @param $platform
     * @param $zone_id
     * @return array
     */
    public function getRole($role_id, $platform, $zone_id)
    {
        $role = [
            'roles' => [(int)$role_id, $platform, (int)$zone_id]
        ];
        return erl('adm', 'auto_gifts', "[[{i,b,i}]] ", array($role));
    }

    /**
     * eYou 卡号发放接口
     * @param $role_id int 角色ID
     * @param $platform string 平台名称
     * @param $zone_id int 区分ID
     * @param $card_no string 卡号
     * @return array
     */
    public function cards($role_id, $platform, $zone_id, $card_no)
    {
        $args = [
            'rid' => (int)$role_id,
            'platform' => $platform,
            'zone_id' => (int)$zone_id,
            'card_no' => $card_no,
        ];

        return erl('adm', 'card_gifts', "[b]", array(json_encode($args)));
    }

    /**
     * 自动礼包发货接口
     * @param $role_id array 角色信息格式 [[rid|role_name, platform, zoneID], ...]
     * @param $card_id int 卡号唯一ID
     * @param card_no string 卡号
     * @param $title string 邮件标题
     * @param $content string 邮件内容
     * @param $item array  [[1, 1, 20]] 物品信息 格式: 物品ID,是否绑定,物品数量 [[item_id, bind number], …]
     * @return array
     */
    public function gifts($role_id, $card_id ,$card_no, $title, $content, $item){
        $args = [
            'roles' => [$role_id],
            'card_id' => (int)$card_id,
            'card_no' => $card_no,
            'title' => $title,
            'content' => $content,
            'items' => $item,
        ];

        return erl('adm', 'send_gift', "[b]", array(json_encode($args)));
    }

    /**
     * 代金券通知接口
     * @param $role_id array 角色信息格式 [[rid|role_name, platform, zoneID], ...]
     * @param $type int 通知类型 1: 个人通知 2:在线玩家通知
     * @return array
     */
    public function bond($role_id, $type){
        $args = [
            'roles' => [$role_id],
            'type' => (int)$type,
        ];

        return erl('adm', 'notice_bond', "[b]", array(json_encode($args)));
    }

    /**
     *
     * 获取角色周报信息接口
     * @param $role_id
     * @param $platform
     * @param $zoneId
     * @return array
     */
    public function weeklyInfo($role_id, $platform, $zoneId)
    {
        $role = [(int)$role_id, $platform, (int)$zoneId];
        return erl('adm', 'get_role_weekly_info', "[i,b,i]", $role);
    }

    /**
     * 发送周报分享接口（仅在本周周报第一次分享后调用）
     * @param $role_id
     * @param $platform
     * @param $zoneId
     * @return array
     */
    public function weeklyShare($role_id, $platform, $zoneId)
    {
        $role = [(int)$role_id, $platform, (int)$zoneId];
        return erl('adm', 'send_role_weekly_share_award', "[i,b,i]", $role);
    }

    /**
     * 发送嘉年华分享接口
     * @param $role_id
     * @param $platform
     * @param $zoneId
     * @return array
     */
    public function carnivalShare($role_id, $platform, $zoneId)
    {
        $role = [(int)$role_id, $platform, (int)$zoneId];
        return erl('adm', 'send_role_carnival_share_award', "[i,b,i]", $role);
    }

    /**
     * eyou礼包发送
     * @param $role_id
     * @param $platform
     * @param $zoneId
     * @param $packId
     * @return array
     */
    public function eYouGift($role_id, $platform, $zoneId, $orderId, $packId)
    {
        $data = [(int)$role_id, $platform, (int)$zoneId, $orderId, (int)$packId];
        return erl('adm', 'eyou_gift', "[i,b,i,b,i]", $data);
    }

}
