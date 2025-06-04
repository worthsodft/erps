<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\helper\QueryHelper;
use think\facade\Db;
use vandles\lib\FileLock;
use vandles\lib\Tool;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\PayInModel;
use vandles\model\PayOutModel;

class PayOutService extends BaseService {
    protected static $instance;


    public static function instance(): PayOutService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);

        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|PayOutModel
     */
    public function getModel() {
        return PayOutModel::mk();
    }

    public function mQuery(): QueryHelper {
        return PayOutModel::mQuery();
    }

    public function bind(array &$data) {
        $cgids     = array_column($data, 'supplier_gid');
        $suppliers = SupplierService::instance()->getListOptsByGids($cgids);

        $suids1   = array_unique(array_column($data, 'create_by'));
        $suids2   = array_unique(array_column($data, 'pass_by'));
        $suids    = array_filter(array_unique(array_merge($suids1, $suids2)));
        $sysUsers = SystemUserService::instance()->getList(['id' => $suids], "id,nickname");
        $sysUsers = array_column($sysUsers->toArray(), 'nickname', 'id');

        $status    = config('a.pay_out_status');
        $pay_types = config('a.pay_out_types') + config('a.order_pay_types');

        foreach ($data as &$vo) {
            $vo['supplier_gid_txt'] = $suppliers[$vo['supplier_gid']] ?? '未设置';

            $vo['create_by_txt']      = $sysUsers[$vo['create_by']] ?? "";
            $vo['pass_by_txt']        = $sysUsers[$vo['pass_by']] ?? "";

            $vo['status_txt']       = $status[$vo['status']] ?? "未知状态";
            $vo['pay_type_txt']     = $pay_types[$vo['pay_type']] ?? "未知方式";
        }
    }

    public function bindOne(&$vo) {
        $supplier  = SupplierService::instance()->getByGid($vo['supplier_gid'], "id,gid,title");

        $suids    = array_filter([$vo['create_by'], $vo['pass_by']]);
        $sysUsers = SystemUserService::instance()->getList(['id' => $suids], "id,nickname");
        $sysUsers = array_column($sysUsers->toArray(), 'nickname', 'id');

        $status    = config('a.pay_out_status');
        $pay_types = config('a.pay_out_types') + config('a.order_pay_types');

        $vo['supplier_gid_txt'] = $supplier['title'] ?? "未设置";

        $vo['create_by_txt']      = $sysUsers[$vo['create_by']] ?? "";
        $vo['pass_by_txt']        = $sysUsers[$vo['pass_by']] ?? "";

        $vo['status_txt']       = $status[$vo['status']] ?? "未知状态";
        $vo['pay_type_txt']     = $pay_types[$vo['pay_type']] ?? "未知方式";
    }

    public function genSn() {
        return Tool::genSn("PO");
    }

    public function pass($post) {
        if (empty($post['is_pass']) && empty($post['pass_remark'])) VException::throw("请在审核备注栏填写驳回原因");
        $key  = md5("pay_out_pass_{$post['id']}");
        $lock = FileLock::instance($key);
        if (!$lock->lock(true)) VException::throw("系统繁忙，请稍后访问！");

        $payOut = $this->getModel()::findOrEmpty($post['id']);
        if (!$payOut) VException::throw("付款单不存在");
        if ($payOut->status != 0) VException::throw("付款单已审核，不需要重复审核");

        $inStockService = InStockService::instance();
        $in_stock_sns = explode(",", $payOut['in_stock_sns']);
        $inStocks     = $inStockService->getMainListBySns($in_stock_sns, "id,sn,amount,paid_amount,amount-paid_amount unpaid_amount,status,pay_out_sns");

        // 1. 修改付款单状态、审核时间、审核人、审核备注
        $payOutData = [
            'status'      => $post['is_pass'] ? 1 : -1,
            'pass_remark' => $post['pass_remark'],
            'pass_at'     => now(),
            'pass_by'     => session("user.id"),
        ];

        // 如果是驳回，从入库单中将付款单号去除，修改付款单信息，然后返回
        if(empty($post['is_pass'])) {

            Db::startTrans();
            try{
                // 1. 从入库单中将付款单号去除
                foreach ($inStocks as $inStock){
                    $pay_out_sns = explode(",", $inStock['pay_out_sns']);
                    // 找到$payOut['sn']在$pay_out_sns中的位置
                    $pay_out_sns_key = array_search($payOut['sn'], $pay_out_sns);
                    unset($pay_out_sns[$pay_out_sns_key]);
                    $inStock['pay_out_sns'] = implode(",", $pay_out_sns);
                    $inStock->save();
                }

                // 2. 修改付款单信息
                $this->updateById($payOut['id'], $payOutData);
                Db::commit();
            }catch(\Exception $e){
                Db::rollback();
                VException::throw($e->getMessage());
            }
            return true;
        }

        // 计算合计应付款金额
        $unpaid_amount = 0;
        foreach ($inStocks as $inStock) {
            if ($inStock['status'] != 1) VException::throw("入库单：{$inStock['sn']} 未审核");
            $unpaid_amount += $inStock['unpaid_amount'];
        }

        if (count($inStocks) != count($in_stock_sns)) VException::throw("付款单关联的入库单与实际入库单数量不符");

        $money = floatval($payOut['money']);
        if ($payOut['money'] > $unpaid_amount) VException::throw("付款金额({$money})不能大于合计应付款金额({$unpaid_amount})");;

        // 组织入库单数据（用于付款核销）
        $items = $inStockService->getDeltaItems($money, $inStocks->toArray());
        if (count($inStocks) != count($items)) VException::throw("付款金额不能覆盖所有入库单，请重新选择入库单");


        Db::startTrans();
        try{
            // 1. 修改入库单的已支付金额
            foreach ($items as $item){
                $inStockService->updateById($item['id'], [
                    "paid_amount" => Db::raw("paid_amount + {$item['delta']}")
                ]);
            }

            // 2. 修改付款单信息
            $this->updateById($payOut['id'], $payOutData);
            Db::commit();
        }catch(\Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }

    /**
     * 生成付款单 && 审核
     * @param $stockSn
     */
    public function createAndPassByStockSn($stockSn) {
        $stock = InStockService::instance()->getModel()::where("sn", $stockSn)->find();
        $order = OrderService::instance()->getOrderBySn($stock['buy_order_sn']);

        $scene = $stock->in_stock_type == 'refund' ? "订单退款" : "创建订单";

        // 付款单数据(含审核)
        $data = [
            'sn'             => $this->genSn(),
            'supplier_gid'   => $stock['supplier_gid'],
            'in_stock_sns'   => $stockSn,
            'pay_type'       => $order->pay_type,
            'un_paid_amount' => $stock['amount'], // 所选入库单的合计应付款金额
            'money'          => $stock['amount'],
            'from'           => $order->from, // 客户订单来源
            'status'         => 1, // 默认为已审核
            'remark'         => "{$scene}时系统自动生成",
            'pass_at'        => now(),
            'pass_remark'    => "{$scene}时系统自动审核",
        ];

        // 出库单的修改数据
        $stock->paid_amount = $stock['amount'];
        $stock->pay_out_sns = $data['sn'];

        Db::startTrans();
        try{
            $payOut = $this->create($data);
            $stock->save();
            Db::commit();
        }catch(\Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return $payOut;
    }


}