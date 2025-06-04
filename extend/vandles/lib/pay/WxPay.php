<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/8/13
 * Time: 11:23
 */

namespace vandles\lib\pay;

use vandles\lib\VException;
use vandles\service\ConfigService;
use WeChat\Pay;

class WxPay extends BasePay {

    public function createOrder($opts) {
        // TODO: Implement createOrder() method.
    }

    public function getPayInfo($opts) {
        // TODO: Implement getPayInfo() method.
    }

    public function orderQueryByTransactionId($transactionId) {
        $opts = [
            'transaction_id' => $transactionId
        ];
        return $this->orderQuery($opts);
    }
    public function orderQueryByOutTradeNo($outTradeNo) {
        $opts = [
            'out_trade_no' => $outTradeNo
        ];
        return $this->orderQuery($opts);
    }

    public function orderQuery($opts) {
        $wechat = Pay::instance($this->config);
        $res    = $wechat->queryOrder($opts);
        $this->responseFilter($res);

        if (isset($res["total_fee"])) $res["total_fee"] = round($res['total_fee'] / 100, 2);
        if (isset($res["cash_fee"])) $res["cash_fee"] = round($res['cash_fee'] / 100, 2);
        return $res;
    }

    public function refund($opts) {
        $wechat = Pay::instance($this->config);

        $opts['total_fee']  = round($opts['total_fee'] * 100, 2);
        $opts['refund_fee'] = round($opts['refund_fee'] * 100, 2);

        $res = $wechat->createRefund($opts);

        $this->responseFilter($res);

        if (isset($res["total_fee"])) $res["total_fee"] = round($res['total_fee'] / 100, 2);
        if (isset($res["cash_fee"])) $res["cash_fee"] = round($res['cash_fee'] / 100, 2);
        if (isset($res["refund_fee"])) $res["refund_fee"] = round($res['refund_fee'] / 100, 2);
        if (isset($res["coupon_refund_fee"])) $res["coupon_refund_fee"] = round($res['coupon_refund_fee'] / 100, 2);
        if (isset($res["cash_refund_fee"])) $res["cash_refund_fee"] = round($res['cash_refund_fee'] / 100, 2);

        return $res;
    }

    public function refundQuery($opts) {
        $wechat = Pay::instance($this->config);
        $res    = $wechat->queryRefund($opts);

        try {
            $this->responseFilter($res);
        } catch (\Exception $e) {
            VException::throw("退款申请失败：" . $e->getMessage());
        }

        if (isset($res["total_fee"])) $res["total_fee"] = round($res['total_fee'] / 100, 2);
        if (isset($res["cash_fee"]))  $res["cash_fee"] = round($res['cash_fee'] / 100, 2);
        if (isset($res["refund_fee"])) $res["refund_fee"] = round($res['refund_fee'] / 100, 2);
        if (isset($res["coupon_refund_fee"])) $res["coupon_refund_fee"] = round($res['coupon_refund_fee'] / 100, 2);
        if (isset($res["cash_refund_fee"])) $res["cash_refund_fee"] = round($res['cash_refund_fee'] / 100, 2);
        if (isset($res["refund_fee_0"])) $res["refund_fee_0"] = round($res['refund_fee_0'] / 100, 2);
        if (isset($res["cash_refund_fee_0"])) $res["cash_refund_fee_0"] = round($res['cash_refund_fee_0'] / 100, 2);

        return $res;

    }

    public function refundQueryByOutTradeNo($outTradeNo) {
        $opts = [
            'out_trade_no' => $outTradeNo,
        ];
        return $this->refundQuery($opts);
    }


    public function refundQueryByRefundId($refundId) {
        $opts = [
            'refund_id' => $refundId,
        ];
        return $this->refundQuery($opts);
    }

    private function responseFilter($res) {
        if ($res['return_code'] != "SUCCESS") {
            VException::throw($res['return_msg']);
        } else if ($res['result_code'] != "SUCCESS") {
            $code = $res['err_code'] ?? "-";
            $desc = $res['err_code_des'] ?? "未知错误";
            VException::throw("({$code})" . $desc);
        }
    }
}