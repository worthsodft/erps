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
use think\facade\Db;
use think\helper\Str;
use think\Model;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\UserInfoModel;
use vandles\model\UserTempModel;

/**
 * @package vandles\service
 *
 */
class UserTempService extends BaseService {
    protected static $instance;

    public static function instance(): UserTempService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        // if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $data = [
            [
                "openid" => Str::random(32),
            ], [
                "openid" => Str::random(32),
            ]
        ];

        dd('init');
    }

    /**
     * @return BaseSoftDeleteModel|UserTempModel
     */
    public function getModel() {
        return UserTempModel::mk();
    }

    public function getUserTempListByPhones(array $phones, $fields="*") {
        $query = $this->getModel()->field($fields)->whereIn("phone", $phones);
        return $query->select();
    }

    public function updateByPhone($phone, $data) {
        return $this->getModel()->where("phone", $phone)->update($data);
    }
    public function getUserTempByPhone($phone, $fields="*") {
        return $this->getModel()->field($fields)->where("phone", $phone)->find();
    }
}