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
use think\helper\Str;
use think\Model;
use vandles\lib\Tool;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CartModel;
use vandles\model\CouponTplModel;
use vandles\model\CustomerModel;
use vandles\model\InStockModel;
use vandles\model\SupplierModel;
use vandles\model\UserAddressModel;
use vandles\model\WaterStationModel;

class InStockService extends BaseService {
    protected static $instance;


    public static function instance(): InStockService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|InStockModel
     */
    public function getModel() {
        return InStockModel::mk();
    }

    public function mQuery(): QueryHelper {
        return InStockModel::mQuery();
    }

    /**
     * 新建（$post['sn']不存在） 或 更新（$post['sn']存在）
     * @param $post
     * @return Model|BaseSoftDeleteModel
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function saveWithSubs($post) {
        $subs = $post['subs'] ?? [];
        // 判断是新建还是更新
        if (empty($post['sn'])) [$isCreate, $sn] = [true, Tool::genSn("IS")];
        else [$isCreate, $sn] = [false, $post['sn']];

        // 只能修改自己创建的单据
        if (!$isCreate) {
            $main = $this->getModel()->where('sn', $sn)->find();
            if (empty($main)) VException::throw("单据不存在");
            if ($main['create_by'] != session("user.id")) VException::throw("只能修改自己创建的单据");
            if ($main['status'] != 0) VException::throw("单据已审核，无法修改");
        }

        // 1. 创建主入库单
        $main = [
            'in_stock_type' => $post['in_stock_type'] ?? 'purchase',
            'buy_order_sn'  => $post['buy_order_sn'] ?? '',
            'supplier_gid'  => $post['supplier_gid'] ?? '',
            'depot_gid'     => $post['depot_gid'] ?? '',
            'remark'        => $post['remark'] ?? '',
            'create_by'     => $post['create_by']??session("user.id"),
        ];

        // 2. 创建明细入库单
        $goodsSns  = array_unique(array_column($subs, 'goods_sn'));
        $goodsList = GoodsService::instance()->getList(['sn' => $goodsSns], "sn goods_sn,name goods_name,unit goods_unit,produce_spec goods_spec, cost goods_cost", null, "goods_sn");
        $items     = [];
        $serial_no = 1;
        $total     = 0;
        $amount    = 0;

        foreach ($subs as $sub) {
            if (!isset($goodsList[$sub['goods_sn']])) VException::throw("商品不存在或已下架: " . $sub['goods_name']);
            if ($sub['goods_number'] <= 0) VException::throw("入库数量不能小于等于0: " . $sub['goods_name']);

            $goods_cost = $main['in_stock_type'] == 'refund' ? $sub['goods_price'] : $sub['goods_cost'];

            $goods        = $goodsList[$sub['goods_sn']];
            $goods_amount = round($sub['goods_number'] * $goods_cost, 2);

            if (!isset($items[$sub['goods_sn']])) {
                $items[$sub['goods_sn']] = [
                    'in_stock_sn'  => $sn,
                    'serial_no'    => $serial_no++,
                    'goods_sn'     => $goods['goods_sn'],
                    'goods_name'   => $goods['goods_name'],
                    'goods_unit'   => $goods['goods_unit'],
                    'goods_spec'   => $goods['goods_spec'],
                    'goods_number' => $sub['goods_number'],
                    'goods_cost'   => $goods_cost,
                    'goods_amount' => $goods_amount,
                ];
            } else {
                $items[$sub['goods_sn']]['goods_number'] = round($items[$sub['goods_sn']]['goods_number'] + $sub['goods_number'], 3);;
                $items[$sub['goods_sn']]['goods_amount'] = round($items[$sub['goods_sn']]['goods_amount'] + $goods_amount, 2);
            }
            $total  += $sub['goods_number'];
            $amount += $goods_amount;
        }
        $main['total']  = $total;
        $main['amount'] = round($amount, 2);

        Db::startTrans();
        try {
            if ($isCreate) {
                $main['sn'] = $sn;
                $main       = $this->create($main);
            } else {
                $this->updateBySn($sn, $main);
                $main['sn'] = $sn;
                InStockSubService::instance()->softDelBySn($sn);
            }
            InStockSubService::instance()->createAll($items);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }

        $main['subs'] = $items;
        return $main;
    }

    public function bind(array &$list, $opts = []) {
        if (empty($opts['supplierList'])) {
            $gids         = array_unique(array_column($list, 'supplier_gid'));
            $supplierList = SupplierService::instance()->getListOptsByGids($gids);
        }

        $suids1   = array_unique(array_column($list, 'create_by'));
        $suids2   = array_unique(array_column($list, 'pass_by'));
        $suids    = array_filter(array_unique(array_merge($suids1, $suids2)));
        $sysUsers = SystemUserService::instance()->getList(['id' => $suids], "id,nickname");
        $sysUsers = array_column($sysUsers->toArray(), 'nickname', 'id');

        $status = config('a.in_stock_status');
        $types  = config('a.in_stock_types');

        if (empty($opts['depotList'])) {
            $depotList = DepotService::instance()->getDepotListAndWaterStationList(true);
        }

        foreach ($list as &$vo) {
            $vo['supplier_gid_txt']  = $supplierList[$vo['supplier_gid']] ?? "无供应商";
            $vo['create_by_txt']     = $sysUsers[$vo['create_by']] ?? "";
            $vo['pass_by_txt']       = $sysUsers[$vo['pass_by']] ?? "";
            $vo['status_txt']        = $status[$vo['status']] ?? "未知状态";
            $vo['in_stock_type_txt'] = $types[$vo['in_stock_type']] ?? "未知类型";
            $vo['depot_gid_txt']     = $depotList[$vo['depot_gid']] ?? "无仓库";
            $vo['unpaid_amount']     = round($vo['amount'] - $vo['paid_amount'], 2);
        }
    }

    public function bindOne(&$vo, $opts = []) {
        if (empty($opts['supplier'])) {
            $supplier = SupplierService::instance()->getSupplierByGid($vo['supplier_gid']);
        }
        if (empty($opts['depot'])) {
            $depot = DepotService::instance()->getDepotOrWaterStationByGid($vo['depot_gid']);
        }


        $suids    = array_filter([$vo['create_by'], $vo['pass_by']]);
        $sysUsers = SystemUserService::instance()->getList(['id' => $suids], "id,nickname");
        $sysUsers = array_column($sysUsers->toArray(), 'nickname', 'id');

        $status = config('a.in_stock_status');
        $types  = config('a.in_stock_types');

        $vo['supplier_gid_txt']  = $supplier['title'] ?? "无供应商";
        $vo['create_by_txt']     = $sysUsers[$vo['create_by']] ?? "";
        $vo['pass_by_txt']       = $sysUsers[$vo['pass_by']] ?? "";
        $vo['status_txt']        = $status[$vo['status']] ?? "未知状态";
        $vo['in_stock_type_txt'] = $types[$vo['in_stock_type']] ?? "未知类型";
        $vo['depot_gid_txt']     = $depot['title'] ?? "无仓库";
        $vo['unpaid_amount']     = round($vo['amount'] - $vo['paid_amount'], 2);
    }

    public function updateBySn(string $sn, array $data) {
        return $this->getModel()->where(['sn' => $sn])->update($data);
    }

    /**
     * 从计划订单创建入库单
     *
     * $planSns[$post['plan_sn']] = $post['plan_sn'];
     * $formData[$sub['id']] = [
     *     "id" => $sub['id'],
     *     "actual_number1" => $sub['actual_number1'],
     *     "actual_number2" => $sub['actual_number2'],
     * ];
     *
     * @param $planSns
     * @param $formData
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function createFromPlans($planSns, $formData) {
        $ids = array_keys($formData);

        if (empty($ids)) VException::throw("计划id不存在");
        $subs = PlanSubService::instance()->getListByIds($ids, "id,plan_sn,sn plan_sub_sn,goods_sn,goods_name");
        if (empty($subs) || $subs->isEmpty()) VException::throw("计划不存在");

        $goodsSns  = array_unique(array_column($subs->toArray(), 'goods_sn'));
        $goodsList = GoodsService::instance()->getList(['sn' => $goodsSns], "sn goods_sn,name goods_name,unit goods_unit,produce_spec goods_spec,cost goods_cost", null, "goods_sn");

        $mains = [];
        $suid  = session("user.id");
        if (empty($suid)) VException::throw("请登录后操作");
        // 1:合格品入库，2二级品入库
        foreach ([1, 2] as $num) {
            // 1. 创建主入库单
            $main = [
                'supplier_gid'  => '', // 生产入库，没有供应商
                'plan_sns'      => join(',', $planSns),
                'create_by'     => $suid,
                'sn'            => Tool::genSn("IS"),
                'in_stock_type' => "produce",
            ];

            // 2. 创建明细入库单
            $items     = [];
            $serial_no = 1;
            $total     = 0;
            $amount    = 0;
            foreach ($subs as $sub) {
                if (!isset($goodsList[$sub['goods_sn']])) VException::throw("商品不存在: " . $sub['goods_name']);
                $goods = $goodsList[$sub['goods_sn']];

                $item    = [
                    'in_stock_sn'  => $main['sn'],
                    'serial_no'    => $serial_no++,
                    'goods_sn'     => $goods['goods_sn'],
                    'goods_name'   => $goods['goods_name'],
                    'goods_unit'   => $goods['goods_unit'],
                    'goods_spec'   => $goods['goods_spec'],
                    'goods_number' => $formData[$sub['id']]['actual_number' . $num],
                    'goods_cost'   => $goods['goods_cost'],
                    'goods_amount' => round($formData[$sub['id']]['actual_number' . $num] * $goods['goods_cost'], 2),
                ];
                $items[] = $item;
                $total   += $item['goods_number'];
                $amount  += $item['goods_amount'];
            }
            $main['total']  = $total;
            $main['amount'] = $amount;
            if ($num == 2) $main['remark'] = "二级品生产入库";
            $main['subs'] = $items;
            $mains[]      = $main;
        }
        Db::startTrans();
        try {
            foreach ($mains as &$main) {
                if (!$main['total']) continue; // 入库总数量为0的, 不创建入库单
                $main = $this->create($main);
                InStockSubService::instance()->createAll($main['subs']);
                $main['subs'] = $items;
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
        return $mains;
    }

    public function getBySn(string $sn, string $field = "*") {
        return $this->getModel()->field($field)->where(['sn' => $sn])->find();
    }

    /**
     * 判断入库单是否是生产入库单 || 原料退还单
     * @param $sn
     * @return bool
     */
    public function isProduceType($sn) {
        if (empty($sn)) return false;
        $vo = $this->getBySn($sn, 'id,in_stock_type');
        if (empty($vo)) return false;
        return $vo['in_stock_type'] == 'produce' || $vo['in_stock_type'] == 'produceback';
    }

