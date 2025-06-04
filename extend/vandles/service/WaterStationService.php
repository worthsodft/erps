<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\Model;
use vandles\lib\FileLock;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CartModel;
use vandles\model\CouponTplModel;
use vandles\model\UserAddressModel;
use vandles\model\WaterStationModel;

class WaterStationService extends BaseService {
    protected static $instance;


    public static function instance(): WaterStationService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);
        $data = [
            [
                "gid"        => uuid(),
                "title"      => "津淼公司本部",
                "detail"     => "河北区淮安道8号",
                "link_name"  => "和丽",
                "link_phone" => "26321009",
            ], [
                "gid"   => uuid(),
                "title" => "自来水集团水站",
                "detail"     => "和平区建设路54号自来水集团车库",
                "link_name"  => "李姐",
                "link_phone" => "15620933583",
            ], [
                "gid"   => uuid(),
                "title" => "美福园水站",
                "detail"     => "河东区新开路华捷道美福园1号楼底商",
                "link_name"  => "刘金立",
                "link_phone" => "24320740,24320720",
            ], [
                "gid"   => uuid(),
                "title" => "君临天下水站",
                "detail"     => "河北区博爱道19号君临大厦面朝海河1014号",
                "link_name"  => "王维",
                "link_phone" => "24460708",
            ]
        ];

        $res  = $this->getModel()->saveAll($data);
        $list = $this->getModel()->select();
        dd($list->toArray());
    }

    /**
     * @return BaseSoftDeleteModel|WaterStationModel
     */
    public function getModel() {
        return WaterStationModel::mk();
    }
    public function mQuery():QueryHelper {
        return WaterStationModel::mQuery();
    }

    /**
     * 得到下单时供选择的水站列表
     * @return void
     */
    public function getListForOrderConfirm() {
        // todo vandles

        return [];
    }

    public function getStationListWithDistance(array $post, $field="*") {
        $list = $this->search(['status'=>1])->field($field)->order("sort desc, id desc")->select();
        $list->each(function (WaterStationModel $item) use ($post) {
            // 计算距离
//            if($item->lat && $item->lng){
//                $item->distance = $this->getDistance($post['lat'], $post['lng'], $item->lat, $item->lng);
//            }
        });
        return $list;
    }

    public function getStationOpeningOpts($field="*") {
        $query = $this->getModel()->field($field)->where([
            'status' => 1,
            'is_open' => 1
        ])->order("sort desc, id desc");

        return $query->select();
    }

    /**
     * 获取直线距离
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     * @return float
     */
    public function getDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6367000; // 地球半径（单位：米）
        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;

        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;

        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }

    public function getListByGids(array $gids=[], string $field="*", string $asKey="") {
        $query = $this->getModel()->order("sort desc, id desc");
        if($gids) $query->whereIn("gid", $gids);
        if($field) $query->field($field);
        $list = $query->select();

        if(empty($asKey) || empty($list)) return $list;
        else return array_column($list->toArray(), null, $asKey);
    }

    /**
     * 得到一个营业中的水站
     * @param $station_gid
     * @param string $field
     * @return array|mixed|Model|BaseSoftDeleteModel|WaterStationModel|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getOpeningOneByGid($station_gid, $field="*") {
        $query = $this->getModel()->field($field)->where(['status'=>1,"is_open"=>1])->where("gid", $station_gid);

        return $query->find();
    }

    /**
     * 减少余额
     * @param $station_gid
     * @param $delta
     * @param string $log_type
     * @param string $target_gid
     * @return boolean
     */
    public function reduceMoney($station_gid, $delta, string $log_type, string $target_gid) {
        if ($delta <= 0) VException::runtime("金额必须大于0");

        $lock = FileLock::instance("partner_money_" . $station_gid);
        if (!$lock->lock(true)) VException::runtime("系统繁忙，请稍后再试");

        Db::startTrans();
        try {
            $buyer = $this->getModel()->where("gid", $station_gid)->lock(true)->find();
            if (empty($buyer)) VException::runtime("经销商客户不存在");
            if ($buyer['money'] < $delta) VException::runtime("经销商客户余额不足");

            // 1. 减少余额
            $before          = $buyer->money;
            $buyer->money = round($buyer->money - $delta, 2);
            $buyer->save();

            // 2. 增加余额记录
            $data = BuyerMoneyLogService::instance()->create([
                'gid'          => uuid(),
                'buyer_gid'    => $station_gid,
                'before'       => $before,
                'delta'        => -$delta,
                'log_type'     => $log_type,
                'target_gid'   => $target_gid,
                'buyer_type'   => 'partner',
            ]);

            // 3. 增加系统操作日志
            $suid = session("user.id");
            sysoplog("经销商客户余额变动", "经销商客户:{$station_gid},原金额：{$before},余额减少:-{$delta},操作人:{$suid},余额记录gid:{$data->gid}");
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }

    /**
     * 增加余额
     * @param $buyer_gid
     * @param $delta
     * @param $log_type string 记录类型,recharge:预付款增加
     * @param $target_gid string 变动对象gid，log_type为order时，取订单编号，recharge时，取预付款单号
     * @return true
     * @throws \Exception
     */
    public function incMoney($buyer_gid, $delta, string $log_type, string $target_gid) {
        if ($delta <= 0) VException::runtime("金额必须大于0");

        $lock = FileLock::instance("money_" . $buyer_gid);
        if (!$lock->lock(true)) VException::runtime("系统繁忙，请稍后再试");

        Db::startTrans();
        try {
            // 1. 增加余额
            $buyer = $this->getModel()->where("gid", $buyer_gid)->lock(true)->find();
            if (empty($buyer)) VException::runtime("经销商客户不存在");

            $before          = $buyer->money;
            $buyer->money = round($buyer->money + $delta, 2);
            $buyer->save();

            // 2. 增加余额记录
            $data = BuyerMoneyLogService::instance()->create([
                'gid'          => uuid(),
                'buyer_gid'    => $buyer_gid,
                'before'       => $before,
                'delta'        => $delta,
                'log_type'     => $log_type,
                'target_gid'   => $target_gid,
                'buyer_type'   => 'partner',
            ]);

            // 3. 增加系统操作日志
            $suid = session("user.id");
            sysoplog("经销商客户余额变动", "经销商客户:{$buyer_gid},原金额：{$before},余额增加:{$delta},操作人:{$suid},余额记录gid:{$data->gid}");
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }

    public function getListOptsByGids(array $gids = null) {
        $query = $this->getModel()->order("sort desc, id desc");
        if (!empty($gids)) $query->whereIn('gid', $gids);
        return $query->column('title', 'gid');
    }


}