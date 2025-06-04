<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\helper\QueryHelper;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\facade\Db;
use vandles\lib\FileLock;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\UserCouponModel;

class UserCouponService extends BaseService {
    protected static $instance;

    public static function instance(): UserCouponService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $data = [
            [
                "gid"                => uuid(),
                "openid"             => uuid(),
                "coupon_tpl_gid"     => uuid(),
                "coupon_publish_gid" => uuid(),
                "fetch_openid"       => uuid(),
                "title"              => "100减20",
            ], [
                "gid"                => uuid(),
                "openid"             => uuid(),
                "coupon_tpl_gid"     => uuid(),
                "coupon_publish_gid" => uuid(),
                "fetch_openid"       => uuid(),
                "title"              => "100减10",
            ]
        ];

        $res  = $this->getModel()->saveAll($data);
        $list = $this->getModel()->select();
        dd($list->toArray());
    }

    /**
     * @return BaseSoftDeleteModel|UserCouponModel
     */
    public function getModel() {
        return UserCouponModel::mk();
    }

    public function mQuery(): QueryHelper {
        return UserCouponModel::mQuery();
    }

    public function bind(array &$list) {
        foreach ($list as &$one) {
            $this->bindOne($one);
        }
    }

    public function bindOne(&$one) {
        $one['use_scope_txt'] = (config('a.coupon_use_scopes.' . $one['use_scope']) ?: '未知');
    }

    public function getTotalByOpenid($openid) {
        return $this->getModel()->where("status", 1)->where("openid", $openid)->count();
    }

    /**
     * 根据订单金额，得到可用我的优惠券列表
     * @param $openid
     * @param $total_amount
     * @param string $field
     * @return array|Collection|Query[]|BaseSoftDeleteModel[]
     */
    public function getUsableListByOpenid($openid, $total_amount, $field = "*") {
        $query = $this->getUsableQuery($openid)->field($field);
        $query->where("min_use_money", "<=", $total_amount);
        return $query->select();
    }

    public function fetchCoupon($openid, $gid) {
        $lock = FileLock::instance("fetch_coupon_" . $gid);
        if(!$lock->lock()){
            VException::runtime("系统繁忙，请稍后重试！");
        }
        $publishService = CouponPublishService::instance();
        $coupon         = $publishService->search([
            "status" => 1,
            "gid"    => $gid,
        ])->find();
        if (empty($coupon)) VException::runtime("优惠券不存在");
        if ($coupon->has_count <= 0) VException::runtime("优惠券已领完");
        if ($coupon->is_new && !UserInfoService::instance()->isNew($openid)) {
            VException::runtime("此优惠券只能新用户领取");
        }
        if ($coupon->per_count != 0) {
            $count = $this->getFetchCount($gid, $openid);
            if ($count >= $coupon->per_count) VException::runtime("优惠券每人限领 {$coupon->per_count} 张");
        }
        $gids = $publishService->getGidsForOpenid($openid);
        // 数组键值互换
        $gids = array_flip($gids);
        if (!isset($gid, $gids)) VException::runtime("此券不在可领范围之内");

        // 领券操作
        $userCoupon = Db::transaction(function () use ($openid, $gid, $coupon) {
            // 1. 为用户增加优惠券
//            d($coupon->toArray());
            // 有效期至, 时间绝对值, 优先级高于expire_days，如果均为空，默认30天有效
            $expire_at = $coupon->expire_at ?: date("Y-m-d H:i:s", time() + ($coupon->expire_days?:30) * 86400);
            $data = [
                'gid'                => uuid(),
                'coupon_publish_gid' => $coupon->gid,
                'fetch_openid'       => $openid,
                'openid'             => $openid,
                'title'              => $coupon->title,

                'money'              => $coupon->money,
                'discount'           => $coupon->discount,
                'min_use_money'      => $coupon->min_use_money,

                'remark'             => $coupon->remark,
                'use_scope'          => $coupon->use_scope,
                'is_new'             => $coupon->is_new,

                'expire_at'          => $expire_at,
                'fetch_at'           => now(),

            ];
            $userCoupon = $this->create($data);

            // 2. 发布has_count -1
            $coupon->has_count--;
            $coupon->save();
            return $userCoupon;
        });
        $lock->unlock();
        return $userCoupon;
    }
    public function fetchCoupon_dev($openid, $gid){
        $lock = FileLock::instance("fetch_coupon_" . $gid);
        if(!$lock->lock()){
            VException::runtime("系统繁忙，请稍后重试！");
        }

        $publishService = CouponPublishService::instance();
        $coupon         = $publishService->search([
            "status" => 1,
            "gid"    => $gid,
        ])->find();
        if (empty($coupon)) VException::runtime("优惠券不存在");
        if ($coupon->has_count <= 0) VException::runtime("优惠券已领完");
        if ($coupon->is_new && !UserInfoService::instance()->isNew($openid)) {
            VException::runtime("此优惠券只能新用户领取");
        }
        if ($coupon->per_count != 0) {
            $count = $this->getFetchCount($gid, $openid);
            if ($count >= $coupon->per_count) VException::runtime("优惠券每人限领 {$coupon->per_count} 张");
        }
        $gids = $publishService->getGidsForOpenid($openid);
        // 数组键值互换
        $gids = array_flip($gids);
        if (!isset($gid, $gids)) VException::runtime("此券不在可领范围之内");

        // 领券操作
        $userCoupon = Db::transaction(function () use ($openid, $gid, $coupon) {
            // 1. 为用户增加优惠券
//            d($coupon->toArray());
            // 有效期至, 时间绝对值, 优先级高于expire_days，如果均为空，默认30天有效
            $expire_at = $coupon->expire_at ?: date("Y-m-d H:i:s", time() + ($coupon->expire_days?:30) * 86400);
            $data = [
                'gid'                => uuid(),
                'coupon_publish_gid' => $coupon->gid,
                'fetch_openid'       => $openid,
                'openid'             => $openid,
                'title'              => $coupon->title,

                'money'              => $coupon->money,
                'discount'           => $coupon->discount,
                'min_use_money'      => $coupon->min_use_money,

                'remark'             => $coupon->remark,
                'use_scope'          => $coupon->use_scope,
                'is_new'             => $coupon->is_new,

                'expire_at'          => $expire_at,
                'fetch_at'           => now(),

            ];
            $userCoupon = $this->create($data);

            // 2. 发布has_count -1
            $coupon->has_count--;
            $coupon->save();
            return $userCoupon;
        });
        $lock->unlock();
        return $userCoupon;
    }

    public function getFetchCount($gid, $openid) {
        return $this->getModel()->where("openid", $openid)->where("coupon_publish_gid", $gid)->count();
    }

    /**
     * 得到可用的优惠券
     * @param $openid
     * @param $field
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getCouponPageDataUsableByOpenid($openid, $field="*") {
        return $this->getUsableQuery($openid)->field($field)->paginate();
    }

    public function getUsableQuery($openid=null){
        $query = $this->search(['status'=>1])
            ->whereNull("use_at")
            ->where("expire_at", ">=", now())
            ->order("sort desc, id desc");
        if($openid) $query->where("openid", $openid);
        return $query;
    }

    public function isUsable($coupon, $openid=null, $goods_amount=0) {
        if(is_string($coupon)) $coupon = $this->getModel()->where("gid", $coupon)->find();
        if($openid && $coupon->openid != $openid) VException::runtime("优惠券不属于此用户");
        if(!empty($coupon->use_at) || $coupon->status != 1) VException::runtime("优惠券已被使用");
        if(strtotime($coupon->expire_at) <= time()) VException::runtime("优惠券已过期");
        if($goods_amount < $coupon->min_use_money) VException::runtime("订单金额不满足使用条件");
        // 是否新用户可用
        if(!empty($openid) && $coupon->is_new && !UserInfoService::instance()->isNew($openid)){
            VException::runtime("仅新用户可用");
        }
        return $coupon;
    }

    /**
     * 核销优惠券
     * 1. 修改券的状态、核销时间、核销订单号
     *
     * @param $coupon_gid
     * @param $gid
     * @return void
     */
    public function writeOff($coupon_gid, $order) {
        if(is_string($order)) $order = OrderService::instance()->getOneByGid($order, "sn,openid,goods_amount");
        if(!$order) VException::runtime("订单不存在");
        $coupon = $this->getModel()->where("gid", $coupon_gid)->find();
        $this->isUsable($coupon, $order->openid, $order->goods_amount);
        $coupon->status = 0;
        $coupon->use_at = now();
        $coupon->target_gid = $order->sn;
        $coupon->save();
    }

    /**
     * 反撤销优惠券
     * @param $coupon
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function unWriteOff($coupon) {
        if(is_string($coupon)) $coupon = $this->getModel()->where("gid", $coupon)->find();
        if(!$coupon) VException::runtime("优惠券不存在");
        $coupon->status = 1;
        $coupon->use_at = null;
        $coupon->target_gid = null;
        $coupon->save();
    }

    public function getMyCouponCount($openid) {
        return $this->getUsableQuery($openid)->count();
    }


}