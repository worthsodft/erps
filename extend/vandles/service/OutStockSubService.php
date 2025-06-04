<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\helper\QueryHelper;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\OutStockSubModel;

class OutStockSubService extends BaseService {
    protected static $instance;


    public static function instance(): OutStockSubService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|OutStockSubModel
     */
    public function getModel() {
        return OutStockSubModel::mk();
    }
    public function mQuery():QueryHelper {
        return OutStockSubModel::mQuery();
    }

    public function createAll(array $items) {
        return $this->getModel()->saveAll($items);
    }

    public function softDelBySn(string $sn) {
        return $this->getModel()->where(['out_stock_sn'=>$sn])->update(['deleted'=>1]);
    }

    /**
     * 周期内原材料申领数量
     * @param $data
     * @param array $date
     */
    public function getProduceGetNumGroupByGoodsSn(array $date) {
        $query = $this->mQuery()->alias("a")
            ->field("a.goods_sn, a.goods_name, sum(a.goods_number) goods_number")
            ->join("a_out_stock b", "a.out_stock_sn = b.sn")
            ->whereBetween("b.create_at", $date)
            ->where("b.status", 1)
            ->where("b.out_stock_type", "produceget")
            ->group("a.goods_sn, a.goods_name");

        $list = $query->select();
        return array_column($list->toArray(), null, "goods_sn");
    }


}