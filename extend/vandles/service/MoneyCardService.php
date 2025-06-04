<?php
/**
 *
 * Author: vandles
 * Date: 2021/9/10 15:16
 * Email: <vandles@qq.com>
 **/

namespace vandles\service;

use think\admin\Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\helper\Str;
use think\Model;
use vandles\lib\pay\WxPay;
use vandles\lib\VException;
use vandles\model\BaseSoftDeleteModel;
use vandles\model\CartModel;
use vandles\model\CouponTplModel;
use vandles\model\MoneyCardLogModel;
use vandles\model\MoneyCardModel;
use vandles\model\RechargeWayModel;
use vandles\model\UserAddressModel;

class MoneyCardService extends BaseService {
    protected static $instance;


    public static function instance(): MoneyCardService {
        if (!static::$instance) static::$instance = new static();
        return static::$instance;
    }

    public function init($isCreateTable = false, $isDeleteTable = false) {
        if ($isCreateTable) $this->getModel()->schema($isDeleteTable);

        dd("init");
    }

    /**
     * @return BaseSoftDeleteModel|MoneyCardModel
     */
    public function getModel() {
        return MoneyCardModel::mk();
    }

    /**
     * 根据充值日志，批量创建金额水卡
     * @return int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function batchCreateFromRechargeLog(): int {
        $count        = 0;
        $rechargeList = UserMoneyLogService::instance()->search()->whereRaw("log_type='recharge' or log_type='give'")
            ->field("id,openid,delta,log_type,recharge_way_gid,transaction_id,target_gid,create_at")
            ->order("id asc")->select();

        $data = [];
        foreach ($rechargeList as $k => $log) {
//            echo "{$log->delta}, \t\t\t{$log->log_type}\n";
//            if ($log->log_type == 'give') echo "\n";
            if ($log->log_type == 'recharge') {
                $give       = $rechargeList[$k + 1] ?? [];
                $realMoney  = $log->delta;
                $giveMoney  = $give->delta ?? 0;
                $totalMoney = bcadd($realMoney, $giveMoney, 2);
                $realRate   = bcdiv($realMoney, $totalMoney, 7);
                $giveRate   = bcsub(1, $realRate, 7);
                $data[]     = [
                    "gid"        => guid(),
                    "openid"     => $log->openid,
                    "real_init"  => $realMoney,
                    "give_init"  => $giveMoney,
                    "total_init" => $totalMoney,
                    "expire_at"  => date("Y-m-d H:i:s", strtotime($log->create_at) + 86400 * 365),
                    "real_rate"  => $realRate,
                    "give_rate"  => $giveRate,
                    "real_has"   => $realMoney,
                    "give_has"   => $giveMoney,
                    "total_has"  => $totalMoney,
                    "trans_id"   => $log->transaction_id,
                    "way_gid"    => $log->recharge_way_gid,
                    "create_at"  => $log->create_at,
                ];
                $count++;
            }
        }
        $this->getModel()->saveAll($data);
        return $count;
    }

    public function refund(BaseSoftDeleteModel $order) {
        $moneyCardInfos = json_decode($order->money_card_snap, true);

        Db::startTrans();
        try {
            if (is_array($moneyCardInfos) && count($moneyCardInfos) > 0) {
                foreach ($moneyCardInfos as $info) {
                    $card = $this->getModel()->where("gid", $info['card_gid'])->find();

                    // 卡里加金额
                    $this->getModel()->where("gid", $info['card_gid'])->update([
                        'real_has'  => Db::raw("real_has + " . $info['real_deduct']),
                        'give_has'  => Db::raw("give_has + " . $info['give_deduct']),
                        'total_has' => Db::raw("total_has + " . $info['total_deduct']),
                    ]);

                    // 增加日志
                    $logData = [
                        'gid'            => guid(),
                        'openid'         => $card->openid,
                        'real_before'    => $card->real_has,
                        'give_before'    => $card->give_has,
                        'total_before'   => $card->total_has,
                        'real_delta'     => $info['real_deduct'],
                        'give_delta'     => $info['give_deduct'],
                        'total_delta'    => $info['total_deduct'],
                        'order_sn'       => $order->sn,
                        'money_card_gid' => $card->gid,
                        'log_type'       => "refund",

                        'real_rate' => $card->real_rate,
                        'give_rate' => $card->give_rate,
                    ];
                    MoneyCardLogModel::create($logData);
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }

    /**
     * 卡消费
     * @param $card
     * @param $order
     * @return boolean
     */
    public function expend(BaseSoftDeleteModel $order) {
        if ($order['real_amount'] == 0) return false; // 商品实付金额为0的 不处理
        if ($order['pay_amount'] == 0) return false;  // 支付金额为0的 不处理

        $card = $this->search(['openid' => $order->openid])->whereRaw("total_has > 0")->where("status", 1)->order("id asc")->find();
        if (!$card && $order->status == OrderService::ORDER_STATUS_REFUND) return false; // 当前是退单，也没有卡，是之前手动删除的，不处理

//        if ($order['real_deduct'] > 0) return false;  // 已计算过的 不处理
        if (!in_array($order['status'], [
            OrderService::ORDER_STATUS_DELIVERING,
            OrderService::ORDER_STATUS_FINISHED,
            OrderService::ORDER_STATUS_REFUND
        ])) return false;// 不是 配送中 或 已完成 的 不处理

        $isSplitDeliver = true; // 运费是否拆分
        if ($isSplitDeliver) $amount = $order->pay_amount;
        else $amount = $order->real_amount; // 运费不拆分，在最后消费的卡中从real里扣减

        if ($order->pay_type != OrderService::PAY_TYPE_YUE) {
            OrderService::instance()->updateById($order->id, [
                'real_deduct'    => $amount,
                'invoice_amount' => $amount
            ]);
            return true;
        }

        $order_real_deduct = $order_give_deduct = 0;
        $moneyCardSnap     = [];
        Db::startTrans();
        try {
            while (true) {
                $card = $this->search(['openid' => $order->openid])->whereRaw("total_has > 0")->where("status", 1)->order("id asc")->find(); //
                if (!$card) {
                    VException::throw("用户没有金额水卡（openid:{$order->openid}）");
                }

                if ($amount <= $card->total_has) {
                    if ($isSplitDeliver) $deliver_amount = 0;
                    else $deliver_amount = $order->deliver_amount;
                    $this->doExpendCard($amount, $card, $order_real_deduct, $order_give_deduct, $moneyCardSnap, $order, $deliver_amount);
                    break;
                } else {
                    // 计算基本金额：卡剩余金额
                    $has = $card->total_has;
                    $this->doExpendCard($has, $card, $order_real_deduct, $order_give_deduct, $moneyCardSnap, $order);
                    $amount = round($amount - $has, 2);
                }
            }

//            dd($order->toArray());
            // 组织订单需要更新的数据
            $data = [
                'real_deduct'     => $order_real_deduct,
                'give_deduct'     => $order_give_deduct,
                'money_card_snap' => json_encode($moneyCardSnap),
            ];
            if ($isSplitDeliver) $data['invoice_amount'] = $data['real_deduct'];
            else $data['invoice_amount'] = round($order->deliver_amount + $data['real_deduct'], 2);

            OrderService::instance()->updateById($order->id, $data);

            // 退款返余额
            if ($order->status == 9 && $order->refund_status == 2 && $order->pay_type == 'yue') {
                $order = OrderService::instance()->getById($order->id);
                $this->refund($order);
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return true;
    }

    private function doExpendCard($base, $card, &$order_real_deduct, &$order_give_deduct, &$moneyCardSnap, $order, $deliver_amount = 0) {
        // 按比例扣减的金额
        $real_deduct = round($base * $card->real_rate, 2);
        $give_deduct = round($base - $real_deduct, 2);

        // 要写入订单里面的累计扣减金额
        $order_real_deduct = round($order_real_deduct + $real_deduct, 2);
        $order_give_deduct = round($order_give_deduct + $give_deduct, 2);
        // 要写入订单里面的用卡记录，退款时，原路退还
        $total_deduct    = round($real_deduct + $give_deduct, 2);
        $moneyCardSnap[] = [
            'card_gid'     => $card->gid,
            'real_deduct'  => $real_deduct,
            'give_deduct'  => $give_deduct,
            'total_deduct' => $total_deduct,
        ];

        // 金额水卡消费日志数据
        $logData = [
            'gid'            => guid(),
            'openid'         => $card->openid,
            'real_before'    => $card->real_has,
            'give_before'    => $card->give_has,
            'total_before'   => $card->total_has,
            'real_delta'     => -$real_deduct,
            'give_delta'     => -$give_deduct,
            'total_delta'    => -$total_deduct,
            'order_sn'       => $order['sn'],
            'money_card_gid' => $card->gid,
            'log_type'       => "order",

            'real_rate' => $card->real_rate,
            'give_rate' => $card->give_rate,

            'create_at' => $order['pay_at']
        ];


        // 更新卡余额数据
        if ($base == $card->total_has) {
            $card->real_has  = 0;
            $card->give_has  = 0;
            $card->total_has = 0;
        } else {
            $card->real_has  = round($card->real_has - $real_deduct - $deliver_amount, 2);
            $card->give_has  = round($card->give_has - $give_deduct, 2);
            $card->total_has = round($card->real_has + $card->give_has, 2);
        }
        MoneyCardLogModel::create($logData);
        $card->save();
    }

    /**
     * 退款
     * @param $gid
     * @throws \Exception
     */
    public function refundByGid($gid) {
        if (empty($gid)) VException::throw("参数错误");
        $card = $this->getByGid($gid);
        if (empty($card)) VException::throw("卡号不存在");
        if ($card->status == 0) VException::throw("卡已作废");
        if ($card->status != 1) VException::throw("卡状态不正确");
        if (floatval($card->real_has) <= 0) VException::throw("退款金额不能为零");
        if ($card->real_has != $card->real_init) VException::throw("卡已开始使用，不能退款");

        $res = $this->refundWeixin($card->trans_id, $card->real_init, $card->real_has, "卡退余额");
        $this->refundSuccess($card->gid, $res);
        return "success";
    }

    /**
     * @param $trans_id
     * @param $total_money
     * @param $refund_money
     * @param $refund_reason
     * @return mixed
     * @throws Exception
     */
    private function refundWeixin($trans_id, $total_money, $refund_money, $refund_reason) {
        $wxPay = WxPay::instance(ConfigService::instance()->getConfigMini());
        $opts  = [
            'transaction_id' => $trans_id,
            'out_refund_no'  => $this->genSn("refundcard"),
            'total_fee'      => $total_money,
            'refund_fee'     => $refund_money,
            'refund_desc'    => $refund_reason
        ];

        $res   = $wxPay->refund($opts); // 正式
//        $res = $this->getRefundDemoData(); // 模拟

        sleep(1);
        $res   = $wxPay->refundQueryByRefundId($res['refund_id']);
        $count = 0;
        while ($count++ < 10 && $res['refund_status_0'] == "PROCESSING") {
            sleep(1);
            $res = $wxPay->refundQueryByRefundId($res['refund_id_0']);
        }
        $res = $wxPay->refundQueryByRefundId($res['refund_id_0']);

        if ($res['refund_status_0'] != "SUCCESS") VException::throw("退款超时，请稍后再试");
        return $res;
    }

    public function refundSuccess(string $cardGid, $refundRes) {
        $card = $this->getByGid($cardGid);
        if (empty($card)) VException::throw("卡退款回调更新失败");
        $card->status        = 0;
        $card->refund_id     = $refundRes['refund_id_0'];
        $card->refund_sn     = $refundRes['out_refund_no_0'];
        $card->refund_remark = "卡退余额";
        $card->refund_at     = now();
        $log = UserMoneyLogService::instance()->getLogRechargeByTransId($card->trans_id, "id,target_gid");
        if(empty($log)) VException::throw("无法退余额，历史充值不存在");
        Db::startTrans();
        try {
            // 退实充，减用户余额
            UserInfoService::instance()->reduceMoney($card->openid, $card->real_has, UserMoneyLogService::MONEY_LOG_TYPE_CARD_REFUND_REAL, $log->target_gid, $card->trans_id, $card->refund_remark."(实充)");
            // 退赠送，减用户余额
            UserInfoService::instance()->reduceMoney($card->openid, $card->give_has, UserMoneyLogService::MONEY_LOG_TYPE_CARD_REFUND_GIVE, $log->target_gid, "", $card->refund_remark."(赠送)");
            $card->save();
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            VException::throw($e->getMessage());
        }
        return "success";
    }

    /**
     * 生成编码
     * @return string
     */
    public function genSn($prefix = "") {
        return strtoupper($prefix . Str::random(15));
    }

    /**
     * @param string $openid
     * @param float $real
     * @param float $give
     * @return Model|BaseSoftDeleteModel
     */
    public function addCard(string $openid, float $real, float $give=0) {
        $total = round($real + $give, 2);
        $real_rate = round($real / $total, 7);
        $give_rate = round(1 - $real_rate, 7);
        $data = [
            'gid'        => uuid(),
            'openid'     => $openid,
            'real_init'  => $real,
            'give_init'  => $give,
            'total_init' => $total,
            'expire_at'  => null,
            'real_rate'  => $real_rate,
            'give_rate'  => $give_rate,
            'real_has'   => $real,
            'give_has'   => $give,
            'total_has'  => $total,
        ];
        return $this->create($data);
    }

    /**
     * 退款模拟数据
     * @return array
     */
    private function getRefundDemoData() {
        return [
            "return_code"         => "SUCCESS",
            "return_msg"          => "OK",
            "appid"               => "wx4f8a0a36cd1e624a",
            "mch_id"              => "1227693802",
            "nonce_str"           => "830yULndhwlRqA4i",
            "sign"                => "EC907F3891A8EEED24C970EAE32165653F2FC2909457A4642A1F2A8DF8DBBDBC",
            "result_code"         => "SUCCESS",
            "transaction_id"      => "4200002423202408122086590058",
            "out_trade_no"        => "ORDERWAGHXQKIZ8TWRIV",
            "out_refund_no"       => "REFUNDJWEHZKPBGFDDKQE",
            "refund_id"           => "50303800612024081315308774615",
            "refund_channel"      => [],
            "refund_fee"          => 0.01,
            "coupon_refund_fee"   => 0,
            "total_fee"           => 0.01,
            "cash_fee"            => 0.01,
            "coupon_refund_count" => 0,
            "cash_refund_fee"     => 0.01,
        ];
    }
}