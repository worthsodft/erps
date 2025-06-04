<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use app\master\controller\sale\Waterstation;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\Model;
use vandles\lib\Tool;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\OutStockModel;

class OutStockService extends BaseService {
    protected static $instance;


    public static function instance(): OutStockService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|OutStockModel
     */
    public function getModel() {
        return OutStockModel::mk();
    }

    public function mQuery(): QueryHelper {
        return OutStockModel::mQuery();
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
        if (empty($post['sn'])) [$isCreate, $sn] = [true, Tool::genSn("OS")];
        else [$isCreate, $sn] = [false, $post['sn']];

        // 只能修改自己创建的单据
        if (!$isCreate) {
            $main = $this->getModel()->where('sn', $sn)->find();
            if (empty($main)) VException::throw("单据不存在");
            if ($main['create_by'] != session("user.id")) VException::throw("只能修改自己创建的单据");
            if ($main['status'] != 0) VException::throw("单据已审核，无法修改");
        }

        // 1. 创建主出库单
        $main = [
            'out_stock_type' => $post['out_stock_type'] ?? 'sale',
            'order_sn'       => $post['order_sn'] ?? '',
            'buyer_gid'      => $post['buyer_gid'] ?? '',
            'depot_gid'      => $post['depot_gid'] ?? '',
            'remark'         => $post['remark'] ?? '',
            'create_by'      => $post['create_by']??session("user.id"),
            'from'           => $post['from'] ?? null
        ];

        // 2. 创建明细出库单
        $goodsSns  = array_unique(array_column($subs, 'goods_sn'));
        $goodsList = GoodsService::instance()->getList(['sn' => $goodsSns], "sn goods_sn,name goods_name,unit goods_unit,produce_spec goods_spec,self_price goods_price", null, "goods_sn");
        $items     = [];
        $serial_no = 1;
        $total     = 0;
        $amount    = 0;
        foreach ($subs as $sub) {
            if (!isset($goodsList[$sub['goods_sn']])) VException::throw("商品不存在或已下架: " . $sub['goods_name']);
            if ($sub['goods_number'] <= 0) VException::throw("出库数量不能小于等于0: " . $sub['goods_name']);

            $goods        = $goodsList[$sub['goods_sn']];
            $goods_amount = round($sub['goods_number'] * $sub['goods_price'], 2);

            if (!isset($items[$sub['goods_sn']])) {
                $items[$sub['goods_sn']] = [
                    'out_stock_sn' => $sn,
                    'serial_no'    => $serial_no++,
                    'goods_sn'     => $goods['goods_sn'],
                    'goods_name'   => $goods['goods_name'],
                    'goods_unit'   => $goods['goods_unit'],
                    'goods_spec'   => $goods['goods_spec'],
                    'goods_number' => $sub['goods_number'],
                    'goods_price'  => $sub['goods_price'],
                    'goods_amount' => $goods_amount,
                ];
            } else {
                $items[$sub['goods_sn']]['goods_number'] = round($items[$sub['goods_sn']]['goods_number'] + $sub['goods_number'], 3);
                $items[$sub['goods_sn']]['goods_amount'] = round($items[$sub['goods_sn']]['goods_amount'] + $goods_amount, 2);
            }
            $total  += $sub['goods_number'];
            $amount += $goods_amount;
        }
        $main['total']  = $total;
        $main['amount'] = round($amount, 2);

        // 企业客户预付款处理
        // if (!empty($main['buyer_gid'])) {
        //     $customer = CustomerService::instance()->getCustomerByGid($main['buyer_gid'], "id,gid,money");
        //     if (empty($customer)) VException::throw("企业客户不存在");
        //     if ($customer['money'] > 0 && $customer['money'] < $main['amount']) VException::throw("企业客户余额不足，请先充值预付款");

            // 冻结数据
/*            $freezeData = [
                'money'        => $main['amount'],
                'buyer_gid' => $main['buyer_gid'],
                'out_stock_sn' => $sn,
            ];*/
        // }


        Db::startTrans();
        try {
/*
            // 1. 解冻旧冻结记录（buyer_gid + out_stock_sn）
            $res1 = CustomerService::instance()->unfreezeMoney($main['buyer_gid'], $sn);
            // 2. 如果有新的冻结数据（$freezeData），冻结之
            if(!empty($freezeData)){
                $res2 = CustomerService::instance()->freezeMoney($freezeData);
            }*/

            if ($isCreate) { // 创建
                $main['sn'] = $sn;
                $main       = $this->create($main);
            } else { // 修改
                $this->updateBySn($sn, $main);
                $main['sn'] = $sn;
                OutStockSubService::instance()->softDelBySn($sn);
            }
            OutStockSubService::instance()->createAll($items);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }

        $main['subs'] = $items;
        return $main;
    }

