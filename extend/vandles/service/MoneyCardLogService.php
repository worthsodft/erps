<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\helper\Str;
use vandles\lib\pay\WxPay;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CartModel;
use vandles\model\CouponTplModel;
use vandles\model\MoneyCardLogModel;
use vandles\model\MoneyCardModel;
use vandles\model\RechargeWayModel;
use vandles\model\UserAddressModel;

class MoneyCardLogService extends BaseService {
    protected static $instance;


    public static function instance(): MoneyCardLogService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);

        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|MoneyCardLogModel
     */
    public function getModel() {
        return MoneyCardLogModel::mk();
    }

}