<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CartModel;
use vandles\model\CouponTplModel;
use vandles\model\ServiceMenuModel;

class ServiceMenuService extends BaseService {
    protected static $instance;

    private static $AUTH_FINISH_ORDER = 1; // 订单核销权限
    private static $AUTH_DELIVER_ORDER = 2; // 订单配送权限
    private static $AUTH_DEMO = 99; // demo权限


    public static function instance(): ServiceMenuService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);

        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|ServiceMenuModel
     */
    public function getModel() {
        return ServiceMenuModel::mk();
    }

    public function getListWithOpenid($openid) {
        $query = $this->getModel()->where("status", 1)->order("sort desc, id desc");
        $userAuth = UserInfoService::instance()->getModel()->where("openid", $openid)->value("auth");
        if (!empty($userAuth)) {
            $query->where(function ($query) use($userAuth,$openid) {
                $query->where("auth", 0);
                if ((UserInfoService::$AUTH_FINISH_ORDER & $userAuth) == UserInfoService::$AUTH_FINISH_ORDER) {
                    $query->whereOr("auth", self::$AUTH_FINISH_ORDER);
                }
                if ((UserInfoService::$AUTH_DELIVER_ORDER & $userAuth) == UserInfoService::$AUTH_DELIVER_ORDER) {
                    $query->whereOr("auth", self::$AUTH_DELIVER_ORDER);
                }
                if($openid == "obzAZ7c85NvxGhEXyOEXnP6uY0JM" && config("a.is_show_demo")){
                    $query->whereOr("auth", self::$AUTH_DEMO);
                }
            });
        }elseif($openid == "obzAZ7c85NvxGhEXyOEXnP6uY0JM" && config("a.is_show_demo")){
            $query->where(function ($query) {
                $query->where("auth", 0)->whereOr("auth", self::$AUTH_DEMO);
            });
        }else{
            $query->where("auth", 0);
        }
        return $query->select();
    }


}