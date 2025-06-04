<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class OutStockSubModel extends BaseSoftDeleteModel {

    protected $name = 'AOutStockSub';
}