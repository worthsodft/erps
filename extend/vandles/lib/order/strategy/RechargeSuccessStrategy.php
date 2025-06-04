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
 * 充值支付成功处理策略
 */
class RechargeSuccessStrategy implements OrderActionStrategy {

    public function execute($data) {
        Log::info("充值支付成功");
    }
}