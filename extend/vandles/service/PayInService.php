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
use vandles\lib\Snowflake;
use vandles\lib\Tool;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\PayInModel;
use WePay\Custom;

class PayInService extends BaseService {
    protected static $instance;


    public static function instance(): PayInService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);

        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|PayInModel
     */
    public function getModel() {
        return PayInModel::mk();
    }

    public function mQuery(): QueryHelper {
        return PayInModel::mQuery();
    }

    public function genSn(): string {
        return Tool::genSn("PI");
    }

    public function bind(array &$data) {
        [$companyList, $partnerList] = CustomerService::instance()->getCompanyOrPartnerOptsByList($data);

        $openids = [];
        foreach ($data as $item) {
            if($item['from'] == 'mini' && !empty($item['buyer_gid'])) $openids[] = $item['buyer_gid'];
        }
        $openids = array_unique($openids);
        $userInfoList = UserInfoService::instance()->getListOptsByOpenids($openids);

        $suids1   = array_unique(array_column($data, 'create_by'));
        $suids2   = array_unique(array_column($data, 'pass_by'));
        $suids    = array_filter(array_unique(array_merge($suids1, $suids2)));
        $sysUsers = SystemUserService::instance()->getList(['id' => $suids], "id,nickname");
        $sysUsers = array_column($sysUsers->toArray(), 'nickname', 'id');

        $status    = config('a.pay_in_status');
        $pay_types = config('a.pay_in_types') + config('a.order_pay_types');

        foreach ($data as &$vo) {
            if($vo['from'] == 'company') $vo['buyer_gid_txt'] = $companyList[$vo['buyer_gid']] ?? '未设置';
            elseif($vo['from'] == 'partner') $vo['buyer_gid_txt'] = $partnerList[$vo['buyer_gid']] ?? '未设置';
            elseif($vo['from'] == 'mini') $vo['buyer_gid_txt'] = $userInfoList[$vo['buyer_gid']] ?? '未设置';
            else $vo['buyer_gid_txt'] = '未设置';

            $vo['create_by_txt'] = $sysUsers[$vo['create_by']] ?? "";
            $vo['pass_by_txt']   = $sysUsers[$vo['pass_by']] ?? "";

            $vo['status_txt']   = $status[$vo['status']] ?? "未知状态";
            $vo['pay_type_txt'] = $pay_types[$vo['pay_type']] ?? "未知方式";
        }
    }

    public function bindOne(&$vo) {
        if($vo['from'] == 'mini') $buyer = UserInfoService::instance()->getUserInfoByOpenid($vo['buyer_gid'], "id,openid gid, nickname title");
        else $buyer = CustomerService::instance()->getCompanyOrPartnerByGidAndFrom($vo['buyer_gid'], $vo['from'], "id,gid,title");

        $suids    = array_filter([$vo['create_by'], $vo['pass_by']]);
        $sysUsers = SystemUserService::instance()->getList(['id' => $suids], "id,nickname");
        $sysUsers = array_column($sysUsers->toArray(), 'nickname', 'id');

        $status    = config('a.pay_in_status');
        $pay_types = config('a.pay_in_types') + config('a.order_pay_types');

        $vo['buyer_gid_txt'] = $buyer['title'] ?? "未设置";

        $vo['create_by_txt'] = $sysUsers[$vo['create_by']] ?? "";
        $vo['pass_by_txt']   = $sysUsers[$vo['pass_by']] ?? "";

        $vo['status_txt']   = $status[$vo['status']] ?? "未知状态";
        $vo['pay_type_txt'] = $pay_types[$vo['pay_type']] ?? "未知方式";
    }

    /**
     * 收款单审核
     * 1. 修改收款单状态、审核时间、审核人、审核备注
     * 2. 修改出库单已支付金额
     * @param $post
     * @return bool
     */
    public function pass($post) {
        if (empty($post['is_pass']) && empty($post['pass_remark'])) VException::throw("请在审核备注栏填写驳回原因");
        $key  = md5("pay_in_pass_{$post['id']}");
        $lock = FileLock::instance($key);
        if (!$lock->lock(true)) VException::throw("系统繁忙，请稍后访问！");

        $payIn = $this->getModel()::findOrEmpty($post['id']);
        if (!$payIn) VException::throw("收款单不存在");
        if ($payIn->status != 0) VException::throw("收款单已审核，不需要重复审核");

        $outStockService = OutStockService::instance();
        $out_stock_sns   = explode(",", $payIn['out_stock_sns']);
        $outStocks       = $outStockService->getMainListBySns($out_stock_sns, "id,sn,amount,paid_amount,amount-paid_amount unpaid_amount,status,pay_in_sns");

        // 1. 修改收款单状态、审核时间、审核人、审核备注
        $payInData = [
            'status'      => $post['is_pass'] ? 1 : -1,
            'pass_remark' => $post['pass_remark'],
            'pass_at'     => now(),
            'pass_by'     => session("user.id"),
        ];

        // 如果是驳回，从出库单中将收款单号去除，修改收款单信息，然后返回
        if (empty($post['is_pass'])) {

            Db::startTrans();
            try {
                // 1. 从出库单中将收款单号去除
                foreach ($outStocks as $outStock) {
                    $pay_in_sns = explode(",", $outStock['pay_in_sns']);
                    // 找到$payIn['sn']在$pay_in_sns中的位置
                    $pay_in_sns_key = array_search($payIn['sn'], $pay_in_sns);
                    unset($pay_in_sns[$pay_in_sns_key]);
                    $outStock['pay_in_sns'] = implode(",", $pay_in_sns);
                    $outStock->save();
                }

                // 2. 修改收款单信息
                $this->updateById($payIn['id'], $payInData);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                VException::throw($e->getMessage());
            }
            return true;
        }

        // 计算合计应收款金额
        $unpaid_amount = 0;
        foreach ($outStocks as $outStock) {
            if ($outStock['status'] != 1) VException::throw("出库单：{$outStock['sn']} 未审核");
            $unpaid_amount += $outStock['unpaid_amount'];
        }

        if (count($outStocks) != count($out_stock_sns)) VException::throw("收款单关联的出库单与实际出库单数量不符");

        $money = floatval($payIn['money']);
        if ($payIn['money'] > $unpaid_amount) VException::throw("收款金额({$money})不能大于合计应收款金额({$unpaid_amount})");;

        // 组织出库单数据（用于收款核销）
        $items = $outStockService->getDeltaItems($money, $outStocks->toArray());
        if (count($outStocks) != count($items)) VException::throw("收款金额不能覆盖所有出库单，请重新选择出库单");


        Db::startTrans();
        try {
            // 1. 修改出库单的已支付金额
            $orderService = OrderService::instance();
            foreach ($items as $item) {
                $outStockService->updateById($item['id'], [
                    "paid_amount" => Db::raw("paid_amount + {$item['delta']}")
                ]);
                $out_ = $outStockService->getOneById($item['id'], "id,amount,paid_amount,out_stock_type,order_sn");
                // 如果是销售订单，并且已支付金额大于等于订单金额，更新订单的支付状态
                if($out_->out_stock_type == 'sale' && $out_->order_sn && $out_->paid_amount >= $out_->amount){
                    // 更新订单的支付状态
                    $payRemark = "收款单号：" . $payIn['sn'];
                    $data = [
                        "pay_remark" => Db::raw("CONCAT(IFNULL(pay_remark, ''), ' [{$payRemark}]')"),
                        'pay_at' => now()
                    ];
                    $orderService->updateBySn($out_->order_sn, $data);
                }
            }

            // 2. 修改收款单信息
            $this->updateById($payIn['id'], $payInData);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }

    public function createWhenPrePay($payInData) {
        $money = $payInData['delta'];
        $suid  = session("user.id");
        $data  = [
            'sn'             => $this->genSn(),
            'buyer_gid'      => $payInData['buyer_gid'],
            'out_stock_sns'  => $payInData['target_gid'],
            'pay_type'       => '3', // 3预收款
            'un_paid_amount' => $money,
            'money'          => $money,
            'create_by'      => $suid,
            'pass_by'        => $suid,
            'pass_at'        => now(),
            'pass_remark'    => '预收款支付，出库(审核)时自动审核',
            'status'         => 1 // 默认为已审核
        ];
        return $this->create($data);
    }

    public function createAndPassByStockSn($stockSn) {
        $stock = OutStockService::instance()->getModel()::where("sn", $stockSn)->find();
        $order = OrderService::instance()->getOrderBySn($stock['order_sn']);

        // 收款单数据(含审核)
        $data = [
            'sn'             => $this->genSn(),
            'buyer_gid'      => $stock['buyer_gid'],
            'out_stock_sns'  => $stockSn,
            'pay_type'       => $order->pay_type,
            'un_paid_amount' => $stock['amount'], // 所选出库单的合计应收款金额
            'money'          => $stock['amount'],
            'from'           => $order->from, // 客户订单来源
            'status'         => 1, // 默认为已审核
            'remark'         => '创建订单时系统自动生成',
            'pass_at'        => now(),
            'pass_remark'    => '创建订单时系统自动审核',
        ];

        // 出库单的修改数据
        $stock->paid_amount = $stock['amount'];
        $stock->pay_in_sns  = $data['sn'];

        Db::startTrans();
        try{
            $payIn = $this->create($data);
            $stock->save();
            Db::commit();
        }catch(\Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return $payIn;
    }


}