    public function bind(array &$list, $opts = []) {
        $companyGids = $partnerGids = $openids = [];
        foreach ($list as $v) {
            if(empty($v['buyer_gid'])) continue;
            if($v['from'] == 'company') $companyGids[] = $v['buyer_gid'];
            elseif($v['from'] == 'partner') $partnerGids[] = $v['buyer_gid'];
            elseif($v['from'] == 'mini') $openids[] = $v['buyer_gid'];
        }
        $companyGids = array_unique($companyGids);
        $partnerGids = array_unique($partnerGids);
        $openids     = array_unique($openids);

        if (empty($opts['companyList'])) {
            $companyList = CustomerService::instance()->getListOptsByGids($companyGids);
        }else $companyList = $opts['companyList'];

        if (empty($opts['partnerList'])) {
            $partnerList = WaterStationService::instance()->getListOptsByGids($partnerGids);
        }else $partnerList = $opts['partnerList'];

        if (empty($opts['userInfoList'])) {
            $userInfoList = UserInfoService::instance()->getListOptsByOpenids($openids);
        }else $userInfoList = $opts['userInfoList'];





        $suids1   = array_unique(array_column($list, 'create_by'));
        $suids2   = array_unique(array_column($list, 'pass_by'));
        $suids    = array_filter(array_unique(array_merge($suids1, $suids2)));
        $sysUsers = SystemUserService::instance()->getList(['id' => $suids], "id,nickname");
        $sysUsers = array_column($sysUsers->toArray(), 'nickname', 'id');

        $status = config('a.out_stock_status');
        $types  = config('a.out_stock_types');

        if (empty($opts['depotList'])) {
            $depotList = DepotService::instance()->getDepotListAndWaterStationList(true);
        }

        foreach ($list as &$vo) {
            if($vo['from'] == 'company') $vo['buyer_gid_txt'] = $companyList[$vo['buyer_gid']] ?? "未设置";
            elseif($vo['from'] == 'partner') $vo['buyer_gid_txt'] = $partnerList[$vo['buyer_gid']] ?? "未设置";
            elseif($vo['from'] == 'mini') $vo['buyer_gid_txt'] = $userInfoList[$vo['buyer_gid']] ?? "未设置";
            else $vo['buyer_gid_txt'] = "未设置";
            $vo['create_by_txt']      = $sysUsers[$vo['create_by']] ?? "";
            $vo['pass_by_txt']        = $sysUsers[$vo['pass_by']] ?? "";
            $vo['status_txt']         = $status[$vo['status']] ?? "未知状态";
            $vo['out_stock_type_txt'] = $types[$vo['out_stock_type']] ?? "未知类型";
            $vo['depot_gid_txt']      = $depotList[$vo['depot_gid']] ?? "无仓库";
            $vo['unpaid_amount']      = round($vo['amount'] - $vo['paid_amount'], 2);
            if(!empty($opts['withSubs'])){
                $vo['subs'] = OutStockSubService::instance()->search(['out_stock_sn' => $vo['sn']])->order("serial_no asc,id desc")->select();
            }
        }
    }

