<?php
/**
 * Created by wth
 * User: vandles
 * Date: 2024/12/31
 * Time: 15:10
 */

namespace vandles\lib\order\strategy;

use think\facade\Log;

/**
 * 配货(配送人员)策略
 */
class PickupShipperStrategy implements OrderActionStrategy {

    public function execute($data) {
        Log::info("配货(配送人员)策略");
        // 微信发货
        // $res = $orderService->wxShippingByOutTradeNo($post['out_trade_no']);

    }
}