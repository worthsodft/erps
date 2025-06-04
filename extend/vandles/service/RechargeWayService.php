<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use vandles\model\BaseSoftDeleteModel;
use vandles\model\CartModel;
use vandles\model\CouponTplModel;
use vandles\model\RechargeWayModel;
use vandles\model\UserAddressModel;

class RechargeWayService extends BaseService {
    protected static $instance;


    public static function instance(): RechargeWayService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $data = [
            [
                "gid"        => uuid(),
                "money"      => 100,
                "give_money" => 20,
            ], [
                "gid"        => uuid(),
                "money"      => 200,
                "give_money" => 50,
            ], [
                "gid"        => uuid(),
                "money"      => 500,
                "give_money" => 120,
            ], [
                "gid"        => uuid(),
                "money"      => 1000,
                "give_money" => 300,
            ]
        ];

        $res  = $this->getModel()->saveAll($data);
        $list = $this->getModel()->select();
        dd($list->toArray());
    }

    /**
     * @return BaseSoftDeleteModel|RechargeWayModel
     */
    public function getModel() {
        return RechargeWayModel::mk();
    }

    public function getOpts() {
        $list = $this->getModel()->field("id,gid,money,give_money")->select()->each(function(&$item){
            $item->title = "充" . floatval($item['money']) . "送" . floatval($item['give_money']) . "元";
        });
        return array_column($list->toArray(), null, "gid");
    }


}