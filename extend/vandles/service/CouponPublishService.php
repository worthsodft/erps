<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\helper\Str;
use think\Model;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CouponPublishModel;
use vandles\model\CouponTplModel;
use vandles\model\UserInfoModel;

class CouponPublishService extends BaseService {
    protected static $instance;


    public static function instance():CouponPublishService {
        if(!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false){
        if($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $data = [
            [
                "gid" => guid(),
                "title" => "asdf",
            ],[
                "gid" => guid(),
                "title" => "1234",
            ]
        ];

        $res = $this->getModel()->saveAll($data);
        $list = $this->getModel()->select();
        dd($list->toArray());
    }
    /**
     * @return BaseSoftDeleteModel|CouponPublishModel
     */
    public function getModel() {
        return CouponPublishModel::mk();
    }

    public function bind(&$list) {
        foreach ($list as &$one){
            $this->bindOne($one);
        }
    }
    public function bindOne(&$one) {
        $one['use_scope_txt'] = (config('a.coupon_use_scopes.'.$one['use_scope'])?:'未知');
        if(empty($one['expire_at']) && $one['expire_days'] > 0)
            $one['expire_at'] = date("Y-m-d H:i:s", time() + $one['expire_days'] * 86400);

    }

    public function getListForFetch($field="*") {
        $list = $this->getModel()->field($field)
            ->where('status', 1)
            ->whereRaw("has_count > 0")
            ->order('sort desc,id desc')
            ->select();
        $this->bind($list);
        return $list;
    }
    public function getListForFetchWithHasCount0($field="*") {
        $list = $this->getModel()->field($field)
            ->where('status', 1)
            ->order('sort desc,id desc')
            ->select();
        $this->bind($list);
        return $list;
    }

    /**
     * 我可领取的优惠券发布
     * 1. has_count > 0的
     * 2. fetch_openids 为空的
     * 3. fetch_openids 包含我的openid
     * 4. 如果我是新用户（不存在已支付订单的）
     * @param $openid
     * @param string $field
     * @return array|Collection|BaseSoftDeleteModel[]|CouponPublishModel[]
     */
    public function getListForOpenid($openid, $field="*") {
        // 已支付订单数量，用来判断是否是新用户
        $paidOrderCount = OrderService::instance()->getPaidOrderCount($openid);
//        $paidOrderCount = 0;
        $list = $this->getModel()->field("*")
            ->hidden(['fetch_openids'])
            ->field($field)
            ->where('status', 1)
            ->whereRaw("has_count > 0")
            ->whereRaw("(fetch_openids is null or find_in_set('{$openid}', fetch_openids))")
            ->when($paidOrderCount == 0, function ($query) {
                $query->whereOrRaw("status = 1 and is_new = 1");
            })
            ->order('sort desc,id desc')
            ->fetchSql(0)
            ->select();
//        alert($list);
        return $list;
    }

    /**
     * 我可领取的优惠券发布，剩余数量为0的也显示
     * @param $openid
     * @param $field
     * @return array|string|Collection|\think\db\Fetch[]|BaseSoftDeleteModel[]|CouponPublishModel[]
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getListForOpenidWithHasCount0($openid, $field="*") {
        // 已支付订单数量，用来判断是否是新用户
        $paidOrderCount = OrderService::instance()->getPaidOrderCount($openid);
//        $paidOrderCount = 0;
        $list = $this->getModel()->field("*")
            ->hidden(['fetch_openids'])
            ->field($field)
            ->where('status', 1)
            ->whereRaw("(fetch_openids is null or find_in_set('{$openid}', fetch_openids))")
            ->when($paidOrderCount == 0, function ($query) {
                $query->whereOrRaw("status = 1 and is_new = 1");
            })
            ->order('sort desc,id desc')
            ->fetchSql(0)
            ->select();
//        alert($list);
        return $list;
    }

    /**
     * 获取可领券的gids
     * @param $openid
     * @param $field
     * @return array
     */
    public function getGidsForOpenid($openid, $field="*") {
        // 已支付订单数量，用来判断是否是新用户
        $paidOrderCount = OrderService::instance()->getPaidOrderCount($openid);
        $gids = $this->getModel()->field("*")
            ->hidden(['fetch_openids'])
            ->field($field)
            ->where('status', 1)
            ->whereRaw("has_count > 0")
            ->whereRaw("fetch_openids is null or find_in_set('{$openid}', fetch_openids)")
            ->when($paidOrderCount == 0, function ($query) {
                $query->whereOrRaw("is_new = 1");
            })
            ->order('sort desc,id desc')
            ->column("gid");
        return $gids;
    }

    public function updateByGid($gid, $data) {
        return $this->search(['gid' => $gid])->update($data);
    }


}