<?php

/**
 * 充值类
 *
 */
class PayApi
{
    //角色名/角色id
    protected $idOrName;
    //平台标识
    protected $platform;
    //区号
    protected $zoneID;
    //描述 $idOrName 的类型 name/rid 之一
    protected $roleType;

    //充值类型
    const NORMAL = 1; //正常充值
    const SELF = 2; //平台自充值
    const TEST = 3; //测试充值
    const CATEGORY = [
        self::NORMAL,
        self::SELF,
        self::TEST,
    ];

    //角色类型
    const ROLE_ID = 2;
    const ROLE_NAME = 1;
    const ROLE_TYPE = [
        self::ROLE_ID => '角色ID',
        self::ROLE_NAME => '角色名',
    ];

    public function __construct($idOrName, $platform, $zoneID, $roleType = self::ROLE_ID)
    {
        $this->idOrName = $idOrName;
        $this->platform = $platform;
        $this->zoneID = $zoneID;
        $this->roleType = $roleType;
    }

    /**
     * 统一的充值方式
     *
     * @param string $orderId 订单号
     * @param float $amount 金额  单位 元
     * @param string $currencyType 货币类型 (ISO 4217标准)
     * @param int $category 充值类型 1正常充值 2平台自充值  3测试充值
     * @param array $ext 自定义扩展字段
     * @return array
     */
    public function pay($orderId, $amount,  $ext = [], $category = 1, $currencyType = 'CNY')
    {
        $orderId = trim($orderId);
        if ($orderId === '') return API::returnMsg('INVALID_ORDER', '无效的订单号');
        //判断订单号是否存在
        if (Db::getInstance()->getOne("select sn from mod_charge where sn='{$orderId}'"))
        {
            return API::returnMsg('ORDER_EXISTS', '订单号已存在');
        }

        if (!is_numeric($amount) || (float)$amount <= 0) return API::returnMsg('INVALID_AMOUNT', '无效的金额');
        $amount = (float)$amount;
        if (!in_array($category, self::CATEGORY)) API::returnMsg('CATEGORY_ORDER', '无效的充值类型');
        $category = (int)$category;

        $type = is_int($this->idOrName) && $this->roleType == self::ROLE_ID ? 'i' : 'b';

        $ret = erl('adm', 'pay', "[{$type}, i, b, i, b, i, b, i, b]", [
            $this->idOrName,
            $this->roleType,
            $this->platform,
            $this->zoneID,
            $orderId,
            $amount,
            $currencyType,
            $category,
            json_encode($ext),
        ]);

        if ($ret['success']) {
            return API::returnMsg('SUCCESS', $ret['message']);
        } else {
            return API::returnMsg('ORDER_HANDLE_FAILURE', $ret['message']);
        }
    }
}
