<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/8/23
 * Time: 15:23
 */

namespace vandles\lib\pay;

use vandles\lib\Http;
use vandles\lib\VException;
use vandles\middleware\BaseMiddleware;
use WeChat\Contracts\BasicWeChat;
use WeChat\Contracts\BasicWePay;

/**
 * 微信发货接口
 */
class WxShipping {
    private $config;
    private static $instance;
    private $gateway = "https://api.weixin.qq.com/wxa/sec/order";

    // 订单单号类型, 1:商户侧, 2:微信侧
    const ORDER_NUMBER_TYPE_MER = 1;
    const ORDER_NUMBER_TYPE_WX  = 2;

    // 物流模式, 1:快递, 2:同城配送, 3:虚拟, 4:自提
    const LOGISTICS_TYPE_EXPRESS = 1;
    const LOGISTICS_TYPE_DELIVER = 2;
    const LOGISTICS_TYPE_VIRTUAL = 3;
    const LOGISTICS_TYPE_SELF    = 4;

    // 发货模式, 1、UNIFIED_DELIVERY（统一发货）2、SPLIT_DELIVERY（分拆发货）
    const LOGISTICS_MODE_UNIFIED = 1;
    const LOGISTICS_MODE_SPLIT   = 2;

    // 订单状态, (1) 待发货；(2) 已发货；(3) 确认收货；(4) 交易完成；(5) 已退款。
    const ORDER_STATE_NOT_SHIPPED = 1;
    const ORDER_STATE_SHIPPED     = 2;
    const ORDER_STATE_RECEIVED    = 3;
    const ORDER_STATE_FINISHED    = 4;
    const ORDER_STATE_REFUNDED    = 5;


    public static function instance($config) {
        if (!self::$instance) {
            self::$instance         = new self();
            self::$instance->config = $config;
        }
        return self::$instance;
    }

    /**
     * 发货信息录入接口
     * @param string $merchantTradeNo
     * @return mixed
     * [
     *  "errcode" => 0,
     *  "errmsg"  => "ok"
     * ]
     *
     */
    public function uploadShippingInfo(array $opts) {
        $access_token               = $this->getAccessToken();
        $url                        = $this->gateway . "/upload_shipping_info?access_token={$access_token}";
        $opts['order_key']["mchid"] = $this->config['mch_id'];
        $resp                       = $this->post($url, $opts);
        return $resp;
    }

    /**
     * 查询订单发货状态
     * @param string $merchantTradeNo
     *  [
     *   "errcode" => 0,
     *   "errmsg"  => "ok"
     *  ]
     */
    public function getOrder(string $merchantTradeNo) {
        $access_token = $this->getAccessToken();
        $url          = $this->gateway . "/get_order?access_token={$access_token}";
        $opts         = [
            "merchant_trade_no" => $merchantTradeNo,
        ];
        $resp         = $this->post($url, $opts);
        return $resp;
    }
    public function getOrder_dev(string $merchantTradeNo) {
        $access_token = $this->getAccessToken_dev();
        $url          = $this->gateway . "/get_order?access_token={$access_token}";
        $opts         = [
            "merchant_trade_no" => $merchantTradeNo,
        ];
        $resp         = $this->post($url, $opts);
        return $resp;
    }

    /**
     * 获取订单列表，
     * @param string|null $openid
     * @param int|null $order_state 默认为查询1待发货订单
     */
    public function getOrderList(string $openid=null, int $order_state=null) {
        $access_token = $this->getAccessToken();
        $url          = $this->gateway . "/get_order_list?access_token={$access_token}";
        $opts = [];
        if($openid) $opts['openid'] = $openid;
        if($order_state) $opts['order_state'] = $order_state;
        if(empty($opts)) $opts['order_state'] = 1; // 默认为查询1待发货订单
        $resp = $this->post($url, $opts);
        return $resp;
    }

    private function post($url, $opts) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($opts, JSON_UNESCAPED_UNICODE));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($curl);
        $resp = json_decode($resp, true);
        return $resp;
    }

    private function get($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($curl);
        $resp = json_decode($resp, true);
        return $resp;
    }

    private function getAccessToken() {
        return BasicWeChat::instance($this->config)->getAccessToken();

//        $key = "wx_access_token";
        $key = $this->config['appid'] . '_access_token';
        if ($access_token = cache($key)) return $access_token;
        $url  = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->config['appid']}&secret={$this->config['appsecret']}";
        $resp = Http::instance()->get($url);
        if (isset($resp['data'])) {
            $resp = json_decode($resp['data'], true);
            if (isset($resp['access_token'])) {
                cache($key, $resp['access_token'], 7000);
                return $resp['access_token'];
            }
        }
        VException::throw("access_toke获取失败：");
    }
    private function getAccessToken_dev() {
        return BasicWeChat::instance($this->config)->getAccessToken();
//        $key = "wx_access_token_dev";
        $key = $this->config['appid'] . '_access_token';
        if ($access_token = cache($key)) return $access_token;
        $url  = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->config['appid']}&secret={$this->config['appsecret']}";
        $resp = Http::instance()->get($url);
        if (isset($resp['data'])) {
            $resp = json_decode($resp['data'], true);
            if (isset($resp['access_token'])) {
                cache($key, $resp['access_token'], 7000);
                return $resp['access_token'];
            }
        }
        VException::throw("access_toke获取失败：");
    }


}