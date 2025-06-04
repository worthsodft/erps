<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/8/13
 * Time: 11:17
 */

namespace vandles\lib\pay;

abstract class BasePay {
    public static $instance;
    protected $config;

    /**
     * @param $config
     * @return WxPay
     */
    public static function instance($config){
        if(is_null(self::$instance)) {
            self::$instance = new static();
            self::$instance->config = $config;
        }
        return self::$instance;
    }

    abstract public function createOrder($opts);
    abstract public function getPayInfo($opts);
    abstract public function orderQuery($opts);
    abstract public function orderQueryByOutTradeNo($outTradeNo);
    abstract public function orderQueryByTransactionId($transactionId);
    abstract public function refund($opts);
    abstract public function refundQuery($opts);
    abstract public function refundQueryByRefundId($refundId);
    abstract public function refundQueryByOutTradeNo($outTradeNo);

}