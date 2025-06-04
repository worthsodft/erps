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
use vandles\model\CouponTplModel;
use vandles\model\UserInfoModel;

class CouponTplService extends BaseService {
    protected static $instance;


    public static function instance(): CouponTplService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $data = [
            [
                "gid"           => uuid(),
                "title"         => "满100减20",
                "money"         => "20",
                "min_use_money" => "100",
            ],[
                "gid"           => uuid(),
                "title"         => "满100减20",
                "money"         => "20",
                "min_use_money" => "100",
            ],[
                "gid"           => uuid(),
                "title"         => "满100二折",
                "discount"      => "2",
                "min_use_money" => "100",
            ],[
                "gid"           => uuid(),
                "title"         => "满100减5",
                "money"         => "5",
                "discount"      => "2",
                "min_use_money" => "100",
            ]
        ];

        $res  = $this->getModel()->saveAll($data);
        $list = $this->getModel()->select();
        dd($list->toArray());
    }

    /**
     * @return BaseSoftDeleteModel|CouponTplModel
     */
    public function getModel() {
        return CouponTplModel::mk();
    }


}