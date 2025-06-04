<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class OutStockModel extends BaseSoftDeleteModel {

    protected $name = 'AOutStock';

    public function getPayStatusAttr() {
        return $this->amount == $this->paid_amount;
    }
}