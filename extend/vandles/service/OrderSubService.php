<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\helper\Str;
use think\Model;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\GoodsModel;
use vandles\model\OrderModel;
use vandles\model\OrderSubModel;
use vandles\model\UserInfoModel;

/**
 * @package vandles\service
 *
 */
class OrderSubService extends BaseService {
    protected static $instance;


    public static function instance(): OrderSubService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $datas = [];

//        $this->getModel()->saveAll($datas);
//        $list = OrderService::instance()->getList();
//        dd($list->toArray());
    }

    /**
     * @return BaseSoftDeleteModel|OrderSubModel
     */
    public function getModel() {
        return OrderSubModel::mk();
    }

    public function getSubListByOrderSn($sn, $field="*") {
        $data = $this->getModel()->where("status", 1)->where("order_sn", $sn)
            ->field($field)
            ->select();
        return $data;
    }

    public function getSubsBySns(array $sns, $field="*") {
        return $this->getModel()->where("status", 1)
            ->field($field)
            ->whereIn("order_sn", $sns)
            ->select();
    }

    public function getSubsBySn($sn, $field="*") {
        return $this->getModel()->where("status", 1)
            ->field($field)
            ->where("order_sn", $sn)
            ->select();
    }

    public function getSubsGroupByGoodsSnBySns($sns) {
        $query = $this->getModel()->whereIn("order_sn", $sns)
            ->field("goods_sn,goods_cover,goods_name,goods_unit,sum(goods_number) goods_total")
            ->group("goods_sn,goods_cover,goods_name,goods_unit")
            ->order("goods_sn");

        $list = $query->select();
        return $list;
    }


}