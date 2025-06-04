<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 16:02
 * Email: <vandles@qq.com>
 **/

namespace vandles\model;


use think\facade\Db;
use vandles\lib\VException;

class GoodsModel extends BaseSoftDeleteModel {
    protected $name = 'AGoods';

    public function getExpireMonthsAttr() {
        return $this->expire_days / 30;
    }

    public function getUseTypeTxtAttr() {
        return config("a.giftcard_use_types." . $this->use_type);
    }

}