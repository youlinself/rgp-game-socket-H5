<?php
/**
 * Created by PhpStorm.
 * User: weiming Email: 329403630@qq.com
 * Date: 2019/5/21
 * Time: 16:06
 * 角色信息查询接口
 */

require CURR_PLATFORM_DIR. './SYGAMESDK.class.php';
//获取请求记录和日志记录
$p = API::param()->getParams();
if (SYGAMESDK::DEBUG) API::log($p, 'sygame_role_info', 'request');
//角色信息组织
$srv_id = $p['server_id'];
$account = $p['uid'];
//sign验证
if($p['sign'] != SYGAMESDK::createPaySign($p, '')) {
    API::log('sign验证错误', 'sygame_role_info', 'request');
    SYGAMESDK::outSy(-4, 'sign error');
}
//查询角色信息
$db = Db::getInstance();
$role = $db->getAll("select * FROM role WHERE srv_id= '{$srv_id}' and account = '{$account}' and acc_group in (1,3,6)");

if(!$role)
{
    SYGAMESDK::outSy(-5, '无效查询');
} else {

    $data = [];
    foreach ($role as $k => $v) {
        $data[] = [
            'role_id' => $v['rid'],
            'role_name' => $v['name'],
        ];
    }
    API::log($data, 'sygame_role_info', 'request');
    SYGAMESDK::outSy(0, '成功', $data);
}
