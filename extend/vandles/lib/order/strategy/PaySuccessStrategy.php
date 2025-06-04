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
 * 订单支付成功处理策略
 */
class PaySuccessStrategy implements OrderActionStrategy {

    public function execute($data) {
        Log::info("订单支付成功");
    }
}