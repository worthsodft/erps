<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\model\SystemUser;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\helper\Str;
use think\Model;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CouponTplModel;
use vandles\model\DepotModel;
use vandles\model\PlanModel;
use vandles\model\StaffModel;
use vandles\model\UserInfoModel;

class SystemUserService extends BaseService {
    protected static $instance;


    public static function instance(): SystemUserService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
//        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);

        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|SystemUser
     */
    public function getModel() {
        return SystemUser::mk();
    }

    public function getListInSuids(array $suids, $field="*") {
        return $this->getModel()->field($field)->whereIn('id', $suids)->select();
    }

    public function getOptsInSuids(array $suids, $field="*") {
        $list = $this->getListInSuids($suids, $field);
        return array_column($list->toArray(), null, 'id');
    }


}