    public function bindOne(&$vo, $opts = []) {
        if (empty($opts['buyer'])) {
            if($vo['from'] == 'company') $buyer = CustomerService::instance()->getByGid($vo['buyer_gid']);
            elseif($vo['from'] == 'partner') $buyer = WaterStationService::instance()->getByGid($vo['buyer_gid']);
            elseif($vo['from'] == 'mini') $buyer = UserInfoService::instance()->getUserInfoByOpenid($vo['buyer_gid'], 'id,openid gid, nickname title');
            else $buyer = null;
        }else $buyer = $opts['buyer'];

        if (empty($opts['depot'])) {
            $depot = DepotService::instance()->getDepotOrWaterStationByGid($vo['depot_gid']);
        }

        $suids    = array_filter([$vo['create_by'], $vo['pass_by']]);
        $sysUsers = SystemUserService::instance()->getList(['id' => $suids], "id,nickname");
        $sysUsers = array_column($sysUsers->toArray(), 'nickname', 'id');

        $status = config('a.out_stock_status');
        $types  = config('a.out_stock_types');

        $vo['buyer_gid_txt']      = $buyer['title'] ?? "未设置";
        $vo['create_by_txt']      = $sysUsers[$vo['create_by']] ?? "";
        $vo['pass_by_txt']        = $sysUsers[$vo['pass_by']] ?? "";
        $vo['status_txt']         = $status[$vo['status']] ?? "未知状态";
        $vo['out_stock_type_txt'] = $types[$vo['out_stock_type']] ?? "未知类型";
        $vo['depot_gid_txt']      = $depot['title'] ?? "无仓库";
        $vo['unpaid_amount']      = round($vo['amount'] - $vo['paid_amount'], 2);
    }

    public function updateBySn(string $sn, array $data) {
        return $this->getModel()->where(['sn' => $sn])->update($data);
    }

    public function getBySn(string $sn, string $field = "*") {
        return $this->getModel()->field($field)->where(['sn' => $sn])->find();
    }