    /**
     * 未支付总金额
     * @param $sns
     * @return int|mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getUnPaidAmountBySns($sns) {
        $one = $this->getModel()->whereIn("sn", $sns)
            ->fieldRaw("sum(amount) - sum(paid_amount) as un_paid_amount")
            ->find();
        return $one['un_paid_amount'] ?? 0;
    }

    /**
     * 组织入库单数据（用于付款核销）
     * @param float $money
     * @param array $inStocks
     * @return array
     */
    public function getDeltaItems(float $money, array $inStocks) {
        if (!is_array($inStocks[0])) {
            $inStocks = $this->getMainListBySns($inStocks, "id,sn,amount,paid_amount,amount-paid_amount unpaid_amount,status");
        }
        $items = [];
        foreach ($inStocks as $inStock) {
            $item   = ['id' => $inStock['id']];
            $money_ = $money - $inStock['unpaid_amount'];
            if ($money_ >= 0) {
                $item['delta'] = $inStock['unpaid_amount'];
                $money         = $money_;
                $items[]       = $item;
                if ($money_ == 0) break;
            } else {
                $item['delta'] = $money;
                $items[]       = $item;
                break;
            }
        }
        return $items;
    }

    /**
     * 查询入库库单所关联的、未审核的付款单号
     * @param $in_stock_sns
     * @return array
     */
    public function getUnPassedPayOutSnsByInStockSns($in_stock_sns) {
        $pay_out_sns = $this->getPayOutSnsBySns($in_stock_sns);
        $list = PayOutService::instance()->getModel()::whereIn("sn", $pay_out_sns)
            ->where('status', 0)->column("sn");
        return $list;
    }

