<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CartModel;
use vandles\model\CouponTplModel;
use vandles\model\CustomerModel;
use vandles\model\InStockModel;
use vandles\model\InStockSubModel;
use vandles\model\SupplierModel;
use vandles\model\UserAddressModel;
use vandles\model\WaterStationModel;

class InStockSubService extends BaseService {
    protected static $instance;


    public static function instance(): InStockSubService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|InStockSubModel
     */
    public function getModel() {
        return InStockSubModel::mk();
    }
    public function mQuery():QueryHelper {
        return InStockSubModel::mQuery();
    }

    public function createAll(array $items) {
        return $this->getModel()->saveAll($items);
    }

    public function softDelBySn(string $sn) {
        return $this->getModel()->where(['in_stock_sn'=>$sn])->update(['deleted'=>1]);
    }

    /**
     * 周期内原材料返还数量
     * @param array $date
     * @return array
     */
    public function getProduceBackNumGroupByGoodsSn(array $date) {
        $query = $this->mQuery()->alias("a")
            ->field("a.goods_sn, a.goods_name, sum(a.goods_number) goods_number")
            ->join("a_in_stock b", "a.in_stock_sn = b.sn")
            ->whereBetween("b.create_at", $date)
            ->where("b.status", 1)
            ->where("b.in_stock_type", "produceback")
            ->group("a.goods_sn, a.goods_name");

        $list = $query->select();
        return array_column($list->toArray(), null, "goods_sn");
    }

    /**
     * 周期内生产入库的产品
     * @param array $date
     * @return QueryHelper
     */
    public function getSubsQueryByDate(array $date) {
        $query = $this->mQuery()->alias("a")
            ->field("a.goods_sn, a.goods_name, sum(a.goods_number) goods_number")
            ->join("a_in_stock b", "a.in_stock_sn = b.sn")
            ->whereBetween("b.create_at", $date)
            ->where("b.status", 1)
            ->where("b.in_stock_type", "produce")
            ->group("a.goods_sn, a.goods_name");
        return $query;
    }
    /**
     * 周期内生产入库的产品sns查询
     * @param array $date
     * @return QueryHelper
     */
    public function getSnsQueryByDate(array $date) {
        $query = $this->mQuery()->alias("a")
            ->field("a.goods_sn")
            ->join("a_in_stock b", "a.in_stock_sn = b.sn")
            ->whereBetween("b.create_at", $date)
            ->where("b.status", 1)
            ->where("b.in_stock_type", "produce")
            ->group("a.goods_sn, a.goods_name");
        return $query;
    }

    /**
     * 周期内生产入库的产品用到的原材料sns
     * @param array $date
     * @return array
     */
    public function getMaterialSnsByDate(array $date) {
        $db = InStockSubService::instance()->getSnsQueryByDate($date);
        $goodsList = GoodsService::instance()->getModel()->field("id,sn,material")->whereRaw("sn in {$db->buildSql()}")
            ->select();
        $sns = [];
        foreach ($goodsList as $vo){
            $list = json_decode($vo['material'],true);
            foreach ($list as $material){
                if(!isset($sns[$material['goods_sn']]))$sns[$material['goods_sn']] = $material['goods_sn'];
            }
        }
        $sns = array_keys($sns);
        return $sns;
    }
}