    /**
     * 判断出库单是否是生产领料出库单
     * @param $sn
     * @return bool
     */
    public function isProduceType($sn) {
        if (empty($sn)) return false;
        $vo = $this->getBySn($sn, 'id,out_stock_type');
        if (empty($vo)) return false;
        return $vo['out_stock_type'] == 'produceget';
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
     * 查询出库所关联的收款单
     * @param $out_stock_sns
     * @return array|mixed|QueryHelper|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getPayInSnsBySns($out_stock_sns) {
        $res        = $this->getModel()::mQuery()->whereIn("sn", $out_stock_sns)
            ->fieldRaw("group_concat(pay_in_sns) as pay_in_sns")
            ->find();
        $pay_in_sns = explode(",", $res['pay_in_sns'] ?? '');
        return array_unique(array_filter($pay_in_sns));
    }

    /**
     * 查询出库单所关联的、未审核的收款单号
     * @param $out_stock_sns
     * @return array
     */
    public function getUnPassedPayInSnsByOutStockSns($out_stock_sns) {
        $pay_in_sns = $this->getPayInSnsBySns($out_stock_sns);
        $list       = PayInService::instance()->getModel()::whereIn("sn", $pay_in_sns)
            ->where('status', 0)->column("sn");
        return $list;
    }

    public function updateBySns($sns, array $data) {
        return $this->getModel()->whereIn("sn", $sns)->update($data);
    }

    public function getPassedMainListBySns(array $sns, $field = "*") {
        $sns = array_filter($sns);
        if (empty($sns)) return [];
        $sns = array_unique($sns);
        return $this->getModel()->whereIn("sn", $sns)->where("status", 1)->field($field)->orderField("sn", $sns)->select();
    }

    public function getMainListBySns(array $sns, $field = "*") {
        $sns = array_filter($sns);
        if (empty($sns)) return [];
        $sns = array_unique($sns);
        return $this->getModel()->whereIn("sn", $sns)->field($field)->orderField("sn", $sns)->select();
    }

    /**
     * 组织出库单数据（用于收款核销）
     * @param float $money
     * @param array $outStocks 出库单数组，或出库单号数组
     * @return array
     */
    public function getDeltaItems(float $money, array $outStocks) {
        if (!is_array($outStocks[0])) {
            $outStocks = $this->getMainListBySns($outStocks, "id,sn,amount,paid_amount,amount-paid_amount unpaid_amount,status");
        }
        $items = [];
        foreach ($outStocks as $outStock) {
            $item   = ['id' => $outStock['id']];
            $money_ = $money - $outStock['unpaid_amount'];
            if ($money_ >= 0) {
                $item['delta'] = $outStock['unpaid_amount'];
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

    public function getColumnMap() {
        return [
            "id"                 => "ID",
            "sn"                 => "编号",
            "order_sn"           => "订单编号",
            "buyer_gid_txt"      => "客户",
            "depot_gid_txt"      => "仓库",
            "out_stock_type_txt" => "出库类型",
            "create_by_txt"      => "创建人",
            "pass_by_txt"        => "审核人",
            "pass_at"            => "审核时间",
            "pass_remark"        => "审核备注",
            "total"              => "总数量",
            "amount"             => "总金额",
            "paid_amount"        => "已支付金额",
            "unpaid_amount"      => "未支付金额",
            "pay_in_sns"         => "收款单号",
            "remark"             => "备注",
            "status_txt"         => "状态",
            "create_at"          => "创建时间",
            "update_at"          => "更新时间"
        ];
    }

    /**
     * @param array $post
     * @return true
     */
    public function pass(array $post) {

        $main = OutStockService::instance()->getBySn($post['sn']);
        if ($main->status != 0) VException::throw("该出库单已审核，不能再次审核");
        if ($main->paid_amount > 0) VException::throw("该出库单已发生支付，不能审核");

        $data = [
            'status'      => $post['type'],
            'pass_remark' => $post['pass_remark'],
            'pass_by'     => session("user.id"),
            'pass_at'     => now(),
            'depot_gid'   => $post['depot_gid'] ?? null
        ];

        Db::startTrans();
        try {
            // 审核通过，需要做的操作
            if ($post['type'] == 1) {

                // 1. 处理企业客户预付款处理
                /*
                if (!empty($main['buyer_gid'])) {
                    $customer = CustomerService::instance()->getCustomerByGid($main['buyer_gid'], "id,gid,money");
                    if (empty($customer)) VException::throw("企业客户不存在");
                    if ($customer['money'] > 0 && $customer['money'] < $main['amount']) VException::throw("企业客户余额不足，请先充值预付款");
                    // 使用预付款支付
                    $payInData = [
                        "buyer_gid" => $main['buyer_gid'],
                        "target_gid"   => $main['sn'],
                        "delta"        => $main['amount']
                    ];
                }
                if (isset($payInData)) {
                    // ① 企业用户减余额
                    CustomerService::instance()->reduceMoney($main['buyer_gid'], $main['amount'], 'order', $main['sn']);
                    // ② 更新出库单已支付金额
                    $data['paid_amount'] = $main['amount'];
                    // ③ 新增收款单
                    $payIn = PayInService::instance()->createWhenPrePay($payInData);
                    $data['pay_in_sns'] = $payIn->sn;
                }
                */
                // 2. 更新出库单
                OutStockService::instance()->updateBySn($post['sn'], $data);
                // 3. 更新商品库存
                GoodsStockService::instance()->updateByOutStockSn($post['sn']);

            } else {
                // 更新出库单
                OutStockService::instance()->updateBySn($post['sn'], $data);
                // 解冻
                // CustomerService::instance()->unfreezeMoney($main->buyer_gid, $post['sn']);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }

    /**
     * 根据订单编号创建出库单
     * @param $sn
     */
    public function createSaleByOrderSn($sn) {
        $order = OrderService::instance()->getOrderBySn($sn);
        $subs = $order->subs()->where("status", 1)->select();

        $data = [
            'out_stock_type' => 'sale',
            'order_sn'       => $sn,
            'buyer_gid'      => $order->openid,
            'depot_gid'      => $order->depot_gid,
            'remark'         => '创建订单时系统自动生成',
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
     * 创建订单时,系统自动审核,仅审核状态,不做数量变更
     * @param $sn
     */
    public function passOnlyByStockSn($sn) {
        $stock = $this->getBySn($sn);
        $stock->status = 1;
        $stock->pass_remark = "创建订单时系统自动审核";
        $stock->pass_at = now();
        $stock->save();
        return $stock;
    }


}