    public function getMainListBySns(array $sns, $field="*") {
        $sns = array_filter($sns);
        if (empty($sns)) return [];
        $sns = array_unique($sns);
        return $this->getModel()->whereIn("sn", $sns)->field($field)->orderField("sn", $sns)->select();
    }

    /**
     * 查询入库所关联的付款单
     * @param $in_stock_sns
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    private function getPayOutSnsBySns($in_stock_sns) {
        $res = $this->getModel()::mQuery()->whereIn("sn", $in_stock_sns)
            ->fieldRaw("group_concat(pay_out_sns) as pay_out_sns")
            ->find();
        $pay_out_sns = explode(",", $res['pay_out_sns'] ?? '');
        return array_unique(array_filter($pay_out_sns));
    }

    public function getColumnMap() {
        return [
            "id"                 => "ID",
            "sn"                 => "编号",
            "buy_order_sn"       => "采购单编号",
            "supplier_gid_txt"   => "供应商",
            "depot_gid_txt"      => "仓库",
            "in_stock_type_txt"  => "入库类型",
            "create_by_txt"      => "创建人",
            "pass_by_txt"        => "审核人",
            "pass_at"            => "审核时间",
            "pass_remark"        => "审核备注",
            "total"              => "总数量",
            "amount"             => "总金额",
            "paid_amount"        => "已支付金额",
            "unpaid_amount"      => "未支付金额",
            "pay_out_sns"        => "付款单号",
            "remark"             => "备注",
            "status_txt"         => "状态",
            "create_at"          => "创建时间",
            "update_at"          => "更新时间"
        ];
    }

    /**
     * 退款时，创建入库单
     * @param $orderSn
     */
    public function createRefundByOrderSn($orderSn) {
        $order = OrderService::instance()->getOrderBySn($orderSn);
        $subs = $order->subs()->where("status", 1)->select();

        $data = [
            'in_stock_type'  => 'refund',
            'buy_order_sn'   => $orderSn,
            'supplier_gid'   => $order->openid,
            'depot_gid'      => $order->depot_gid,
            'remark'         => '订单退款时系统自动生成',
            'from'           => $order->from,
            'create_by'      => null, // 自动生成,没有创建人
        ];

        $items = [];
        foreach ($subs as $sub) {
            $items[] = [
                'goods_sn'     => $sub['goods_sn'],
                'goods_number' => $sub['goods_number'],
                'goods_price'  => $sub['goods_self_price']
            ];
        }
        $data['subs'] = $items;

        $main = $this->saveWithSubs($data);
        return $main;
    }

    /**
     * 审核退款入库单,系统自动审核,仅审核状态,不做数量变更
     * @param $stockSn
     */
    public function passOnlyByStockSn($stockSn) {
        $stock = $this->getBySn($stockSn);
        $stock->status = 1;
        $stock->pass_remark = "订单退款时系统自动审核";
        $stock->pass_at = now();
        $stock->save();
        return $stock;
    }


}