<?php
/**
 * 角色相关查询游戏接口
 * @author: heshitan
 */

class Role_GameApi extends GameApi {
   public $queryField = 'rid, srv_id, reg_channel, account, name, sex, career, lev';

    /**
     * 获取当前所有在线玩家角色id列表
     *
     * @return GameApi::ret       成功返回当前所有在线玩家角色id列表
     */
    public function getOnlineRids() {
        $ret = $this->rpc('adm', 'online_role');
        if (is_array($ret) && isset($ret[1])) {
            return $this->ret('OK', $ret);
        }
        return $this->ret('Unknow Error');
    }

    /**
     * 通过账号查询相关角色
     
     * @param string|array  $account    多个账号传入数组
     * @param string        $platform   平台标识
     * @param int           $zoneID     区号
     
     * @return array 如果游戏允许多个角色存在，将返回多条数据
     */
    public function getRoleByAccount($account, $platform = NULL, $zoneID = NULL) {
        $where = array();
        if ($platform !== NULL && $zoneID !== NULL) {
            $srv_id = $platform .'_'. $zoneID;
            $where[] = "srv_id = '{$srv_id}'";
        }

        if (is_array($account)) {
            $account = implode("', '", $account);
            $where[] = "account IN ('{$account}')";
        } else {
            $where[] = "account = '{$account}'";
        }
        return $this->_queryRole($where);
    }

    /**
     * 通过角色名查询相关角色
     
     * @param string|array  $name       多个角色传入数组
     * @param string        $platform   平台标识
     * @param int           $zoneID     区号
     
     * @return array
     */
    public function getRoleByName($name, $platform = NULL, $zoneID = NULL) {
        $where = array();
        if (is_array($name)) {
            $name = implode("', '", $name);
            $where[] = "name IN ('{$name}')";
        } else {
            $where[] = "name = '{$name}'";
        }

        if ($platform !== NULL && $zoneID !== NULL) {
            $srv_id = $platform .'_'. $zoneID;
            $where[] = "srv_id = '{$srv_id}'";
        }

        return $this->_queryRole($where);
    }

    /**
     * 通过角色ID查询相关角色
     
     * @param string|array  $rid        多个ID传入数组
     * @param string        $platform   平台标识
     * @param int           $zoneID     区号
     
     * @return array()
     */
    public function getRoleByRid($rid, $platform = NULL, $zoneID = NULL) {
        $where = array();
        if (is_array($rid)) {
            $rid = implode("', '", $rid);
            $where[] = "rid IN ('{$rid}')";
        } else {
            $where[] = "rid = '{$rid}'";
        }

        if ($platform !== NULL && $zoneID !== NULL) {
            $srv_id = $platform .'_'. $zoneID;
            $where[] = "srv_id = '{$srv_id}'";
        }

        return $this->_queryRole($where);
    }

    private function _queryRole($where) {
        $roles = Db::getInstance()->getAll('SELECT '.$this->queryField.' FROM role  WHERE '.implode(' AND ', $where));
        if ($roles) {
            return $this->ret('OK', $roles);
        } else {
            return $this->ret('Account Not Found', array());
        }
    }

    /**
     * 获取某天注册角色相关信息
     * @param string $date 正确日期格式
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getRoleByDate($date, $limit=100, $page=1) {
        $st = strtotime($date);
        $et = $st + 86400;
        $offset = ($page - 1) * $limit;
        $where = " reg_time>={$st} AND reg_time<{$et}";
        //总页数
        $totalRows = Db::getInstance()->getOne("select count(*) from role r WHERE {$where}");
        $totalPages = ceil($totalRows/$limit);

        $roles = Db::getInstance()->getAll('SELECT '.$this->queryField.' FROM role WHERE '.$where.' limit '.$offset.','.$limit);
        if ($roles) {
            return $this->ret('OK', array('roles' => $roles, 'totalPages' => $totalPages));
        } else {
            return $this->ret('Account Not Found', array());
        }
    }

    /**
     * 是否自充值账号
     * @param $account
     * @return bool
     */
    public function isChargeAccount($account)
    {
        $id = (int)Db::getInstance()->getOne("select id from mod_self_charge WHERE account='{$account}' AND status=1");
        if ($id > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取设备id
     * @param $rid
     * @param $srv_id
     * @return bool|string
     * @throws Exception
     */
    public  function getIdfa($rid, $srv_id)
    {
        $device_id = Db::getInstance()->getOne("select idfa from role where rid={$rid} and srv_id='{$srv_id}'");
        return empty($device_id) ? 'unknown' : $device_id;
    }

}
