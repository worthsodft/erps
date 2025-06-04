<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use vandles\model\BaseSoftDeleteModel;
use vandles\model\GoodsCateModel;

class GoodsCateService extends BaseService {
    protected static $instance;


    public static function instance(): GoodsCateService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|GoodsCateModel
     */
    public function getModel() {
        return GoodsCateModel::mk();
    }

    /**
     * 1. 被其他分类使用
     * 2. 被商品使用
     * @param $id
     * @return boolean
     */
    public function isUsing($ids) {
        $ids = explode(",", trim($ids, ","));
        $countUseForCate = $this->getModel()::whereIn('pid', $ids)->where('status', 1)->count();
        if($countUseForCate > 0) return true;
        foreach ($ids as $id){
            $countUseForGoods = GoodsService::instance()->getModel()->whereLike('cateids', "%,$id,%")->where('status', 1)->count();
            if($countUseForGoods > 0) return true;
        }
        return false;
    }
}