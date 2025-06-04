<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\helper\QueryHelper;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CartModel;
use vandles\model\CouponTplModel;
use vandles\model\UserAddressModel;

class UserAddressService extends BaseService {
    protected static $instance;


    public static function instance(): UserAddressService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $data = [
            [
                "gid"    => uuid(),
                "openid" => uuid(),
            ],[
                "gid"    => uuid(),
                "openid" => uuid(),
            ]
        ];

        $res  = $this->getModel()->saveAll($data);
        $list = $this->getModel()->select();
        dd($list->toArray());
    }

    /**
     * @return BaseSoftDeleteModel|UserAddressModel
     */
    public function getModel() {
        return UserAddressModel::mk();
    }
    public function mQuery():QueryHelper {
        return UserAddressModel::mQuery();
    }

    public function getTotalByOpenid($openid) {
        return $this->getModel()->where("status", 1)->where("openid", $openid)->count();
    }

    /**
     * 下单时，指定默认的收货地址
     * @param $openid
     */
    public function getDefaultAddressByOpenid($openid, $field = "*") {
        $address = $this->getModel()->where("openid", $openid)->where("is_default", 1)->field($field)->find();
        if ($address) return $address;
        else{
            $address = $this->getModel()->where("openid", $openid)->order("id desc")->field($field)->find();
        }
        return $address;
    }

    public function getListByOpenid($openid) {
        return $this->getModel()->where("status", 1)->where("openid", $openid)->order("sort desc, is_default desc, id desc")->select();
    }

    public function setIsDefaultBatchByOpenid($openid, int $is_default=0) {
        return $this->getModel()->where("is_default", $is_default?0:1)->where("openid", $openid)->update(["is_default" => $is_default]);
    }

    public function updateByGid($gid, array $post) {
        return $this->getModel()->where("gid", $gid)->update($post);
    }

    public function bind(array &$data, $userInfos) {
        foreach ($data as &$item) {
            $this->bindOne($item, $userInfos);
        }
    }

    public function bindOne(array &$vo, $userInfos=null) {
        if($userInfos) $vo['user'] = $userInfos[$vo['openid']]??[];

    }

}