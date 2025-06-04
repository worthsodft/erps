<?php

namespace vandles\model;

use think\facade\Db;
use think\helper\Str;
use vandles\lib\VException;

class InStockSubModel extends BaseSoftDeleteModel {

    protected $name = 'AInStockSub';
}